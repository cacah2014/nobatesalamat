<?php

namespace App\Console\Commands;

use App\Exceptions\SmsException;
use App\Services\OtpService;
use App\Services\SmsService;
use Illuminate\Console\Command;

class OtpStatusCommand extends Command
{
    protected $signature = 'otp:status
        {phone : شمارهٔ موبایل، مثال 09121234567}
        {--issue : ساخت کد جدید و نمایش آن (برای تست ورود)}
        {--no-sms : فقط کد ساخته شود، پیامک ارسال نشود}
        {--clear : پاک کردن کد فعلی و بازنشانی محدودیت ارسال}';

    protected $description = 'وضعیت کد یک‌بارمصرف یک شماره: زمان انقضا، تعداد تلاش و ساخت کد تستی';

    public function handle(OtpService $otp, SmsService $sms): int
    {
        $phone = normalize_phone((string) $this->argument('phone'));

        if (! preg_match('/^09\d{9}$/', $phone)) {
            $this->error('شمارهٔ موبایل نامعتبر است.');

            return 2;
        }

        if ($this->option('clear')) {
            $otp->clear($phone);
            $this->info('✅ کد و محدودیت ارسال برای ' . $phone . ' پاک شد.');

            return self::SUCCESS;
        }

        $this->line('شماره: ' . $phone);
        $this->line('TTL: ' . $otp->ttl() . ' ثانیه | حداکثر تلاش: ' . $otp->maxAttempts() . ' | فاصلهٔ ارسال مجدد: ' . $otp->resendAfter() . ' ثانیه');

        $info = $otp->inspect($phone);

        if (! $info) {
            $this->warn('هیچ کد فعالی برای این شماره ثبت نشده است.');
        } else {
            $this->table(
                ['created_at', 'expires_at', 'ثانیهٔ باقی‌مانده', 'تلاش', 'حداکثر تلاش'],
                [[
                    (string) $info['created_at'],
                    (string) $info['expires_at'],
                    $info['expires_in'] > 0 ? $info['expires_in'] : 'منقضی',
                    $info['attempts'],
                    $info['max_attempts'],
                ]]
            );
        }

        if (! $this->option('issue')) {
            if ($info && $info['expires_in'] <= 0) {
                $this->line('برای ساخت کد جدید: php artisan otp:status ' . $phone . ' --issue');
            }

            return self::SUCCESS;
        }

        $code = $otp->store($phone);
        $this->info('کد جدید: ' . $code);

        if ($this->option('no-sms')) {
            $this->line('پیامک ارسال نشد (--no-sms).');

            return self::SUCCESS;
        }

        try {
            $result = $sms->sendOtp($phone, $code);
        } catch (SmsException $e) {
            $this->error('❌ ارسال پیامک ناموفق: ' . $e->getMessage() . ' [code: ' . $e->errorCode() . ']');
            $this->line('کد بالا معتبر است؛ می‌توانید همان را در صفحهٔ ورود وارد کنید.');

            return self::FAILURE;
        }

        $this->info(($result['simulated'] ?? false)
            ? '⚠ پیامک شبیه‌سازی شد (SMS_TOKEN تنظیم نشده)؛ کد بالا را دستی وارد کنید.'
            : '✅ پیامک ارسال شد.');

        return self::SUCCESS;
    }
}
