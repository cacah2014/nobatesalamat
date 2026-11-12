<?php

if (! function_exists('fa_digits')) {
    /** تبدیل ارقام انگلیسی و عربی به فارسی */
    function fa_digits(?string $value): string
    {
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        return str_replace($arabic, $persian, str_replace($english, $persian, (string) $value));
    }
}

if (! function_exists('en_digits')) {
    /** یکسان‌سازی ارقام فارسی/عربی ورودی کاربر به انگلیسی */
    function en_digits(?string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($arabic, $english, str_replace($persian, $english, (string) $value));
    }
}

if (! function_exists('fa_money')) {
    /** قالب‌بندی مبلغ به تومان با ارقام فارسی */
    function fa_money(int|float $amount): string
    {
        return fa_digits(number_format($amount)) . ' تومان';
    }
}

if (! function_exists('normalize_phone')) {
    /** نرمال‌سازی شماره موبایل ایران به قالب 09xxxxxxxxx */
    function normalize_phone(string $input): string
    {
        $phone = preg_replace('/\D/', '', en_digits($input));

        if (str_starts_with($phone, '0098')) {
            $phone = '0' . substr($phone, 4);
        } elseif (str_starts_with($phone, '98')) {
            $phone = '0' . substr($phone, 2);
        } elseif (str_starts_with($phone, '9') && strlen($phone) === 10) {
            $phone = '0' . $phone;
        }

        return $phone;
    }
}

if (! function_exists('admin_phones')) {
    /**
     * فهرست شماره‌های «مدیر اصلی» از ADMIN_PHONE.
     * چند شماره را می‌توان با کاما (یا فاصله) جدا کرد.
     *
     * @return array<int, string>
     */
    function admin_phones(): array
    {
        $raw = implode(',', array_filter([
            (string) config('services.nobat.admin_phone', ''),
            (string) config('services.nobat.owner_phone', ''),
        ]));

        $phones = [];

        foreach (preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
            $phone = normalize_phone((string) $part);

            if (preg_match('/^09\d{9}$/', $phone)) {
                $phones[] = $phone;
            }
        }

        return array_values(array_unique($phones));
    }
}

if (! function_exists('is_admin_phone')) {
    /** آیا این شماره، شمارهٔ مدیر اصلی است؟ */
    function is_admin_phone(?string $phone): bool
    {
        return $phone !== null
            && $phone !== ''
            && in_array(normalize_phone($phone), admin_phones(), true);
    }
}

if (! function_exists('mask_phone')) {
    /** نمایش امن شماره در لاگ: 0936***8962 */
    function mask_phone(?string $phone): string
    {
        $phone = (string) $phone;

        return strlen($phone) >= 7
            ? substr($phone, 0, 4) . '***' . substr($phone, -4)
            : '***';
    }
}

if (! function_exists('fa_date')) {
    function fa_date(\DateTimeInterface|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        try {
            $formatter = new \IntlDateFormatter(
                'fa_IR@calendar=persian',
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE
            );

            return $formatter->format(new \DateTimeImmutable((string) (is_string($date) ? $date : $date->format('c'))));
        } catch (\Throwable) {
            return fa_digits(\Carbon\Carbon::parse($date)->format('Y/m/d'));
        }
    }
}
