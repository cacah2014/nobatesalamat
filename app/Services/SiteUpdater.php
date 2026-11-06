<?php

namespace App\Services;

use App\Models\UpdateLog;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * اجرای به‌روزرسانی سایت از روی مخزن Git.
 *
 * فرمان‌ها از متغیرهای محیطی خوانده می‌شوند تا سرویس‌هایی مثل EnvKit
 * بتوانند بدون تغییر کد، دستور به‌روزرسانی را تعیین کنند.
 */
class SiteUpdater
{
    public function enabled(): bool
    {
        return (bool) config('services.nobat.update.allowed');
    }

    public function tokenMatches(?string $token): bool
    {
        $expected = (string) config('services.nobat.update.token');

        if ($expected === '' || $token === null || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    public function ipAllowed(?string $ip): bool
    {
        $allowed = (array) config('services.nobat.update.allowed_ips', []);

        if ($allowed === []) {
            return true;
        }

        return in_array((string) $ip, $allowed, true);
    }

    public function gitRemote(): string
    {
        return (string) config('services.nobat.update.git_remote', 'origin');
    }

    public function gitBranch(): string
    {
        return (string) config('services.nobat.update.git_branch', 'main');
    }

    /** فرمان نهایی «دریافت نسخهٔ جدید» — از محیط خوانده می‌شود */
    public function gitCommand(): string
    {
        $custom = trim((string) config('services.nobat.update.git_command', ''));

        if ($custom !== '') {
            return $custom;
        }

        return sprintf(
            'git pull --ff-only %s %s',
            escapeshellarg($this->gitRemote()),
            escapeshellarg($this->gitBranch())
        );
    }

    public function phpBinary(): string
    {
        return (string) config('services.nobat.update.php_binary', PHP_BINARY);
    }

    public function composerBinary(): string
    {
        return (string) config('services.nobat.update.composer_binary', 'composer');
    }

    /** لیست دستورها به ترتیب: دریافت از گیت ← Dependency ← Database ← Code */
    public function commands(): array
    {
        return [
            ['git_pull', true, $this->gitCommand()],
            ['dependency', false, [
                $this->composerBinary(), 'install', '--no-dev', '--prefer-dist',
                '--optimize-autoloader', '--no-interaction',
            ]],
            ['database', false, [$this->phpBinary(), 'artisan', 'migrate', '--force']],
            ['code', false, [$this->phpBinary(), 'artisan', 'optimize:clear']],
            ['code', false, [$this->phpBinary(), 'artisan', 'config:cache']],
            ['code', false, [$this->phpBinary(), 'artisan', 'route:cache']],
            ['code', false, [$this->phpBinary(), 'artisan', 'view:cache']],
        ];
    }

    public function runningLog(): ?UpdateLog
    {
        return UpdateLog::where('status', 'running')
            ->where('started_at', '>', now()->subMinutes(15))
            ->latest('started_at')
            ->first();
    }

    public function currentCommit(): string
    {
        try {
            return $this->runShell('git log -1 --format="%h %s"');
        } catch (Throwable) {
            return '—';
        }
    }

    /** خواندن کامیت‌های جدیدِ روی مخزن که هنوز روی سرور نیستند */
    public function pendingCommits(): array
    {
        try {
            $this->runShell(sprintf('git fetch %s --prune', escapeshellarg($this->gitRemote())));
            $output = $this->runShell(sprintf(
                'git log --oneline HEAD..%s/%s',
                escapeshellarg($this->gitRemote()),
                escapeshellarg($this->gitBranch())
            ));
        } catch (Throwable) {
            return [];
        }

        if ($output === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", $output))));
    }

    /**
     * اجرای کامل به‌روزرسانی.
     *
     * @return array{ok: bool, log: UpdateLog, output: array<int, string>, error: string|null}
     */
    public function run(?int $adminId = null, string $trigger = 'panel'): array
    {
        $log = UpdateLog::create([
            'admin_id' => $adminId,
            'status' => 'running',
            'current_step' => 'git_pull',
            'output' => '',
            'started_at' => now(),
        ]);

        $output = ["[trigger] {$trigger}" . ($adminId ? " (admin #{$adminId})" : '')];

        $save = function (string $step, string $text) use ($log, &$output): void {
            $output[] = "[{$step}]\n" . ($text === '' ? 'انجام شد' : $text);
            $log->update(['current_step' => $step, 'output' => implode("\n\n", $output)]);
        };

        try {
            foreach ($this->commands() as [$step, $viaShell, $command]) {
                $save($step, $viaShell ? $this->runShell($command) : $this->runProcess($command));
            }

            $final = config('services.nobat.update.final_command');

            $save('update', $final ? $this->runShell($final) : 'نسخهٔ جدید فعال شد.');

            $log->update(['status' => 'success', 'current_step' => 'completed', 'finished_at' => now()]);

            return ['ok' => true, 'log' => $log, 'output' => $output, 'error' => null];
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) ?: 'خطای نامشخص';
            $output[] = "[ERROR]\n{$message}";
            $log->update(['status' => 'failed', 'output' => implode("\n\n", $output), 'finished_at' => now()]);

            return ['ok' => false, 'log' => $log, 'output' => $output, 'error' => $message];
        }
    }

    private function runProcess(array $command): string
    {
        $process = new Process($command, base_path(), null, null, $this->timeout());
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() . $process->getOutput()));
        }

        return trim($process->getOutput() . $process->getErrorOutput());
    }

    private function runShell(string $command, ?int $timeout = null): string
    {
        $process = Process::fromShellCommandline($command, base_path(), null, null, $timeout ?? $this->timeout());
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() . $process->getOutput()));
        }

        return trim($process->getOutput() . $process->getErrorOutput());
    }

    private function timeout(): int
    {
        return max(60, (int) config('services.nobat.update.timeout', 900));
    }
}
