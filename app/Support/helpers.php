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
    /**
     * قالب‌بندی مبلغ به تومان با ارقام فارسی.
     *
     * مقدار null (مثلاً حاصل Appointment::sum('price') روی جدول خالی) باید
     * صفر در نظر گرفته شود؛ در غیر این صورت TypeError و خطای 500 می‌داد.
     */
    function fa_money(int|float|string|null $amount = 0): string
    {
        return fa_digits(number_format((float) ($amount ?? 0))) . ' تومان';
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

if (! function_exists('admin_menu')) {
    /**
     * آیتم‌های منوی پنل مدیریت بر اساس دسترسی کاربر جاری.
     *
     * محاسبهٔ منو در PHP انجام می‌شود (نه در یک بلوک @php داخل قالب) تا
     * قالب فقط یک حلقهٔ ساده داشته باشد و خطای «Undefined variable $menu»
     * در هیچ حالتی رخ ندهد.
     *
     * @return array<int, array{0: string, 1: string, 2: bool}>
     */
    function admin_menu(?object $admin = null): array
    {
        $admin ??= auth()->user();

        $super = $admin !== null && method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin();

        $can = static fn (string $permission): bool => $admin !== null
            && method_exists($admin, 'hasPermission')
            && $admin->hasPermission($permission);

        return [
            ['admin.dashboard', 'نمای کلی', true],
            ['admin.content', 'محتوای سایت', $can('content')],
            ['admin.doctors', 'مدیریت پزشکان', $can('doctors')],
            ['admin.specialties', 'حوزه‌های کاری', $can('doctors')],
            ['admin.articles', 'مقالات', $can('content')],
            ['admin.faqs', 'سؤالات متداول', $can('content')],
            ['admin.appointments', 'مدیریت نوبت‌ها', $can('appointments')],
            ['admin.admins', 'مدیران و دسترسی‌ها', $super],
            ['admin.update', 'بروزرسانی پروژه', $can('updates')],
        ];
    }
}

if (! function_exists('is_placeholder_phone')) {
    /**
     * شماره‌های نمونهٔ نصب (مثل 09120000000) هرگز نباید دسترسی مدیریت داشته
     * باشند؛ مگر اینکه صریحاً در ADMIN_PHONE/OWNER_PHONE تعیین شده باشند.
     */
    function is_placeholder_phone(?string $phone): bool
    {
        if ($phone === null || $phone === '') {
            return false;
        }

        $placeholders = (array) config('services.nobat.placeholder_phones', ['09120000000']);

        return in_array(normalize_phone($phone), array_map('normalize_phone', $placeholders), true)
            && ! is_admin_phone($phone);
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

if (! function_exists('media_url')) {
    /**
     * آدرس قابل نمایش برای تصویر.
     * مقادیر مجاز: URL کامل، مسیر نسبی مثل /uploads/doctors/x.jpg، یا خالی.
     */
    function media_url(?string $path, string $fallback = '/images/placeholder.svg'): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset(ltrim($fallback, '/'));
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}

if (! function_exists('store_uploaded_image')) {
    /**
     * ذخیرهٔ عکس آپلودی در public/uploads/{folder} و برگرداندن مسیر آن.
     *
     * آپلود داخل public نگه داشته می‌شود (نه storage) تا روی هاست ویندوزی
     * نیاز به `php artisan storage:link` و ساخت junction نباشد.
     */
    function store_uploaded_image(?\Illuminate\Http\UploadedFile $file, string $folder = 'misc'): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower($file->getClientOriginalExtension() ?: (string) $file->extension());

        if (! in_array($extension, $allowed, true)) {
            $extension = 'jpg';
        }

        $folder = trim(preg_replace('/[^a-z0-9_-]/i', '', $folder) ?: 'misc', '/');
        $directory = public_path('uploads/' . $folder);

        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($directory, $name);

        return '/uploads/' . $folder . '/' . $name;
    }
}

if (! function_exists('delete_uploaded_image')) {
    /** حذف فایل آپلودی از public/uploads (فقط مسیرهای داخلی) */
    function delete_uploaded_image(?string $url): void
    {
        $url = trim((string) $url);

        if (! str_starts_with($url, '/uploads/')) {
            return;
        }

        $path = public_path(ltrim($url, '/'));

        if (is_file($path)) {
            @unlink($path);
        }
    }
}

if (! function_exists('persian_slug')) {
    /** ساخت slug یکتا از عنوان فارسی/انگلیسی */
    function persian_slug(string $title, string $prefix = 'item'): string
    {
        $slug = \Illuminate\Support\Str::slug($title);

        if ($slug === '' || $slug === null) {
            $slug = $prefix . '-' . \Illuminate\Support\Str::random(8);
        }

        return \Illuminate\Support\Str::limit($slug, 120, '');
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

if (! function_exists('enamad_code')) {
    /**
     * کد ای‌نماد برای متاتگ <head>.
     *
     * همیشه یک رشتهٔ عددیِ انگلیسی برمی‌گرداند؛ چون سرویس تأیید enamad.ir
     * مقدار متاتگ را دقیق مقایسه می‌کند و رقم فارسی/فاصله/کاراکتر اضافه
     * باعث رد شدن «تایید متاتگ» می‌شود.
     */
    function enamad_code(): string
    {
        $default = '57649165';

        try {
            $value = (string) \App\Models\SiteSetting::get('enamad_code', $default);
        } catch (\Throwable $e) {
            return $default;
        }

        $value = strtr(trim($value), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $digits = preg_replace('/[^0-9]/', '', $value) ?? '';

        return $digits !== '' ? $digits : $default;
    }
}
