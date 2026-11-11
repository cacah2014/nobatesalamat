<?php

namespace App\Console\Commands;

use App\Exceptions\SmsException;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SmsTestCommand extends Command
{
    protected $signature = 'sms:test
        {to? : شمارهٔ گیرنده، مثال 09121234567}
        {--message= : متن پیام (پیش‌فرض: پیام آزمایشی)}
        {--check : فقط تنظیمات را بررسی کن و پیامک نفرست}';

    protected $description = 'تست پنل پیامک ستارگان (srscrm.ir): بررسی تنظیمات و ارسال پیامک آزمایشی';

    public function handle(SmsService $sms): int
    {
        $tokenSet = filled(config('services.sms.token'));
        $gateway = $sms->gateway();

        $this->line('base_url : ' . config('services.sms.base_url'));
        $this->line('token    : ' . ($tokenSet ? '✅ ست شده (SMS_TOKEN)' : '❌ خالی (SMS_TOKEN)'));
        $this->line('gateway  : ' . ($gateway !== '' ? '✅ ' . $gateway : '❌ خالی (SMS_GATEWAY)'));
        $this->line('template : ' . $sms->otpMessage('12345'));

        if ($this->option('check')) {
            if (! $sms->configured()) {
                $this->error('تنظیمات کامل نیست؛ SMS_TOKEN و SMS_GATEWAY را در .env بگذارید.');

                return self::FAILURE;
            }

            $this->info('تنظیمات ظاهراً کامل است. برای ارسال واقعی، دستور را بدون --check اجرا کنید.');

            return self::SUCCESS;
        }

        $to = (string) $this->argument('to');

        if ($to === '') {
            $this->error('شمارهٔ گیرنده را بدهید: php artisan sms:test 09121234567');

            return 2;
        }

        $message = (string) ($this->option('message') ?: 'پیام آزمایشی نوبت سلامت — ' . now()->format('Y-m-d H:i'));

        try {
            $result = $sms->send($to, $message);
        } catch (SmsException $e) {
            $this->error('❌ ' . $e->getMessage() . '  [code: ' . $e->errorCode() . ']');

            return self::FAILURE;
        }

        if ($result['simulated'] ?? false) {
            $this->warn('⚠ پنل پیامک تنظیم نشده؛ پیامک واقعی ارسال نشد (فقط در لاگ ثبت شد).');

            return self::SUCCESS;
        }

        $this->info('✅ پیامک به ' . $sms->normalizeRecipient($to) . ' در صف ارسال قرار گرفت (code: ' . $result['code'] . ').');

        $data = (array) ($result['data'] ?? []);
        $this->table(
            ['message_id', 'status', 'recipients', 'parts', 'cost'],
            [[
                $data['message_id'] ?? '-',
                $data['status'] ?? '-',
                $data['recipients_count'] ?? '-',
                $data['parts_count'] ?? '-',
                $data['cost'] ?? '-',
            ]]
        );

        return self::SUCCESS;
    }
}
