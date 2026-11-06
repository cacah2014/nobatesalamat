# نوبت سلامت — نسخهٔ Laravel

پلتفرم نوبت‌دهی آنلاین پزشکان برای **nobatesalamat.ir** با Laravel 12، Tailwind CSS و MySQL؛ دقیقاً معادل نسخهٔ اجراشدهٔ این پیش‌نمایش.

## امکانات

- ورود/ثبت‌نام کاربر فقط با **شماره موبایل + کد تأیید (OTP)** و اتصال به کاوه‌نگار
- جست‌وجو و فیلتر پزشکان بر اساس تخصص، شهر و مشاورهٔ آنلاین
- رزرو نوبت حضوری/آنلاین با کد رهگیری، پنل کاربری و لغو نوبت
- **پنل مدیریت کامل**: ویرایش محتوای سایت، مدیریت پزشکان، نوبت‌ها، آمار
- **چند مدیر با دسترسی دانه‌به‌دانه**: مدیر ارشد دسترسی بخش‌های «محتوا / پزشکان / نوبت‌ها / به‌روزرسانی» را برای هر مدیر فعال می‌کند
- **به‌روزرسانی پروژه** از داخل پنل: `git pull --ff-only` ← Dependency (`composer install`) ← Database (`php artisan migrate --force`) ← Code/Cache ← Update
- سئوی کامل: Sitemap پویا، Robots، Canonical، Open Graph و JSON-LD (FAQ / Physician / Breadcrumb)

## استقرار گام‌به‌گام (VPS یا هاست SSH)

> این ریپو اکنون فقط شامل کد لاراول است و خودِ ریشهٔ پروژه، پروژهٔ لاراول محسوب می‌شود
> (app / bootstrap / config / database / public / resources / routes / storage).

```bash
# ۱) دریافت کد و نصب وابستگی‌ها
git clone https://github.com/cacah2014/nobatesalamat.git nobatesalamat
cd nobatesalamat
composer install

# ۲) تنظیمات
cp .env.example .env
php artisan key:generate
# مقادیر DB_* ، KAVENEGAR_* ، ADMIN_PHONE و APP_URL را تنظیم کنید

# ۳) دیتابیس MySQL
mysql -u root -p -e "CREATE DATABASE nobatesalamat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# ۴) جداول + داده‌های اولیه (۸ تخصص، ۴ پزشک، تنظیمات، مقالات، مدیر ارشد)
php artisan migrate --seed

# ۵) بهینه‌سازی
php artisan storage:link
php artisan optimize
```

### نکتهٔ وب‌سرور
مستندات لاراول را روی پوشهٔ `public/` تنظیم کنید (DocumentRoot = `nobatesalamat/public`).

## ورود مدیر
از همان صفحهٔ `/login` با شمارهٔ مدیر ارشد وارد شوید (پیش‌فرض: `09120000000`)؛ سیستم خوددکار به `/admin` هدایت می‌کند. اگر کلید کاوه‌نگار تنظیم نشده باشد و `APP_ENV=local` باشد، کد آزمایشی در پاسخ نمایش داده می‌شود.

## به‌روزرسانی از پنل
در `/admin/updates` دکمهٔ اجرا این مراحل را طی می‌کند:
1. `git pull --ff-only`
2. Dependency → `composer install --no-dev --optimize-autoloader`
3. Database → `php artisan migrate --force`
4. Code → `php artisan config:cache route:cache view:cache`
5. Update → فرمان نهایی اختیاری (`UPDATE_FINAL_COMMAND`)

برای امنیت، این قابلیت فقط با `ALLOW_WEB_UPDATES=true` فعال می‌شود و خروجی هر مرحله در جدول `update_logs` ثبت می‌گردد.

## ساختار دسترسی مدیران
`role` کاربر: `user` | `admin` | `super_admin`
ستون `permissions` (JSON): هر مدیر می‌تواند زیرمجموعه‌ای از:
`content` (محتوای سایت)، `doctors` (پزشکان)، `appointments` (نوبت‌ها)، `updates` (به‌روزرسانی)
مدیر ارشد همیشه دسترسی کامل دارد و فقط او می‌تواند مدیر اضافه/حذف و سطح دسترسی تعیین کند.
