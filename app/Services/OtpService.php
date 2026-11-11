<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /** @var array<int, string> وضعیت‌های خروجی بررسی کد */
    public const STATUS_OK = 'ok';
    public const STATUS_MISSING = 'missing';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_TOO_MANY = 'too_many';

    public function __construct(private SmsService $sms) {}

    public function ttl(): int
    {
        return max(60, (int) config('services.nobat.otp_ttl_seconds', 180));
    }

    public function resendAfter(): int
    {
        return max(30, (int) config('services.nobat.otp_resend_seconds', 55));
    }

    public function maxAttempts(): int
    {
        return max(3, (int) config('services.nobat.otp_max_attempts', 5));
    }

    /**
     * تولید و ارسال کد.
     * خروجی: ['ok' => bool, 'message' => string, 'dev_code' => ?string]
     */
    public function send(string $phone): array
    {
        if (Cache::has($this->sentKey($phone))) {
            $wait = max(1, (int) Cache::get($this->sentKey($phone), 0));

            return [
                'ok' => false,
                'message' => 'لطفاً ' . fa_digits((string) $wait) . ' ثانیه دیگر برای ارسال مجدد صبر کنید.',
                'dev_code' => null,
            ];
        }

        $code = $this->store($phone);

        try {
            $result = $this->sms->sendOtp($phone, $code);
        } catch (\Throwable $e) {
            // ارسال ناموفق: کد بی‌مصرف پاک شود و کاربر فوراً بتواند دوباره تلاش کند
            Cache::forget($this->sentKey($phone));
            OtpCode::where('phone', $phone)->delete();

            throw $e;
        }

        // کد آزمایشی فقط در حالت شبیه‌سازی (محیط local/testing) نمایش داده می‌شود
        $simulated = (bool) ($result['simulated'] ?? false);

        return [
            'ok' => true,
            'message' => $simulated
                ? 'کد آزمایشی ایجاد شد (پنل پیامک تنظیم نشده است).'
                : 'کد تأیید پیامک شد.',
            'dev_code' => $simulated ? $code : null,
        ];
    }

    /**
     * ساخت کد جدید و ذخیرهٔ هش آن (بدون ارسال پیامک).
     * برای ارسال، send() از همین متد استفاده می‌کند؛ دستور otp:status هم
     * می‌تواند با آن کد را برای تست روی سرور بسازد.
     */
    public function store(string $phone): string
    {
        $code = (string) random_int(10000, 99999);

        OtpCode::where('phone', $phone)->delete();
        OtpCode::create([
            'phone' => $phone,
            'code_hash' => $this->hash($phone, $code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds($this->ttl()),
            'created_at' => now(),
        ]);

        Cache::put($this->sentKey($phone), $this->resendAfter(), $this->resendAfter());

        return $code;
    }

    /**
     * بررسی کد و برگرداندن دقیق نتیجه.
     *
     * @return array{status: string, attempts_left: int, expires_in: int}
     */
    public function verifyStatus(string $phone, string $code): array
    {
        $otp = OtpCode::where('phone', $phone)->latest('id')->first();

        if (! $otp) {
            $this->logResult($phone, 'missing');

            return ['status' => self::STATUS_MISSING, 'attempts_left' => 0, 'expires_in' => 0];
        }

        if ($otp->expires_at && $otp->expires_at->isPast()) {
            $this->logResult($phone, 'expired', 0, $this->secondsLeft($otp));

            return ['status' => self::STATUS_EXPIRED, 'attempts_left' => 0, 'expires_in' => 0];
        }

        $attempts = (int) $otp->attempts;

        if ($attempts >= $this->maxAttempts()) {
            $this->logResult($phone, 'too_many', 0, $this->secondsLeft($otp));

            return ['status' => self::STATUS_TOO_MANY, 'attempts_left' => 0, 'expires_in' => 0];
        }

        // فقط ارقام: جلوگیری از خطای کدهایی مثل «۱۲ ۳۴۵» یا «12345 »
        $digits = preg_replace('/\D+/', '', en_digits($code));

        if ($digits === '' || ! hash_equals($otp->code_hash, $this->hash($phone, $digits))) {
            $otp->increment('attempts');
            $attempts++;
            $this->logResult($phone, 'invalid', max(0, $this->maxAttempts() - $attempts), $this->secondsLeft($otp));

            return [
                'status' => self::STATUS_INVALID,
                'attempts_left' => max(0, $this->maxAttempts() - $attempts),
                'expires_in' => $this->secondsLeft($otp),
            ];
        }

        $otp->delete();
        $this->logResult($phone, 'ok');

        return ['status' => self::STATUS_OK, 'attempts_left' => $this->maxAttempts(), 'expires_in' => 0];
    }

    /** بررسی کد به‌صورت بولی (سازگاری با کدهای قبلی) */
    public function verify(string $phone, string $code): bool
    {
        return $this->verifyStatus($phone, $code)['status'] === self::STATUS_OK;
    }

    /** اطلاعات کد فعلی برای عیب‌یابی (کد قابل بازیابی نیست چون هش ذخیره می‌شود) */
    public function inspect(string $phone): ?array
    {
        $otp = OtpCode::where('phone', $phone)->latest('id')->first();

        if (! $otp) {
            return null;
        }

        return [
            'created_at' => $otp->created_at,
            'expires_at' => $otp->expires_at,
            'expires_in' => $this->secondsLeft($otp),
            'attempts' => (int) $otp->attempts,
            'max_attempts' => $this->maxAttempts(),
        ];
    }

    public function clear(string $phone): void
    {
        OtpCode::where('phone', $phone)->delete();
        Cache::forget($this->sentKey($phone));
    }

    private function secondsLeft(OtpCode $otp): int
    {
        if (! $otp->expires_at) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($otp->expires_at, false));
    }

    /** ثبت نتیجهٔ بررسی در لاگ برای عیب‌یابی (شماره ماسک می‌شود) */
    private function logResult(string $phone, string $status, int $attemptsLeft = 0, int $expiresIn = 0): void
    {
        Log::info('[OTP] verify', [
            'phone' => $this->maskPhone($phone),
            'status' => $status,
            'attempts_left' => $attemptsLeft,
            'expires_in' => $expiresIn,
        ]);
    }

    private function maskPhone(string $phone): string
    {
        return strlen($phone) > 6
            ? substr($phone, 0, 4) . '***' . substr($phone, -2)
            : '***';
    }

    private function sentKey(string $phone): string
    {
        return "otp_sent_at:$phone";
    }

    private function hash(string $phone, string $code): string
    {
        return hash_hmac('sha256', "{$phone}:{$code}", (string) config('app.key'));
    }
}
