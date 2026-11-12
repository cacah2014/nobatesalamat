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

## ورود مدیر اصلی سایت

> **نکتهٔ مهم:** شمارهٔ `09364528962` در `config/services.php` به‌عنوان `OWNER_PHONE` **همیشه** مدیر ارشد است — حتی اگر `ADMIN_PHONE` در `.env` قدیمی مانده باشد. سه لایهٔ محافظ این را تضمین می‌کنند:
> 1. مهاجرت `2026_01_01_000006_promote_main_admin` — در هر `php artisan migrate` نقش را در دیتابیس اصلاح می‌کند (SiteUpdater هم آن را اجرا می‌کند).
> 2. میدل‌ور `PromoteMainAdmin` — با اولین درخواست بعد از به‌روزرسانی، حتی با نشست قبلی، نقش را اصلاح می‌کند.
> 3. ارتقای خودکار در `verify` هنگام ورود با کد پیامکی.
>
> برای اطمینان: `php artisan app:check` و بعد رفرش صفحه `https://nobatesalamat.ir/admin`.

مدیر اصلی با **شمارهٔ موبایل** و کد پیامکی وارد می‌شود (رمز عبوری وجود ندارد). شمارهٔ مدیر اصلی در `.env` تعیین می‌شود:

```env
ADMIN_PHONE=09364528962
# چند مدیر اصلی: ADMIN_PHONE=09364528962,09121234567
```

بعد از تنظیم:

```bash
php artisan config:clear
```

سپس در `/login` همان شماره را وارد کنید و کد پیامکی را بزنید. **با اولین ورود**، حساب آن شماره به‌طور خودکار `super_admin` و فعال می‌شود (حتی اگر از قبل کاربر عادی بوده یا مایگریشن/سید اجرا نشده باشد) و مستقیم به `/admin` می‌رود.

دستورهای مدیریتی:

```bash
php artisan admin:grant --list                 # فهرست مدیران + اینکه کدام «مدیر اصلی» است
php artisan admin:grant 09364528962            # ثبت/ارتقا به مدیر ارشد
php artisan admin:grant 09364528962 --name="دکتر رضایی"
php artisan admin:grant 09121234567 --role=admin   # مدیر عادی (دسترسی محدود)
php artisan admin:grant 09121234567 --revoke   # حذف دسترسی
php artisan app:check                          # نمایش وضعیت شمارهٔ مدیر اصلی
```

اگر بدون `ADMIN_PHONE` بخواهید وارد شوید، حساب عادی ساخته می‌شود و به `/panel` می‌رود؛ برای دسترسی به `/admin` باید شماره در `ADMIN_PHONE` باشد (یا با `admin:grant` مدیر شود).

## صفحهٔ خطای ۵۰۰ (عیب‌یابی)

صفحهٔ ۵۰۰ حالا فارسی و مستقل از دیتابیس/CSS است و **کد خطا + نوع خطا + محل (فایل:خط) + پیام کوتاه** را نشان می‌دهد؛ بنابراین بدون دسترسی به لاگ می‌توان فهمید چه چیزی خراب است (کافی است اسکرین‌شات بگیرید). همان اطلاعات در \`/health\` هم به‌صورت \`last_error\` آمده است.

اگر خطای ۵۰۰ **فقط یک‌بار بعد از ورود** دیده شد و با رفرش برطرف گردید، علت معمولاً یکی از این دو است و هر دو در کد بسته شده‌اند:

- نبودن ستون \`users.remember_token\` → کوکی «مرا به خاطر بسپار» روی ستون ناموجود کوئری می‌زد. الان اگر ستون نباشد، ورود بدون remember انجام می‌شود.
- خطا در ارتقای خودکار مدیر اصلی → داخل try/catch است و هرگز ۵۰۰ نمی‌سازد.

## بررسی سریع وضعیت سرویس (/health)

```bash
curl https://nobatesalamat.ir/health
```

خروجی JSON: نسخهٔ PHP و Laravel، کامیت مستقرشده، نقش و فعال‌بودن شمارهٔ مدیر اصلی، و تعداد رکوردهای users/doctors/appointments. اطلاعات حساس (شماره کامل، توکن) نمایش داده نمی‌شود.

## خطای «سایت در دسترس نیست» / DNS_PROBE_FINISHED_NXDOMAIN

این خطا از **مرورگر** می‌آید نه از برنامه: مرورگر نتوانسته نام دامنه را به IP تبدیل کند، پس هیچ درخواستی به سرور نرسیده است. برای تفکیک، وضعیت DNS را از بیرون بررسی کنید:

```bash
nslookup nobatesalamat.ir            # با DNS فعلی سیستم
nslookup nobatesalamat.ir 8.8.8.8    # با DNS گوگل
nslookup nobatesalamat.ir 178.22.122.100   # شکن
```

- اگر با ۸.۸.۸.۸ جواب داد ولی با DNS پیش‌فرض نه → مشکل رزولور دستگاه/سرویس‌دهنده است، نه سایت. روی ویندوز: `ipconfig /flushdns` و در کروم `chrome://net-internals/#dns` → *Clear host cache*، سپس DNS را به شکن (178.22.122.100 / 185.51.200.2) یا ۴۰۳ (10.202.10.202 / 10.202.10.102) تغییر دهید.
- اگر هیچ‌کدام جواب نداد → رکورد A دامنه یا نیم‌سرورهای آن در پنل دامنه مشکل دارد.
- رکورد `www` جدا از دامنهٔ اصلی است؛ اگر وجود نداشته باشد `www.nobatesalamat.ir` همیشه NXDOMAIN می‌دهد. یک CNAME برای `www` به `nobatesalamat.ir` بسازید (ریدایرکت www→بدون www در `public/.htaccess` انجام می‌شود).

