<?php

namespace App\Console\Commands;

use App\Services\SiteUpdater;
use Illuminate\Console\Command;

class SiteUpdateCommand extends Command
{
    protected $signature = 'site:update
        {--check : فقط بررسی کن که نسخهٔ جدیدی روی مخزن هست یا نه}
        {--dry-run : فقط دستورهایی را که اجرا می‌شوند نشان بده}
        {--force : حتی اگر ALLOW_WEB_UPDATES=false باشد اجرا کن}';

    protected $description = 'به‌روزرسانی سایت از روی مخزن Git با دستوری که در متغیرهای محیطی (EnvKit) تعیین شده است';

    public function handle(SiteUpdater $updater): int
    {
        $this->line('<options=bold>دستور به‌روزرسانی:</options> ' . $updater->gitCommand());

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->line('مراحل اجرا:');
            foreach ($updater->commands() as [$step, $viaShell, $command]) {
                $this->line(sprintf(
                    '  [%s] %s',
                    $step,
                    $viaShell ? $command : implode(' ', $command)
                ));
            }

            $final = config('services.nobat.update.final_command');
            if ($final) {
                $this->line('  [update] ' . $final);
            }

            return self::SUCCESS;
        }

        $pending = $updater->pendingCommits();

        if ($this->option('check')) {
            $this->info('نسخهٔ فعلی سرور: ' . $updater->currentCommit());

            if ($pending === []) {
                $this->info('هیچ کامیت جدیدی وجود ندارد؛ سایت به‌روز است.');
            } else {
                $this->warn(count($pending) . ' کامیت جدید روی مخزن وجود دارد:');
                foreach ($pending as $line) {
                    $this->line('  • ' . $line);
                }
            }

            return self::SUCCESS;
        }

        if (! $updater->enabled() && ! $this->option('force')) {
            $this->error('به‌روزرسانی غیرفعال است. ALLOW_WEB_UPDATES=true را تنظیم کنید یا از --force استفاده کنید.');

            return self::FAILURE;
        }

        if ($running = $updater->runningLog()) {
            $this->error('یک به‌روزرسانی از قبل در حال اجراست (log #' . $running->id . ').');

            return self::FAILURE;
        }

        if ($pending === []) {
            $this->info('سرور همین حالا آخرین نسخه است: ' . $updater->currentCommit());

            return self::SUCCESS;
        }

        $this->info(count($pending) . ' کامیت جدید پیدا شد:');
        foreach ($pending as $line) {
            $this->line('  • ' . $line);
        }

        $result = $updater->run(null, 'artisan');

        $this->newLine();
        foreach ($result['output'] as $chunk) {
            $this->line($chunk);
        }

        if ($result['ok']) {
            $this->info('✅ به‌روزرسانی کامل شد. نسخهٔ جدید: ' . $updater->currentCommit());

            return self::SUCCESS;
        }

        $this->error('❌ به‌روزرسانی ناموفق بود: ' . $result['error']);

        return self::FAILURE;
    }
}
