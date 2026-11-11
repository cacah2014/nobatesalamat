# نوبت سلامت — نسخهٔ Laravel

پلتفرم نوبت‌دهی آنلاین پزشکان برای **nobatesalamat.ir** با Laravel 12، Tailwind CSS و MySQL؛ دقیقاً معادل نسخهٔ اجراشدهٔ این پیش‌نمایش.

## امکانات

- ورود/ثبت‌نام کاربر فقط با **شماره موبایل + کد تأیید (OTP)** و اتصال به **پنل پیامک ستارگان (srscrm.ir)**
- جست‌وجو و فیلتر پزشکان بر اساس تخصص، شهر و مشاورهٔ آنلاین
- رزرو نوبت حضوری/آنلاین با کد رهگیری، پنل کاربری و لغو نوبت
- **پنل مدیریت کامل**: ویرایش محتوای سایت، مدیریت پزشکان، نوبت‌ها، آمار
- **چند مدیر با دسترسی دانه‌به‌دانه**: مدیر ارشد دسترسی بخش‌های «محتوا / پزشکان / نوبت‌ها / به‌روزرسانی» را برای هر مدیر فعال می‌کند
- **به‌روزرسانی پروژه** از داخل پنل: `git pull --ff-only` ← Dependency (`composer install`) ← Database (`php artisan migrate --force`) ← Code/Cache ← Update
- سئوی کامل: Sitemap پویا، Robots، Canonical، Open Graph و JSON-LD (FAQ / Physician / Breadcrumb)

## شروع سریع (ویندوز / EnvKit)

اگر هنگام باز کردن سایت این خطا را دیدید:

```
Failed opening required '.../vendor/autoload.php'
Fatal error: Uncaught Error: Failed opening required '.../vendor/autoload.php'
```

یعنی کد پروژه هست ولی **وابستگی‌های Composer نصب نشده**. یک بار در ریشهٔ پروژه (همان پوشه‌ای که `composer.json` دارد) یکی از این دو را اجرا کنید:

```powershell
# ویندوز (PowerShell) — نصب کامل + SQLite برای اجرای سریع
powershell -ExecutionPolicy Bypass -File scripts\install.ps1 -Sqlite -Seed
```

```bash
# لینوکس / مک / WSL
bash scripts/install.sh --sqlite --seed
```

این اسکریپت `composer install` را اجرا می‌کند، `.env` می‌سازد، `APP_KEY` را تولید می‌کند و (با `--sqlite`) دیتابیس SQLite را می‌سازد و `migrate --seed` می‌زند.

- **با MySQL:** مقادیر `DB_*` را در `.env` بگذارید و اسکریپت را بدون `--sqlite` اجرا کنید، سپس `php artisan migrate --seed`.
- **بدون اسکریپت:** `composer install` → `copy .env.example .env` → `php artisan key:generate` → `php artisan migrate --seed`.
- اگر `composer` نصب نیست: <https://getcomposer.org/download/> (یا `winget install Composer`).

**پس از هر به‌روزرسانی کد (مهم)**

اگر فایل‌ها را با ZIP یا `git pull` جای‌گزین می‌کنید، کلاس‌های جدید تا وقتی autoload بازسازی نشود شناخته نمی‌شوند و ممکن است خطاهایی مثل
`Cannot redeclare class ...` یا `Class ... not found` ببینید:

