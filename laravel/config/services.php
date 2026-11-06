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
    ],

];
