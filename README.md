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

## به‌روزرسانی سایت با EnvKit (خواندن دستور Git از متغیرهای محیطی)

دستور به‌روزرسانی داخل کد هاردکد نشده؛ از محیط خوانده می‌شود تا EnvKit بتواند آن را تعیین کند.

**۱) متغیرها را در EnvKit (یا `.env`) ست کنید**

```dotenv
ALLOW_WEB_UPDATES=true
DEPLOY_WEBHOOK_TOKEN=یک-توکن-تصادفی-بلند
UPDATE_GIT_REMOTE=origin
UPDATE_GIT_BRANCH=main
# اختیاری: فرمان دلخواه Git به‌جای «git pull --ff-only origin main»
UPDATE_GIT_COMMAND="git fetch --prune origin && git reset --hard origin/main && git clean -fd"
UPDATE_ALLOWED_IPS=            # مثلاً IP های EnvKit؛ خالی = بدون محدودیت
UPDATE_FINAL_COMMAND=          # مثلاً: php artisan queue:restart
```

**۲) به‌روزرسانی را صدا بزنید (سه راه، همه با همان دستور محیطی)**

```bash
# الف) وب‌هوک — مناسب Automation در EnvKit
curl -X POST "https://nobatesalamat.ir/deploy/$DEPLOY_WEBHOOK_TOKEN"
curl -X POST "https://nobatesalamat.ir/deploy/$DEPLOY_WEBHOOK_TOKEN?dry_run=1"  # فقط بررسی
curl "https://nobatesalamat.ir/deploy/$DEPLOY_WEBHOOK_TOKEN"                    # وضعیت
# یا با هدر: -H "X-Deploy-Token: $DEPLOY_WEBHOOK_TOKEN"

# ب) artisan روی سرور (SSH / Agent)
php artisan site:update --check   # چند کامیت جدید داریم؟
php artisan site:update --dry-run # دستورها را نشان بده
php artisan site:update           # اجرای کامل

# ج) دکمهٔ «به‌روزرسانی پروژه» در پنل: /admin/update
```

**۳) چه اتفاقی می‌افتد**

`git` (دستور محیطی) → `composer install --no-dev` → `php artisan migrate --force` → `optimize:clear` + `config/route/view cache` → `UPDATE_FINAL_COMMAND`؛ خروجی هر مرحله در جدول `update_logs` ذخیره می‌شود و در پنل قابل مشاهده است.

اگر سایت همین حالا آخرین نسخه باشد، وب‌هوک کاری انجام نمی‌دهد و `updated: false` برمی‌گرداند. وب‌هوک توکن‌محور است، از CSRF مستثنی شده و throttle ۱۰ درخواست در دقیقه دارد.

## ساختار دسترسی مدیران
`role` کاربر: `user` | `admin` | `super_admin`
ستون `permissions` (JSON): هر مدیر می‌تواند زیرمجموعه‌ای از:
`content` (محتوای سایت)، `doctors` (پزشکان)، `appointments` (نوبت‌ها)، `updates` (به‌روزرسانی)
مدیر ارشد همیشه دسترسی کامل دارد و فقط او می‌تواند مدیر اضافه/حذف و سطح دسترسی تعیین کند.
