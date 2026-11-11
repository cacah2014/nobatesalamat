<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * بررسی سلامت ساختار دیتابیس: جدول‌ها و ستون‌های لازم وجود دارند یا نه.
 * مناسب زمانی که پروژه روی دیتابیس/جدول‌های قدیمی اجرا می‌شود.
 */
class AppCheckCommand extends Command
{
    protected $signature = 'app:check';

    protected $description = 'بررسی جدول‌ها، ستون‌های لازم، کلید برنامه و تنظیمات پیامک';

    private const TABLES = [
        'users', 'otp_codes', 'specialties', 'doctors', 'appointments',
        'site_settings', 'articles', 'update_logs',
        'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs',
    ];

    private const USER_COLUMNS = ['phone', 'full_name', 'role', 'status', 'permissions'];

    public function handle(): int
    {
        $problems = 0;

        $this->line('اتصال دیتابیس: ' . config('database.default'));
        $this->line('دیتابیس      : ' . (string) config('database.connections.' . config('database.default') . '.database'));

        try {
            DB::connection()->getPdo();
            $this->info('✅ اتصال به دیتابیس برقرار است.');
        } catch (Throwable $e) {
            $this->error('❌ اتصال به دیتابیس ناموفق: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('— جدول‌ها —');

        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table)) {
                $this->line('  ✅ ' . $table);
            } else {
                $problems++;
                $this->line('  ❌ ' . $table . '   → php artisan migrate');
            }
        }

        if (Schema::hasTable('users')) {
            $this->newLine();
            $this->line('— ستون‌های جدول users —');

            foreach (self::USER_COLUMNS as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $this->line('  ✅ ' . $column);
                } else {
                    $problems++;
                    $this->line('  ❌ ' . $column . '   → php artisan migrate');
                }
            }

            if (Schema::hasColumn('users', 'status')) {
                $nullStatus = DB::table('users')->whereNull('status')->orWhere('status', '')->count();

                if ($nullStatus > 0) {
                    $problems++;
                    $this->warn('  ⚠ ' . $nullStatus . ' کاربر بدون وضعیت (status خالی) → php artisan migrate (اصلاح خودکار)');
                }
            }

            $this->line('  👥 تعداد کاربران: ' . DB::table('users')->count());
            $this->line('  🛡  تعداد مدیران : ' . DB::table('users')->whereIn('role', ['admin', 'super_admin'])->count());
        }

        $this->newLine();
        $this->line('— تنظیمات برنامه —');

        $checks = [
            'APP_KEY' => filled(config('app.key')),
            'APP_URL' => filled(config('app.url')),
            'SMS_TOKEN' => filled(config('services.sms.token')),
            'SMS_GATEWAY' => filled(config('services.sms.gateway')),
        ];

        foreach ($checks as $key => $ok) {
            $this->line(($ok ? '  ✅ ' : '  ⚠ ') . $key . ($ok ? '' : '   → در .env تنظیم کنید'));
        }

        $this->newLine();

        if ($problems === 0) {
            $this->info('همه‌چیز سالم است.');

            return self::SUCCESS;
        }

        $this->warn($problems . ' مورد نیاز به رسیدگی دارد (بالا را ببینید).');

        return self::FAILURE;
    }
}
