#!/usr/bin/env bash
#
# نصب و راه‌اندازی پروژه در یک مرحله (لینوکس / مک / WSL)
#
#   bash scripts/install.sh              # نصب وابستگی‌ها + ساخت .env + کلید برنامه
#   bash scripts/install.sh --sqlite     # دیتابیس SQLite برای اجرای سریع/تست
#   bash scripts/install.sh --sqlite --seed   # + داده‌های اولیه (پزشکان، تخصص‌ها، مدیر)
#
# برای MySQL فقط کافی است قبلاً .env را تنظیم کنید و بعد:
#   bash scripts/install.sh --skip-composer && php artisan migrate --seed
#
set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
USE_SQLITE=0
SEED=0
SKIP_COMPOSER=0

for arg in "$@"; do
    case "$arg" in
        --sqlite) USE_SQLITE=1 ;;
        --seed) SEED=1 ;;
        --skip-composer) SKIP_COMPOSER=1 ;;
        -h|--help) sed -n '2,12p' "$0"; exit 0 ;;
        *) echo "گزینهٔ ناشناخته: $arg" >&2; exit 2 ;;
    esac
done

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

say() { printf '\033[1;36m▸ %s\033[0m\n' "$1"; }

set_env() {
    local key="$1" value="$2"
    if grep -q "^${key}=" .env; then
        awk -v k="$key" -v v="$value" '$1==k { print k"="v; next } { print }' .env > .env.tmp && mv .env.tmp .env
    else
        printf '%s=%s\n' "$key" "$value" >> .env
    fi
}

command -v "$PHP_BIN" >/dev/null 2>&1 || { echo "PHP پیدا نشد. PHP 8.2+ نصب کنید یا PHP_BIN را ست کنید." >&2; exit 1; }
say "PHP: $("$PHP_BIN" -r 'echo PHP_VERSION;')"

if [[ $SKIP_COMPOSER -eq 0 ]]; then
    command -v "$COMPOSER_BIN" >/dev/null 2>&1 || { echo "Composer پیدا نشد: https://getcomposer.org/download/" >&2; exit 1; }
    say "نصب وابستگی‌ها (composer install)"
    "$COMPOSER_BIN" install --no-interaction --prefer-dist
fi

if [[ ! -f .env ]]; then
    say "ساخت فایل .env از .env.example"
    cp .env.example .env
fi

if [[ $USE_SQLITE -eq 1 ]]; then
    say "تنظیم دیتابیس SQLite"
    mkdir -p database
    [[ -f database/database.sqlite ]] || : > database/database.sqlite
    set_env DB_CONNECTION sqlite
    set_env DB_DATABASE "$ROOT_DIR/database/database.sqlite"
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    say "ساخت کلید برنامه (php artisan key:generate)"
    "$PHP_BIN" artisan key:generate --force
fi

if [[ $USE_SQLITE -eq 1 ]]; then
    say "اجرای مایگریشن‌ها"
    "$PHP_BIN" artisan migrate --force
    if [[ $SEED -eq 1 ]]; then
        say "درج داده‌های اولیه"
        "$PHP_BIN" artisan db:seed --force
    fi
else
    echo
    echo "ℹ دیتابیس MySQL: مقادیر DB_* را در .env تنظیم کنید و سپس:"
    echo "   $PHP_BIN artisan migrate --seed"
fi

"$PHP_BIN" artisan storage:link >/dev/null 2>&1 || true

echo
say "تمام شد. اجرای پروژه:"
echo "   $PHP_BIN -S 127.0.0.1:8000 -t public server.php     # یا: $PHP_BIN artisan serve"
