<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserStatusCommand extends Command
{
    protected $signature = 'user:status
        {phone? : شمارهٔ موبایل کاربر، مثال 09121234567}
        {status? : وضعیت جدید: active یا blocked}
        {--admins : نمایش فهرست مدیران و وضعیتشان}';

    protected $description = 'نمایش یا تغییر وضعیت فعال/غیرفعال حساب کاربران (رفع پیام «حساب شما غیرفعال است»)';

    public function handle(): int
    {
        if ($this->option('admins')) {
            $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER])
                ->orderBy('role')
                ->get(['phone', 'full_name', 'role', 'status']);

            if ($admins->isEmpty()) {
                $this->warn('هیچ مدیری ثبت نشده است. یک بار php artisan db:seed --force را اجرا کنید.');

                return self::SUCCESS;
            }

            $this->table(
                ['phone', 'full_name', 'role', 'status', 'فعال؟'],
                $admins->map(fn (User $user) => [
                    $user->phone,
                    $user->full_name,
                    $user->role,
                    (string) ($user->status ?? '—'),
                    $user->isActive() ? '✅' : '❌',
                ])->all()
            );

            return self::SUCCESS;
        }

        $phone = $this->argument('phone');

        if (! $phone) {
            $this->error('شمارهٔ موبایل را بدهید: php artisan user:status 09121234567');
            $this->line('یا برای دیدن مدیران: php artisan user:status --admins');

            return 2;
        }

        $normalized = normalize_phone((string) $phone);
        $user = User::where('phone', $normalized)->first();

        if (! $user) {
            $this->error('کاربری با شمارهٔ ' . $normalized . ' پیدا نشد. (کاربر با اولین ورود ساخته می‌شود.)');

            return self::FAILURE;
        }

        $newStatus = $this->argument('status');

        if (! $newStatus) {
            $this->line('شماره   : ' . $user->phone);
            $this->line('نام      : ' . $user->full_name);
            $this->line('نقش      : ' . $user->role);
            $this->line('status   : ' . (string) ($user->status ?? 'NULL') . '  →  ' . ($user->isActive() ? 'فعال ✅' : 'غیرفعال ❌'));
            $this->line('دسترسی‌ها: ' . implode(', ', $user->permissions ?? []) ?: '—');
            $this->newLine();
            $this->line('برای تغییر: php artisan user:status ' . $normalized . ' active');
            $this->line('یا:          php artisan user:status ' . $normalized . ' blocked');

            return self::SUCCESS;
        }

        $newStatus = strtolower(trim((string) $newStatus));

        if (! in_array($newStatus, ['active', 'blocked', 'inactive', 'disabled'], true)) {
            $this->error('وضعیت نامعتبر است. مقدارهای مجاز: active یا blocked');

            return 2;
        }

        $user->forceFill(['status' => $newStatus])->save();

        $this->info('✅ وضعیت ' . $user->phone . ' به «' . $newStatus . '» تغییر کرد.');
        $this->line('فعال؟ ' . ($user->fresh()->isActive() ? 'بله' : 'خیر'));

        return self::SUCCESS;
    }
}
