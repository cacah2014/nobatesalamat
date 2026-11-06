<?php

/**
 * روتر ساده برای سرور توکار PHP (بدون نیاز به Apache/Nginx).
 *
 *   php -S 127.0.0.1:8000 -t public server.php
 *
 * فایل‌های استاتیک (css/js/تصاویر) مستقیم سرو می‌شوند و بقیهٔ مسیرها
 * به front controller یعنی public/index.php می‌روند تا URL های تمیز
 * مثل /doctors و /admin کار کنند.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if ($uri !== '/' && is_file(__DIR__.'/public'.$uri)) {
    return false;
}

require __DIR__.'/public/index.php';
