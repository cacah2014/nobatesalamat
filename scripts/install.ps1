# نصب و راه‌اندازی پروژه در یک مرحله (ویندوز / PowerShell)
#
#   powershell -ExecutionPolicy Bypass -File scripts\install.ps1
#   powershell -ExecutionPolicy Bypass -File scripts\install.ps1 -Sqlite -Seed
#
# -Sqlite : دیتابیس SQLite برای اجرای سریع بدون نصب MySQL
# -Seed   : درج داده‌های اولیه (تخصص‌ها، پزشکان، تنظیمات، مدیر ارشد)
# -SkipComposer : اگر vendor از قبل نصب شده است

param(
    [string]$Php = "php",
    [string]$Composer = "composer",
    [switch]$Sqlite,
    [switch]$Seed,
    [switch]$SkipComposer
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

function Say($message) { Write-Host "▸ $message" -ForegroundColor Cyan }

if (-not (Get-Command $Php -ErrorAction SilentlyContinue) -and -not (Test-Path $Php)) {
    Write-Host "PHP پیدا نشد. PHP 8.2+ را نصب کنید یا مسیر آن را با -Php بدهید." -ForegroundColor Red
    exit 1
}
Say "PHP: $(& $Php -r 'echo PHP_VERSION;')"

if (-not $SkipComposer) {
    if (-not (Get-Command $Composer -ErrorAction SilentlyContinue)) {
        Write-Host "Composer پیدا نشد: https://getcomposer.org/download/" -ForegroundColor Red
        exit 1
    }
    Say "نصب وابستگی‌ها (composer install)"
    & $Composer install --no-interaction --prefer-dist
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

if (-not (Test-Path ".env")) {
    Say "ساخت فایل .env از .env.example"
    Copy-Item ".env.example" ".env"
}

function Set-EnvVar([string]$Key, [string]$Value) {
    $content = Get-Content ".env" -Raw
    if ($content -match "(?m)^$([regex]::Escape($Key))=") {
        $content = [regex]::Replace($content, "(?m)^$([regex]::Escape($Key))=.*$", { param($m) "$Key=$Value" })
    } else {
        $content = $content.TrimEnd() + "`r`n$Key=$Value`r`n"
    }
    Set-Content ".env" $content -NoNewline
}

if ($Sqlite) {
    Say "تنظیم دیتابیس SQLite"
    $dbPath = Join-Path $Root "database\database.sqlite"
    if (-not (Test-Path (Split-Path $dbPath -Parent))) { New-Item -ItemType Directory (Split-Path $dbPath -Parent) | Out-Null }
    if (-not (Test-Path $dbPath)) { New-Item -ItemType File $dbPath | Out-Null }
    Set-EnvVar "DB_CONNECTION" "sqlite"
    Set-EnvVar "DB_DATABASE" ($dbPath -replace '\\', '/')
}

$envContent = Get-Content ".env" -Raw
if ($envContent -notmatch "(?m)^APP_KEY=base64:") {
    Say "ساخت کلید برنامه (php artisan key:generate)"
    & $Php artisan key:generate --force
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

if ($Sqlite) {
    Say "اجرای مایگریشن‌ها"
    & $Php artisan migrate --force
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    if ($Seed) {
        Say "درج داده‌های اولیه"
        & $Php artisan db:seed --force
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    }
} else {
    Write-Host ""
    Write-Host "ℹ دیتابیس MySQL: مقادیر DB_* را در .env تنظیم کنید و سپس:" -ForegroundColor Yellow
    Write-Host "   $Php artisan migrate --seed"
}

try { & $Php artisan storage:link | Out-Null } catch { }

Write-Host ""
Say "تمام شد. اجرای پروژه:"
Write-Host "   $Php -S 127.0.0.1:8000 -t public server.php     # یا: $Php artisan serve"
