<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KavenegarService
{
    /**
     * ارسال کد تأیید با سرویس Verify/Lookup کاوه‌نگار.
     * اگر کلید تنظیم نشده باشد false برمی‌گرداند (حالت توسعه).
     */
    public function sendVerificationCode(string $phone, string $code): bool
    {
        $apiKey = config('services.kavenegar.api_key');

        if (! $apiKey) {
            Log::info("OTP dev mode: code {$code} for {$phone}");
            return false;
        }

        $response = Http::timeout(10)->get(
            "https://api.kavenegar.com/v1/{$apiKey}/verify/lookup.json",
            [
                'receptor' => $phone,
                'token' => $code,
                'template' => config('services.kavenegar.template'),
            ]
        );

        if (! $response->successful() || ($response->json('return.status') ?? 0) !== 200) {
            Log::error('Kavenegar OTP failed', ['response' => $response->json()]);
            throw new \RuntimeException('ارسال پیامک با خطا روبه‌رو شد.');
        }

        return true;
    }
}
