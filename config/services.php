<?php

return [

    /*
    |------------------------------------------------------------------
    | پنل پیامک ستارگان (srscrm.ir) — ارسال کد ورود
    |------------------------------------------------------------------
    | POST https://srscrm.ir/api/sms/send  با هدر Authorization: Bearer
    | پارامترها: to، message، gateway (شمارهٔ خط فرستنده، نه شناسه)
    */
    'sms' => [
        'base_url' => env('SMS_BASE_URL', 'https://srscrm.ir/api/sms/send'),
        'token' => env('SMS_TOKEN'),
        'gateway' => env('SMS_GATEWAY'),
        'timeout' => (int) env('SMS_TIMEOUT', 15),
        'otp_template' => env('SMS_OTP_TEMPLATE', 'کد ورود شما به نوبت سلامت: %s'),
    ],

    // تنظیمات اختصاصی نوبت سلامت
    'nobat' => [
        // شمارهٔ «مدیر اصلی» (مدیر ارشد). با اولین ورود همین شماره، حساب
        // به‌صورت خودکار super_admin می‌شود. چند شماره را با کاما جدا کنید:
        //   ADMIN_PHONE=09364528962,09121234567
        'admin_phone' => env('ADMIN_PHONE', '09364528962'),
        'allow_web_updates' => filter_var(env('ALLOW_WEB_UPDATES', false), FILTER_VALIDATE_BOOL),
        'update_final_command' => env('UPDATE_FINAL_COMMAND'),
        // طول عمر کد، فاصلهٔ مجاز ارسال مجدد و حداکثر تلاش اشتباه
        'otp_ttl_seconds' => (int) env('OTP_TTL_SECONDS', 180),
        'otp_resend_seconds' => (int) env('OTP_RESEND_SECONDS', 55),
        'otp_max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),

        /*
        |------------------------------------------------------------------
        | به‌روزرسانی سایت از روی Git (قابل تنظیم با EnvKit)
        |------------------------------------------------------------------
        | همهٔ مقادیر از متغیرهای محیطی خوانده می‌شوند؛ کافی است EnvKit
        | این متغیرها را روی سرور ست کند (یا در .env بنویسد) و سپس
        | وب‌هوک/دستور artisan را صدا بزند.
        */
        'update' => [
            'allowed' => filter_var(env('ALLOW_WEB_UPDATES', false), FILTER_VALIDATE_BOOL),

            // توکن مشترک با EnvKit؛ اگر خالی باشد وب‌هوک غیرفعال است
            'token' => env('DEPLOY_WEBHOOK_TOKEN'),

            // لیست IP های مجاز برای صدا زدن وب‌هوک (خالی = محدودیتی نیست)
            'allowed_ips' => array_values(array_filter(array_map(
                'trim',
                explode(',', (string) env('UPDATE_ALLOWED_IPS', ''))
            ))),

            'git_remote' => env('UPDATE_GIT_REMOTE', 'origin'),
            'git_branch' => env('UPDATE_GIT_BRANCH', 'main'),

            // اگر EnvKit فرمان دلخواهی داشته باشد، جای فرمان پیش‌فرض git pull را می‌گیرد
            // مثال: UPDATE_GIT_COMMAND="git fetch --all && git reset --hard origin/main"
            'git_command' => env('UPDATE_GIT_COMMAND'),

            'composer_binary' => env('UPDATE_COMPOSER_BINARY', 'composer'),
            'php_binary' => env('UPDATE_PHP_BINARY', PHP_BINARY),
            'timeout' => (int) env('UPDATE_TIMEOUT', 900),

            // فرمان نهایی اختیاری (مثلاً: php artisan queue:restart یا supervisorctl restart ...)
            'final_command' => env('UPDATE_FINAL_COMMAND'),
        ],
    ],

];
