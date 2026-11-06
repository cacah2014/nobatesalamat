<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function __construct(private KavenegarService $sms) {}

    /**
     * تولید و ارسال کد. اتمسفیر امن: هش HMAC + مهلت + محدودیت ارسال مجدد.
     * خروجی: ['ok' => bool, 'message' => string, 'dev_code' => ?string]
     */
    public function send(string $phone): array
    {
        $ttl = (int) config('services.nobat.otp_ttl_seconds');
        $resendAfter = (int) config('services.nobat.otp_resend_seconds');

        // محدودیت ارسال مجدد
        if (Cache::has("otp_sent_at:$phone")) {
            return ['ok' => false, 'message' => 'لطفاً برای ارسال مجدد کمی صبر کنید.', 'dev_code' => null];
        }

        $code = (string) random_int(10000, 99999);

        OtpCode::where('phone', $phone)->delete();
        OtpCode::create([
            'phone' => $phone,
            'code_hash' => $this->hash($phone, $code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds($ttl),
            'created_at' => now(),
        ]);

        Cache::put("otp_sent_at:$phone", true, $resendAfter);

        $sent = $this->sms->sendVerificationCode($phone, $code);

        // در حالت توسعه یا بدون کلید پیامک، کد آزمایشی برگردانده می‌شود
        $devCode = (! $sent && app()->environment('local', 'production'))
            ? $code
            : null;

        return [
            'ok' => true,
            'message' => $sent ? 'کد تأیید پیامک شد.' : 'کد آزمایشی ایجاد شد.',
            'dev_code' => $devCode,
        ];
    }

    /** بررسی کد با محدودیت ۵ تلاش */
    public function verify(string $phone, string $code): bool
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (! $otp || $otp->attempts >= 5) {
            return false;
        }

        if (! hash_equals($otp->code_hash, $this->hash($phone, en_digits($code)))) {
            $otp->increment('attempts');
            return false;
        }

        $otp->delete();

        return true;
    }

    private function hash(string $phone, string $code): string
    {
        return hash_hmac('sha256', "{$phone}:{$code}", (string) config('app.key'));
    }
}