```bash
composer dump-autoload -o
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

**اجرای پروژه با سرور توکار PHP:**

```bash
php -S 127.0.0.1:8000 -t public server.php     # یا: php artisan serve
```

در EnvKit هم نقطهٔ ورود همان `public/index.php` است؛ کافی است یک بار پوشهٔ `vendor` ساخته شود (فایل `.gitignore` آن را از گیت خارج کرده، چون باید روی هر ماشین نصب شود).

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
# مقادیر DB_* ، SMS_* ، ADMIN_PHONE و APP_URL را تنظیم کنید

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
از همان صفحهٔ `/login` با شمارهٔ مدیر ارشد وارد شوید (پیش‌فرض: `09120000000`)؛ سیستم خودکار به `/admin` هدایت می‌کند. اگر `SMS_TOKEN` تنظیم نشده باشد و `APP_ENV=local` باشد، کد آزمایشی در پاسخ نمایش داده می‌شود؛ در `production` بدون توکن، ورود با پیام خطای واضح متوقف می‌شود.

## به‌روزرسانی از پنل
در `/admin/updates` دکمهٔ اجرا این مراحل را طی می‌کند:
1. `git pull --ff-only`
2. Dependency → `composer install --no-dev --optimize-autoloader`
3. Database → `php artisan migrate --force`
4. Code → `php artisan config:cache route:cache view:cache`
5. Update → فرمان نهایی اختیاری (`UPDATE_FINAL_COMMAND`)

برای امنیت، این قابلیت فقط با `ALLOW_WEB_UPDATES=true` فعال می‌شود و خروجی هر مرحله در جدول `update_logs` ثبت می‌گردد.

## خطای «حساب شما غیرفعال است»

این پیام یعنی مقدار ستون `status` آن کاربر `active` نیست — یا `NULL` است (مثلاً جدول `users` از نسخهٔ قدیمی پروژه مانده و مهاجرت اجرا نشده).

```bash
php artisan app:check                        # بررسی جدول‌ها، ستون‌ها و وضعیت کاربران
php artisan migrate                          # ستون status را می‌سازد و ردیف‌های NULL را active می‌کند
php artisan user:status --admins             # فهرست مدیران و فعال/غیرفعال بودنشان
php artisan user:status 09120000000 active   # فعال‌سازی یک شماره
php artisan user:status 09121234567 blocked  # مسدود کردن یک شماره
```

## پنل پیامک (ستارگان — srscrm.ir)

ارسال کد ورود از طریق API پنل پیامک ستارگان انجام می‌شود:

```
POST https://srscrm.ir/api/sms/send
Authorization: Bearer {SMS_TOKEN}
{ "to": "09121234567", "message": "...", "gateway": "1000" }
```

**تنظیمات (`.env`)**

```dotenv
SMS_TOKEN=توکن-API-پنل
SMS_GATEWAY=1000                     # شمارهٔ خط فرستنده (نه شناسهٔ خط)
SMS_OTP_TEMPLATE="کد ورود شما به نوبت سلامت: %s"
SMS_BASE_URL=https://srscrm.ir/api/sms/send
SMS_TIMEOUT=15
```

**تست سریع**

```bash
php artisan sms:test --check          # فقط بررسی توکن/خط/قالب
php artisan sms:test 09121234567      # ارسال پیامک آزمایشی
```

کدهای خطای API (توکن نامعتبر، IP غیرمجاز، نبود اعتبار، خط غیرفعال…) به پیام فارسی تبدیل می‌شوند و در `storage/logs/laravel.log` با تگ `[SMS]` ثبت می‌گردند. اگر `SMS_TOKEN` خالی باشد، در `APP_ENV=local` پیامک شبیه‌سازی و کد در پاسخ ورود نمایش داده می‌شود.

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

**۲.۵) اسکریپت آماده برای EnvKit**

اگر EnvKit فقط می‌تواند «دستور» اجرا کند (نه HTTP)، همین یک خط را در Automation بگذارید:

```bash
bash scripts/deploy.sh              # اجرای کامل
bash scripts/deploy.sh --check      # فقط بررسی نسخهٔ جدید
bash scripts/deploy.sh --dry-run    # نمایش دستورهای مؤثر
```

خروجی در `storage/logs/deploy.log` ذخیره می‌شود و کد خروج ۰/۱ برای EnvKit قابل خواندن است.

**۳) چه اتفاقی می‌افتد**

`git` (دستور محیطی) → `composer install --no-dev` → `php artisan migrate --force` → `optimize:clear` + `config/route/view cache` → `UPDATE_FINAL_COMMAND`؛ خروجی هر مرحله در جدول `update_logs` ذخیره می‌شود و در پنل قابل مشاهده است.

اگر سایت همین حالا آخرین نسخه باشد، وب‌هوک کاری انجام نمی‌دهد و `updated: false` برمی‌گرداند. وب‌هوک توکن‌محور است، از CSRF مستثنی شده و throttle ۱۰ درخواست در دقیقه دارد.

## ساختار دسترسی مدیران
`role` کاربر: `user` | `admin` | `super_admin`
ستون `permissions` (JSON): هر مدیر می‌تواند زیرمجموعه‌ای از:
`content` (محتوای سایت)، `doctors` (پزشکان)، `appointments` (نوبت‌ها)، `updates` (به‌روزرسانی)
مدیر ارشد همیشه دسترسی کامل دارد و فقط او می‌تواند مدیر اضافه/حذف و سطح دسترسی تعیین کند.
