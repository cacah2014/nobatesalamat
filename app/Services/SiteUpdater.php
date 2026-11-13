<?php

namespace App\Services;

use App\Models\UpdateLog;
use Closure;
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
    /**
     * به‌روزرسانی از پنل مدیریت (فقط مدیر با دسترسی updates + CSRF).
     * به‌صورت پیش‌فرض فعال است تا نیازی به تنظیم .env نباشد.
     */
    public function panelEnabled(): bool
    {
        return (bool) config('services.nobat.update.panel_allowed', true);
    }

    /**
     * وب‌هوک عمومی /deploy/{token} — فقط اگر ALLOW_WEB_UPDATES=true و توکن ست شده باشد.
     */
    public function webhookEnabled(): bool
    {
        return (bool) config('services.nobat.update.allowed');
    }

    /** سازگاری با کدهای قبلی (وب‌هوک) */
    public function enabled(): bool
    {
        return $this->webhookEnabled();
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

    /** باینری PHP که واقعاً روی سرور اجرا می‌شود (نتیجه در همان درخواست کش می‌شود) */
    private static ?string $resolvedPhp = null;

    /**
     * پیدا کردن PHP CLI قابل اجرا.
     *
     * ترتیب: مقدار UPDATE_PHP_BINARY ← PHP_BINARY خود برنامه ← `php` روی PATH.
     * این کار جلوی خطای «is not recognized as an internal or external command»
     * را می‌گیرد؛ مثلاً وقتی `PHP_BINARY` به php-cgi اشاره می‌کند یا متغیر محیطی
     * با کوتیشن اضافه تنظیم شده است.
     */
    public function phpBinary(): string
    {
        if (self::$resolvedPhp !== null) {
            return self::$resolvedPhp;
        }

        $configured = self::cleanBinary((string) config('services.nobat.update.php_binary', ''));

        $candidates = array_values(array_unique(array_filter([
            $configured,
            self::cleanBinary((string) PHP_BINARY),
            'php',
            'php.exe',
        ], fn (string $value) => $value !== '')));

        foreach ($candidates as $candidate) {
            try {
                if (str_contains($this->runProcess([$candidate, '-v'], 60), 'PHP ')) {
                    return self::$resolvedPhp = $candidate;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return self::$resolvedPhp = $configured !== '' ? $configured : (string) PHP_BINARY;
    }

    /** حذف کوتیشن/فاصلهٔ اضافه از مسیر باینری‌های تنظیم‌شده در EnvKit */
    private static function cleanBinary(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"'");
    }

    public function composerBinary(): string
    {
        return (string) config('services.nobat.update.composer_binary', 'composer');
    }

    /** فرمان composer: composer.phar موجود در ریشهٔ پروژه، سپس دستور روی PATH */
    public function composerCommand(): array
    {
        $phar = base_path('composer.phar');

        if (is_file($phar)) {
            return [$this->phpBinary(), $phar];
        }

        return [$this->composerBinary()];
    }

    /**
     * لیست دستورها به ترتیب: دریافت از گیت ← Dependency ← Database ← Code
     *
     * هر آیتم: [مرحله، از طریق شل؟، فرمان، اجباری؟]
     * فقط git pull اجباری است؛ بقیه اگر شکست بخورند به‌عنوان هشدار ثبت
     * می‌شوند تا کش‌ها در هر حال پاک/بازسازی شوند.
     */
    public function commands(): array
    {
        $custom = trim((string) config('services.nobat.update.composer_command', ''));

        $dependency = $custom !== ''
            ? [true, $custom]
            : [false, array_merge($this->composerCommand(), [
                'install', '--no-dev', '--prefer-dist', '--optimize-autoloader', '--no-interaction',
            ])];

        return [
            ['git_pull', true, $this->gitCommand(), true],
            ['dependency', $dependency[0], $dependency[1], false],
            ['database', false, [$this->phpBinary(), 'artisan', 'migrate', '--force'], false],
            ['code', false, [$this->phpBinary(), 'artisan', 'optimize:clear'], false],
            ['code', false, [$this->phpBinary(), 'artisan', 'config:cache'], false],
            ['code', false, [$this->phpBinary(), 'artisan', 'route:cache'], false],
            ['code', false, [$this->phpBinary(), 'artisan', 'view:cache'], false],
        ];
    }

    /**
     * بررسی پیش‌نیازهای به‌روزرسانی (git، composer، فایل‌های محلی).
     *
     * @return array<int, array{label: string, ok: bool, detail: string}>
     */
    public function preflight(): array
    {
        $checks = [];

        $hasRepo = is_dir(base_path('.git'));
        $checks[] = [
            'label' => 'مخزن گیت',
            'ok' => $hasRepo,
            'detail' => $hasRepo ? 'پوشهٔ .git موجود است' : 'پوشهٔ .git پیدا نشد؛ کد باید با git clone دریافت شده باشد',
        ];

        try {
            $checks[] = ['label' => 'دستور git', 'ok' => true, 'detail' => $this->runShell('git --version', 30)];
        } catch (Throwable $e) {
            $checks[] = ['label' => 'دستور git', 'ok' => false, 'detail' => 'git در PATH سرور پیدا نشد: ' . $e->getMessage()];
        }

        try {
            $composer = $this->runProcess(array_merge($this->composerCommand(), ['--version']));
            $checks[] = ['label' => 'Composer', 'ok' => true, 'detail' => trim($composer)];
        } catch (Throwable $e) {
            $checks[] = [
                'label' => 'Composer',
                'ok' => false,
                'detail' => 'composer پیدا نشد (مرحلهٔ Dependency با هشدار رد می‌شود): ' . $e->getMessage(),
            ];
        }

        try {
            $binary = $this->phpBinary();
            $phpVersion = trim($this->runProcess([$binary, '-v'], 60));
            $checks[] = [
                'label' => 'PHP CLI',
                'ok' => str_contains($phpVersion, 'PHP '),
                'detail' => $phpVersion === ''
                    ? 'php در سرور اجرا نشد (' . $binary . ')'
                    : strtok($phpVersion, "\n") . ' — ' . $binary,
            ];
        } catch (Throwable $e) {
            $checks[] = [
                'label' => 'PHP CLI',
                'ok' => false,
                'detail' => 'PHP CLI اجرا نشد. در EnvKit متغیر UPDATE_PHP_BINARY را روی مسیر کامل php.exe بگذارید '
                    . '(مثال: C:\\ProgramData\\envkit\\services\\php\\8.5.12\\php.exe) — بدون کوتیشن. جزئیات: '
                    . $e->getMessage(),
            ];
        }

        foreach (['storage/framework/views', 'bootstrap/cache'] as $dir) {
            $path = base_path($dir);
            $checks[] = [
                'label' => 'نوشتن در ' . $dir,
                'ok' => is_dir($path) && is_writable($path),
                'detail' => is_dir($path)
                    ? (is_writable($path) ? 'قابل نوشتن است' : 'قابل نوشتن نیست؛ دسترسی پوشه را در ویندوز/هاست اصلاح کنید')
                    : 'پوشه وجود ندارد',
            ];
        }

        $changes = $this->localChanges();

        $checks[] = [
            'label' => 'تغییرات محلی سرور',
            'ok' => $changes === [],
            'detail' => $changes === []
                ? 'فایلی تغییر نکرده است'
                : 'این فایل‌ها با git pull تداخل می‌کنند و به‌طور خودکار در «stash» نگه داشته می‌شوند: '
                    . preg_replace('/\s+/', ' ', implode(' , ', $changes)),
        ];

        return $checks;
    }

    /**
     * مقایسهٔ نسخهٔ سرور با مخزن (بدون پنهان‌کردن خطاها).
     *
     * @return array{ok: bool, error: string|null, behind: int|null, local: string|null, remote: string|null, commits: array<int, string>}
     */
    public function pendingInfo(): array
    {
        $remote = sprintf('%s/%s', $this->gitRemote(), $this->gitBranch());

        try {
            $this->runShell(sprintf('git fetch %s --prune', escapeshellarg($this->gitRemote())), 180);

            $local = $this->runShell('git rev-parse --short HEAD', 30);
            $remoteHead = $this->runShell(sprintf('git rev-parse --short %s', escapeshellarg($remote)), 30);
            $behind = (int) trim($this->runShell(sprintf(
                'git rev-list --count HEAD..%s',
                escapeshellarg($remote)
            ), 60));

            $list = $this->runShell(sprintf('git log --oneline HEAD..%s', escapeshellarg($remote)), 60);

            return [
                'ok' => true,
                'error' => null,
                'behind' => $behind,
                'local' => trim($local),
                'remote' => trim($remoteHead),
                'commits' => $list === '' ? [] : array_values(array_filter(array_map('trim', explode("\n", $list)))),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'behind' => null,
                'local' => null,
                'remote' => null,
                'commits' => [],
            ];
        }
    }

    /**
     * فایل‌هایی که روی سرور تغییر کرده‌اند و `git pull` را متوقف می‌کنند.
     *
     * @return array<int, string>
     */
    public function localChanges(): array
    {
        try {
            $status = $this->runShell('git status --porcelain', 60);
        } catch (Throwable) {
            return [];
        }

        $status = trim($status);

        return $status === '' ? [] : array_values(array_filter(array_map('trim', explode("\n", $status))));
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
        return $this->pendingInfo()['commits'];
    }

    /**
     * اجرای کامل به‌روزرسانی.
     *
     * @return array{ok: bool, log: UpdateLog, output: array<int, string>, error: string|null, warnings: array<int, string>}
     */
    public function run(?int $adminId = null, string $trigger = 'panel', bool $force = false): array
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

        $warnings = [];
        $stashName = null;
        $commands = $this->commands();

        try {
            // حالت اجباری: سرور را دقیقاً با مخزن هم‌تراز کن (تغییرات محلی stash می‌شوند)
            if ($force) {
                $save('git_pull', $this->hardSync());
                $warnings[] = 'به‌روزرسانی اجباری اجرا شد؛ تغییرات محلی در stash ذخیره شد.';
                $commands = array_values(array_filter($commands, fn ($item) => $item[0] !== 'git_pull'));
            }

            foreach ($commands as [$step, $viaShell, $command, $required]) {
                // قبل از دریافت نسخهٔ جدید، تغییرات محلی سرور را در stash نگه می‌داریم
                // تا git pull با خطای «local changes would be overwritten» متوقف نشود.
                if ($step === 'git_pull' && $stashName === null) {
                    $stashName = $this->stashLocalChanges($save);

                    if ($stashName !== null) {
                        $warnings[] = 'تغییرات محلی سرور در stash ذخیره شد (' . $stashName . ')';
                    }
                }

                try {
                    $save($step, $viaShell ? $this->runShell($command) : $this->runProcess($command));
                } catch (Throwable $e) {
                    $message = trim($e->getMessage()) ?: 'خطای نامشخص';

                    if ($required) {
                        // خودترمیمی: اگر git pull به‌خاطر تغییرات محلی/اختلاف پایان خط
                        // شکست بخورد، سرور را با مخزن هم‌تراز می‌کنیم (پس از stash).
                        if ($step === 'git_pull' && (bool) config('services.nobat.update.force_reset', true)) {
                            $warnings[] = 'git pull ناموفق بود؛ هم‌ترازی سخت با مخزن اجرا شد.';
                            $save($step, '[بازیابی خودکار] ' . $message . "\n\n" . $this->hardSync());

                            continue;
                        }

                        throw $e;
                    }

                    // مرحلهٔ غیرحیاتی: ثبت هشدار و ادامه تا کش‌ها در هر حال بازسازی شوند
                    $warnings[] = "{$step}: {$message}";
                    $save($step, '[هشدار] ' . $message);
                }
            }

            if ($stashName !== null) {
                $save('git_pull', 'تغییرات محلی روی سرور در stash ذخیره شد. برای دیدن/بازیابی: '
                    . 'git stash list / git stash show -p stash@{0} / git stash pop');
            }

            $final = trim((string) config('services.nobat.update.final_command'));

            if ($final === '') {
                $save('update', 'نسخهٔ جدید فعال شد.');
            } else {
                try {
                    $save('update', $this->runShell($final));
                } catch (Throwable $e) {
                    // کد و دیتابیس آپدیت شده‌اند؛ فقط فرمان اختیاریِ محیطی خطا داده است
                    $message = trim($e->getMessage()) ?: 'خطای نامشخص';

                    $warnings[] = 'فرمان نهایی (UPDATE_FINAL_COMMAND) ناموفق بود: ' . $message;
                    $save('update', '[هشدار] ' . $message . "\n\nنسخهٔ جدید با موفقیت فعال شد؛ فقط فرمان اختیاری محیطی اجرا نشد.");
                }
            }

            $log->update(['status' => 'success', 'current_step' => 'completed', 'finished_at' => now()]);

            return ['ok' => true, 'log' => $log, 'output' => $output, 'error' => null, 'warnings' => $warnings];
        } catch (Throwable $e) {
            $message = trim($e->getMessage()) ?: 'خطای نامشخص';
            $output[] = "[ERROR]\n{$message}";
            $log->update(['status' => 'failed', 'output' => implode("\n\n", $output), 'finished_at' => now()]);

            return ['ok' => false, 'log' => $log, 'output' => $output, 'error' => $message, 'warnings' => $warnings];
        }
    }

    /**
     * هم‌ترازی سخت سرور با مخزن: stash تغییرات محلی → fetch → reset --hard.
     *
     * مخصوص زمانی که `git pull` به‌خاطر «local changes would be overwritten»
     * یا اختلاف پایان خط (CRLF ویندوز) متوقف می‌شود.
     */
    public function hardSync(): string
    {
        $remote = $this->gitRemote();
        $branch = $this->gitBranch();

        $lines = [];

        $lines[] = 'git config core.autocrlf=false → ' . $this->tryCommand('git config core.autocrlf false');
        $lines[] = 'git config core.eol=lf → ' . $this->tryCommand('git config core.eol lf');

        if ($this->localChanges() !== []) {
            $name = 'auto-stash-' . now()->format('Ymd-His');
            $lines[] = 'git stash -u → ' . $this->tryCommand(
                'git stash push --include-untracked -m ' . escapeshellarg($name)
            );
            $lines[] = 'تغییرات محلی در stash ذخیره شد: ' . $name;
        }

        $lines[] = 'git fetch → ' . $this->tryCommand(
            'git fetch ' . escapeshellarg($remote) . ' --prune'
        );

        $lines[] = 'git reset --hard ' . $remote . '/' . $branch . ' → ' . $this->tryCommand(
            'git reset --hard ' . escapeshellarg($remote . '/' . $branch)
        );

        $lines[] = 'HEAD → ' . $this->tryCommand('git log -1 --oneline');
        $lines[] = 'git stash list → ' . $this->tryCommand('git stash list');

        return implode("\n", array_filter($lines, fn (string $line) => trim($line) !== ''));
    }

    /** اجرای فرمان گیت که خطا دادنش کل فرایند را متوقف نمی‌کند */
    private function tryCommand(string $command, int $timeout = 180): string
    {
        try {
            return trim($this->runShell($command, $timeout)) ?: 'ok';
        } catch (Throwable $e) {
            return '[رد شد] ' . $e->getMessage();
        }
    }

    /**
     * ذخیرهٔ تغییرات محلی در stash (قابل بازیابی) و برگرداندن نام stash.
     *
     * بدون این کار، اگر کسی فایلی روی سرور را دستی تغییر داده باشد،
     * `git pull` با خطای «Your local changes would be overwritten» شکست می‌خورد.
     */
    private function stashLocalChanges(Closure $save): ?string
    {
        $changes = $this->localChanges();

        if ($changes === []) {
            return null;
        }

        if (! (bool) config('services.nobat.update.auto_stash', true)) {
            throw new \RuntimeException(
                'فایل‌های تغییر‌یافتهٔ محلی مانع به‌روزرسانی هستند: ' . implode(' , ', $changes)
            );
        }

        $name = 'auto-stash-' . now()->format('Ymd-His');

        $output = $this->runShell(sprintf(
            'git stash push --include-untracked -m %s',
            escapeshellarg($name)
        ), 180);

        $save('git_pull', 'تغییرات محلی سرور در stash ذخیره شد (' . $name . ')\n' . $output);

        return $name;
    }

    private function runProcess(array $command, ?int $timeout = null): string
    {
        $process = new Process($command, base_path(), null, null, $timeout ?? $this->timeout());
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
