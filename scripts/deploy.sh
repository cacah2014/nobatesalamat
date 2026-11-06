#!/usr/bin/env bash
#
# به‌روزرسانی سایت از روی Git — مناسب اجرا از EnvKit (cron / agent / وب‌هوک SSH)
#
#   bash scripts/deploy.sh            # اجرای کامل (با --force)
#   bash scripts/deploy.sh --check    # فقط بررسی نسخهٔ جدید
#   bash scripts/deploy.sh --dry-run  # نمایش دستورها
#
# دستور Git از متغیرهای محیطی خوانده می‌شود:
#   UPDATE_GIT_COMMAND | UPDATE_GIT_REMOTE / UPDATE_GIT_BRANCH
#
set -euo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
PHP_BIN="${UPDATE_PHP_BINARY:-php}"
LOG_FILE="${APP_DIR}/storage/logs/deploy.log"

cd "$APP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

if [[ $# -eq 0 ]]; then
    set -- --force
fi

stamp() { date -u '+%Y-%m-%dT%H:%M:%SZ'; }

echo "==== deploy start $(stamp) | args: $* ====" >> "$LOG_FILE"

if "$PHP_BIN" artisan site:update "$@" >> "$LOG_FILE" 2>&1; then
    echo "==== deploy ok $(stamp) ====" >> "$LOG_FILE"
    tail -n 12 "$LOG_FILE"
    exit 0
fi

echo "==== deploy failed $(stamp) ====" >> "$LOG_FILE"
tail -n 40 "$LOG_FILE" >&2
exit 1