## استایل و فونت‌ها بدون CDN (رفع «سایت بالا نمی‌آید» روی بعضی دستگاه‌ها)

قبلاً Tailwind و فونت وزیرمتن از CDN خارجی در `<head>` لود می‌شدند (`cdn.tailwindcss.com` و `cdn.jsdelivr.net`). در شبکه‌هایی که به این دامنه‌ها دسترسی ندارند، همان دو خط **رندر صفحه را قفل می‌کردند** و سایت روی بعضی گوشی‌ها/کامپیوترها بالا نمی‌آمد؛ ضمناً Tailwind CDN کل استایل را در مرورگر می‌ساخت که روی گوشی‌های ضعیف کند است.

اکنون همه چیز محلی است:

```
public/css/app.css                  ← CSS کامپایل‌شده (۳۳KB، بدون CDN)
public/fonts/vazirmatn-variable.woff2 ← فونت (۱۰۹KB، متغیر ۱۰۰–۹۰۰)
resources/css/app.css               ← منبع Tailwind
tailwind.config.js                  ← رنگ‌های brand/coral/ink/cream
```

بعد از تغییر کلاس‌های Tailwind در Bladeها، CSS را دوباره بسازید (نیاز به Node فقط روی سیستم توسعه):

```bash
npx tailwindcss@3.4.17 -i resources/css/app.css -o public/css/app.css --minify
```

بررسی سریع روی سرور:

```bash
curl -I https://دامنه/css/app.css      # باید 200 با type text/css
curl -I https://دامنه/fonts/vazirmatn-variable.woff2
```

## خطای «حساب شما غیرفعال است»

این پیام یعنی مقدار ستون `status` آن کاربر `active` نیست — یا `NULL` است (مثلاً جدول `users` از نسخهٔ قدیمی پروژه مانده و مهاجرت اجرا نشده).

```bash
php artisan app:check                        # بررسی جدول‌ها، ستون‌ها و وضعیت کاربران
php artisan migrate                          # ستون status را می‌سازد و ردیف‌های NULL را active می‌کند
php artisan user:status --admins             # فهرست مدیران و فعال/غیرفعال بودنشان
php artisan user:status 09120000000 active   # فعال‌سازی یک شماره
php artisan user:status 09121234567 blocked  # مسدود کردن یک شماره
```

## بعد از زدن کد، «Server error» می‌آید ولی با رفرش وارد شده‌ام

این حالت یعنی ورود انجام شده و فقط پاسخ نهایی خطا داده است. علت رایج: نبودن ستون `remember_token` در جدول `users` (ورود با `remember: true` توکن را ذخیره می‌کند و SQL خطا می‌دهد، در حالی که نشست قبلاً ثبت شده).

```bash
php artisan migrate            # ستون remember_token اضافه می‌شود
php artisan app:check          # بررسی ستون‌های users (remember_token هم چک می‌شود)
tail -n 30 storage/logs/laravel.log | grep -i remember
```

از این نسخه، اگر ستون ساخته نشده باشد ورود بدون «مرا به خاطر بسپار» انجام می‌شود و دیگر ۵۰۰ نمی‌دهد.

## کد تأیید کار نمی‌کند / «تأیید کد انجام نشد»

```bash
php artisan otp:status 09121234567                 # وضعیت کد: ثانیهٔ باقی‌مانده و تعداد تلاش
php artisan otp:status 09121234567 --issue         # ساخت کد جدید + ارسال پیامک (کد چاپ می‌شود)
php artisan otp:status 09121234567 --issue --no-sms # فقط ساخت کد، بدون پیامک
php artisan otp:status 09121234567 --check=48213    # بررسی همان کدی که در فرم می‌زنید
php artisan otp:status 09121234567 --clear          # پاک کردن کد و بازنشانی محدودیت ارسال
```

**روش سریع عیب‌یابی (بدون مرورگر):**

```bash
php artisan otp:status 09121234567 --issue --no-sms   # ۱) کد ساخته و چاپ می‌شود
php artisan otp:status 09121234567 --check=کدِچاپ‌شده # ۲) باید «معتبر ✅» بدهد
grep '\[OTP\]' storage/logs/laravel.log | tail -5    # ۳) دلیل دقیق هر تلاش
```

اگر مرحلهٔ ۲ «معتبر» داد ولی مرورگر خطا نشان می‌دهد، مشکل سمت نشست/ویو است:
`php artisan view:clear` و بررسی جدول `sessions`.

**هر تغییر در `.env` یا کد، نیاز به این دارد:** `php artisan optimize:clear` (کش ویو و کانفیگ). و اگر `APP_KEY` را عوض کرده‌اید، کدهای قبلی بی‌اعتبار می‌شوند → کد جدید بگیرید.

دلایل رایج و تنظیمات مربوطه:

| علت | تنظیم |
|---|---|
| کد منقضی شده | `OTP_TTL_SECONDS` (پیش‌فرض ۱۸۰ ثانیه) |
| بیش از حد تلاش اشتباه | `OTP_MAX_ATTEMPTS` (پیش‌فرض ۵) → کد جدید بگیرید |
| درخواست ارسال مجدد زودتر از موعد | `OTP_RESEND_SECONDS` (پیش‌فرض ۵۵) |
| پیامک اصلاً نرسیده | `php artisan sms:test 09121234567` |
| خطای ۴۱۹ (صفحه رفرش) | `SESSION_DRIVER` و وجود جدول `sessions` (`php artisan migrate`) |

از این نسخه، پیام خطا دقیق است (منقضی / اشتباه / تعداد تلاش زیاد) و کد با ارقام فارسی یا فاصله («۱۲ ۳۴۵») هم پذیرفته می‌شود.

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
