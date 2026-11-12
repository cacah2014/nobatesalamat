{{--
    صفحهٔ خطای ۵۰۰ — مستقل از layout و دیتابیس (CSS درون‌خطی).
    پیام کوتاه خطا نمایش داده می‌شود تا در صورت بروز مشکل،
    بتوان آن را برای پیگیری خواند/اسکرین‌شات گرفت.
--}}
@php
    $e = $exception ?? null;
    $code = $e ? substr(sha1($e->getMessage() . $e->getFile() . $e->getLine()), 0, 8) : '----------';
    $type = $e ? class_basename($e) : 'خطای ناشناخته';
    $message = $e ? \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', $e->getMessage()), 220) : '';
    $origin = $e ? basename($e->getFile()) . ':' . $e->getLine() : '';
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>خطای سرور — نوبت سلامت</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
            font-family: Tahoma, Arial, sans-serif; background: #0f1f1e; color: #e7f3f1;
        }
        .box { max-width: 560px; width: 100%; background: #16302e; border: 1px solid #24504c; border-radius: 18px; padding: 28px; }
        h1 { margin: 0 0 6px; font-size: 18px; }
        p { margin: 0 0 14px; font-size: 13px; line-height: 2; color: #b9cfcc; }
        .code { display: inline-block; padding: 6px 12px; border-radius: 999px; background: #0d4d49; color: #8ce9d2; font-size: 12px; font-weight: bold; letter-spacing: 1px; }
        .detail { margin-top: 16px; padding: 14px; border-radius: 12px; background: #102523; border: 1px dashed #2b5c57; font-size: 11px; line-height: 2; word-break: break-word; }
        .detail b { color: #8ce9d2; font-weight: normal; }
        a { color: #8ce9d2; }
    </style>
</head>
<body>
    <div class="box">
        <span class="code">خطا ۵۰۰ · کد {{ $code }}</span>
        <h1 style="margin-top:14px">سرور نتوانست این صفحه را آماده کند</h1>
        <p>
            اگر این خطا فقط یک‌بار دیدید، صفحه را <strong>رفرش</strong> کنید؛ در بیشتر موارد مشکل برطرف می‌شود.
            اگر تکرار شد، کد بالا را برای پشتیبانی بفرستید.
        </p>

        <div class="detail">
            <div><b>نوع خطا:</b> {{ $type }}</div>
            @if($origin)
                <div><b>محل:</b> {{ $origin }}</div>
            @endif
            @if($message !== '')
                <div><b>پیام:</b> {{ $message }}</div>
            @endif
        </div>

        <p style="margin-top:16px">
            <a href="{{ url('/') }}">بازگشت به صفحهٔ اصلی</a> ·
            <a href="{{ url('/login') }}">ورود</a>
        </p>
    </div>
</body>
</html>
