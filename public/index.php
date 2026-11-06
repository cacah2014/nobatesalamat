<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| بررسی نصب وابستگی‌ها
|--------------------------------------------------------------------------
| اگر پوشهٔ vendor وجود نداشته باشد (مثلاً پروژه تازه clone شده و هنوز
| composer install اجرا نشده)، به‌جای خطای مرگبار PHP یک پیام راهنما
| نمایش داده می‌شود تا در EnvKit / هاست هم قابل فهم باشد.
*/
if (! is_file(__DIR__.'/../vendor/autoload.php')) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');

    echo <<<'HTML'
<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><title>نصب وابستگی‌های Composer</title></head>
<body style="margin:0;padding:40px;background:#f5f9f8;font-family:Tahoma,Arial,sans-serif;line-height:2;color:#123b3a">
<div style="max-width:760px;margin:0 auto;background:#fff;border:1px solid #dbe7e4;border-radius:16px;padding:28px">
  <h1 style="margin:0 0 12px;font-size:20px;color:#0d9488">پوشهٔ vendor پیدا نشد</h1>
  <p style="margin:0 0 16px">کد پروژه موجود است، اما وابستگی‌های Composer نصب نشده‌اند. یک بار دستورهای زیر را در
  <strong>ریشهٔ پروژه</strong> (پوشه‌ای که <code>composer.json</code> در آن است) اجرا کنید:</p>
  <pre dir="ltr" style="background:#102c2b;color:#b8e0da;padding:14px;border-radius:10px;overflow:auto;text-align:left">composer install</pre>
  <p style="margin:16px 0 8px">سپس فایل تنظیمات و کلید برنامه:</p>
  <pre dir="ltr" style="background:#102c2b;color:#b8e0da;padding:14px;border-radius:10px;overflow:auto;text-align:left">copy .env.example .env      # ویندوز
cp .env.example .env        # لینوکس/مک
php artisan key:generate</pre>
  <p style="margin:16px 0 8px">و در پایان ساخت جداول:</p>
  <pre dir="ltr" style="background:#102c2b;color:#b8e0da;padding:14px;border-radius:10px;overflow:auto;text-align:left">php artisan migrate --seed</pre>
  <p style="margin:16px 0 0;font-size:13px;color:#5f7c7a">یا به‌صورت یک‌مرحله‌ای: <code dir="ltr">powershell -File scripts/install.ps1 -Sqlite -Seed</code>
  (ویندوز) / <code dir="ltr">bash scripts/install.sh --sqlite --seed</code> (لینوکس)</p>
</div>
</body>
</html>
HTML;

    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
