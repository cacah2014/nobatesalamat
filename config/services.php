<?php

return [

    // سرویس پیامک کاوه‌نگار برای ارسال کد ورود
    'kavenegar' => [
        'api_key' => env('KAVENEGAR_API_KEY'),
        'template' => env('KAVENEGAR_TEMPLATE', 'login'),
    ],

    // تنظیمات اختصاصی نوبت سلامت
    'nobat' => [
        'admin_phone' => env('ADMIN_PHONE', '09120000000'),
        'allow_web_updates' => filter_var(env('ALLOW_WEB_UPDATES', false), FILTER_VALIDATE_BOOL),
        'update_final_command' => env('UPDATE_FINAL_COMMAND'),
        'otp_ttl_seconds' => 120,
        'otp_resend_seconds' => 55,

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
