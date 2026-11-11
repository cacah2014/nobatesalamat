<?php

namespace App\Services;

use App\Exceptions\SmsException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * کلاینت ارسال پیامک «پنل پیامک ستارگان» (srscrm.ir).
 *
 *   POST https://srscrm.ir/api/sms/send
 *   Authorization: Bearer {SMS_TOKEN}
 *   { "to": "0912...", "message": "...", "gateway": "1000" }
 *
 * مستندات کدهای خطا در آرایهٔ ERROR_MESSAGES نگه‌داری می‌شود.
 */
class SmsService
{
    public const ERROR_MESSAGES = [
        'invalid_token' => 'توکن پنل پیامک نامعتبر است.',
        'ip_not_allowed' => 'IP سرور در پنل پیامک مجاز نشده است.',
        'validation_error' => 'پارامترهای ارسال پیامک ناقص یا نامعتبر است.',
        'gateway_not_found' => 'خط فرستنده (gateway) در پنل پیامک پیدا نشد.',
        'no_recipients' => 'شمارهٔ گیرنده نامعتبر است.',
        'no_wallet' => 'کیف پول پنل پیامک تنظیم نشده است.',
        'insufficient_balance' => 'اعتبار پنل پیامک کافی نیست.',
        'gateway_inactive' => 'خط فرستنده غیرفعال است.',
        'server_error' => 'خطای داخلی پنل پیامک.',
        'not_configured' => 'پنل پیامک تنظیم نشده است (SMS_TOKEN / SMS_GATEWAY).',
        'connection_error' => 'اتصال به پنل پیامک برقرار نشد.',
    ];

    /** اگر توکن و خط فرستنده هر دو ست شده باشند */
    public function configured(): bool
    {
        return filled(config('services.sms.token')) && filled(config('services.sms.gateway'));
    }

    public function gateway(): string
    {
        return trim((string) config('services.sms.gateway'));
    }

    /** شمارهٔ گیرنده را به قالب 09xxxxxxxxx تبدیل می‌کند */
    public function normalizeRecipient(string $to): string
    {
        return normalize_phone($to);
    }

    /**
     * پشتیبانی از چند گیرنده (آرایه یا رشتهٔ جداشده با , ؛ ; یا فاصله).
     * خروجی: رشتهٔ آماده برای پارامتر to مثل "09121234567,09351234567"
     *
     * @param  array<int, string>|string  $to
     *
     * @throws SmsException
     */
    public function normalizeRecipients(string|array $to): string
    {
        $items = is_array($to) ? $to : [$to];
        $numbers = [];

        foreach ($items as $item) {
            foreach ($this->splitRecipients((string) $item) as $candidate) {
                $number = $this->normalizeRecipient($candidate);

                if (! preg_match('/^09\d{9}$/', $number)) {
                    throw new SmsException(self::ERROR_MESSAGES['no_recipients'], 'no_recipients');
                }

                $numbers[] = $number;
            }
        }

        if ($numbers === []) {
            throw new SmsException(self::ERROR_MESSAGES['no_recipients'], 'no_recipients');
        }

        return implode(',', array_unique($numbers));
    }

    /**
     * شکستن ورودی به شماره‌های جدا: پشتیبانی از , ؛ ; و فاصله،
     * اعداد فارسی، و شماره‌های دارای فاصله مثل "0935 123 4567".
     *
     * @return array<int, string>
     */
    private function splitRecipients(string $raw): array
    {
        $chunks = [];

        foreach (preg_split('/\s*[,;]\s*/', trim(en_digits($raw)), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $chunk) {
            $digits = preg_replace('/\D+/', '', $chunk);

            if ($digits === '') {
                $chunks[] = $chunk;

                continue;
            }

            // چند شمارهٔ ۱۱ رقمی پشت سر هم (مثل "09121234567 09351234567")
            if (strlen($digits) > 11
                && strlen($digits) % 11 === 0
                && ! str_starts_with($digits, '0098')
                && ! str_starts_with($digits, '98')) {
                foreach (str_split($digits, 11) as $part) {
                    $chunks[] = $part;
                }

                continue;
            }

            $chunks[] = $digits;
        }

        return $chunks;
    }

    /**
     * ارسال پیامک.
     *
     * @return array{ok: bool, code: string, message: string, data: array|null, simulated: bool}
     *
     * @throws SmsException
     */
    public function send(string|array $to, string $message, ?string $gateway = null): array
    {
        $gateway = $gateway !== null && $gateway !== '' ? trim($gateway) : $this->gateway();

        if (! $this->configured()) {
            if (app()->environment('local', 'testing')) {
                Log::info('[SMS][simulated]', [
                    'to' => is_array($to) ? implode(',', $to) : $to,
                    'message' => $message,
                ]);

                return [
                    'ok' => true,
                    'code' => 'simulated',
                    'message' => 'حالت توسعه: پیامک واقعی ارسال نشد.',
                    'data' => null,
                    'simulated' => true,
                ];
            }

            throw new SmsException(self::ERROR_MESSAGES['not_configured'], 'not_configured');
        }

        $recipient = $this->normalizeRecipients($to);

        $payload = [
            'to' => $recipient,
            'message' => $message,
            'gateway' => $gateway,
        ];

        try {
            $response = Http::withToken((string) config('services.sms.token'))
                ->acceptJson()
                ->asJson()
                ->timeout((int) config('services.sms.timeout', 15))
                ->post((string) config('services.sms.base_url', 'https://srscrm.ir/api/sms/send'), $payload);
        } catch (ConnectionException $e) {
            Log::error('[SMS] connection failed', ['to' => $recipient, 'error' => $e->getMessage()]);

            throw new SmsException(self::ERROR_MESSAGES['connection_error'], 'connection_error');
        }

        $body = (array) ($response->json() ?? []);
        $code = (string) ($body['code'] ?? ($response->successful() ? 'queued' : 'server_error'));
        $apiMessage = trim((string) ($body['message'] ?? ''));

        if (! $response->successful() || ($body['ok'] ?? false) !== true) {
            Log::warning('[SMS] send failed', [
                'to' => $recipient,
                'status' => $response->status(),
                'code' => $code,
                'message' => $apiMessage,
            ]);

            throw new SmsException(
                self::ERROR_MESSAGES[$code] ?? ($apiMessage !== '' ? $apiMessage : 'ارسال پیامک ناموفق بود.'),
                $code
            );
        }

        Log::info('[SMS] queued', [
            'to' => $recipient,
            'gateway' => $gateway,
            'message_id' => $body['data']['message_id'] ?? null,
            'parts' => $body['data']['parts_count'] ?? null,
            'cost' => $body['data']['cost'] ?? null,
        ]);

        return [
            'ok' => true,
            'code' => $code,
            'message' => $apiMessage,
            'data' => (array) ($body['data'] ?? []),
            'simulated' => false,
        ];
    }

    /**
     * ارسال کد یک‌بارمصرف با متن قالب‌بندی‌شده.
     *
     * @return array{ok: bool, code: string, message: string, data: array|null, simulated: bool}
     *
     * @throws SmsException
     */
    public function sendOtp(string $phone, string $code): array
    {
        return $this->send($phone, $this->otpMessage($code));
    }

    public function otpMessage(string $code): string
    {
        $template = (string) config('services.sms.otp_template', 'کد ورود شما به نوبت سلامت: %s');

        return str_replace(['%s', ':code'], $code, $template);
    }
}
