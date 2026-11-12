<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * تعیین مدیر اصلی سایت (super_admin) با شمارهٔ موبایل.
 *
 *   php artisan admin:grant 09364528962
 *   php artisan admin:grant 09364528962 --name="دکتر ..."
 *   php artisan admin:grant 09121234567 --role=admin
 *   php artisan admin:grant --list
 *   php artisan admin:grant 09121234567 --revoke
 */
class AdminGrantCommand extends Command
{
    protected $signature = 'admin:grant
        {phone? : شمارهٔ موبایل (۰۹xxxxxxxxx)}
        {--name= : نام نمایشی مدیر}
        {--role=super_admin : نقش؛ super_admin یا admin}
        {--revoke : حذف دسترسی مدیریتی این شماره}
        {--force : حذف دسترسی حتی وقتی تنها مدیر ارشد است}
        {--list : نمایش فهرست مدیران}';

    protected $description = 'تعیین یا حذف «مدیر اصلی» (super_admin) با شمارهٔ موبایل';

    public function handle(): int
    {
        $phone = $this->argument('phone');

        if ($this->option('list') || ! $phone) {
            return $this->listAdmins();
        }

        $phone = normalize_phone((string) $phone);

        if (! preg_match('/^09\d{9}$/', $phone)) {
            $this->error('شمارهٔ موبایل معتبر نیست. نمونه: 09364528962');

            return self::FAILURE;
        }

        return $this->option('revoke')
            ? $this->revoke($phone)
            : $this->grant($phone);
    }

    private function grant(string $phone): int
    {
        $role = (string) $this->option('role');

        if (! in_array($role, [User::ROLE_ADMIN, User::ROLE_SUPER], true)) {
            $this->error('نقش باید super_admin یا admin باشد.');

            return self::FAILURE;
        }

        $name = trim((string) $this->option('name'));

        $user = User::firstOrNew(['phone' => $phone]);
        $wasAdmin = $user->exists && $user->isAdmin();

        $user->fill([
            'full_name' => $name !== '' ? $name : ($user->full_name ?: 'مدیر ارشد نوبت سلامت'),
            'role' => $role,
            'status' => 'active',
            'permissions' => $user->permissions ?? [],
        ])->save();

        $label = $role === User::ROLE_SUPER ? 'مدیر ارشد' : 'مدیر';

        $this->info(($wasAdmin ? 'به‌روزرسانی شد' : 'ثبت شد') . ': ' . fa_digits($phone) . ' با نقش «' . $label . '»');

        if (! is_admin_phone($phone)) {
            $this->newLine();
            $this->warn('این شماره در ADMIN_PHONE تنظیم نشده است؛ تا وقتی آنجا نباشد، «مدیر اصلی» محسوب نمی‌شود.');
            $this->line('برای مدیر اصلی شدن، در .env بنویسید:');
            $this->line('  ADMIN_PHONE=' . $phone);
            $this->line('سپس: php artisan config:clear');
        }

        $this->newLine();
        $this->line('ورود: صفحهٔ /login → شمارهٔ موبایل → کد پیامکی → پنل مدیریت.');

        return self::SUCCESS;
    }

    private function revoke(string $phone): int
    {
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            $this->warn('کاربری با شمارهٔ ' . fa_digits($phone) . ' وجود ندارد.');

            return self::SUCCESS;
        }

        if (! $user->isAdmin()) {
            $this->warn('این شماره از قبل مدیر نیست.');

            return self::SUCCESS;
        }

        if ($user->isSuperAdmin() && ! $this->option('force')) {
            $supers = User::where('role', User::ROLE_SUPER)->count();

            if ($supers <= 1) {
                $this->error('این تنها مدیر ارشد سایت است؛ حذف او شما را از پنل بیرون می‌اندازد.');
                $this->line('اول مدیر ارشد دیگری بسازید یا از --force استفاده کنید.');

                return self::FAILURE;
            }
        }

        $user->forceFill(['role' => User::ROLE_USER])->save();

        $this->info('دسترسی مدیریتی ' . fa_digits($phone) . ' حذف شد؛ حساب به کاربر عادی تبدیل شد.');

        return self::SUCCESS;
    }

    private function listAdmins(): int
    {
        $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER])->orderBy('id')->get();

        if ($admins->isEmpty()) {
            $this->warn('هیچ مدیری ثبت نشده است.');
        } else {
            $this->table(
                ['شماره', 'نام', 'نقش', 'وضعیت', 'مدیر اصلی؟'],
                $admins->map(fn (User $user) => [
                    fa_digits($user->phone),
                    (string) ($user->full_name ?: '—'),
                    $user->role === User::ROLE_SUPER ? 'مدیر ارشد' : 'مدیر',
                    $user->isActive() ? 'فعال' : 'غیرفعال',
                    is_admin_phone($user->phone) ? '✅' : '—',
                ])->all()
            );
        }

        $configured = admin_phones();

        $this->newLine();
        $this->line('ADMIN_PHONE: ' . ($configured === [] ? '⚠ تنظیم نشده' : fa_digits(implode(' , ', $configured))));
        $this->line('شمارهٔ مدیر اصلی با اولین ورود به‌طور خودکار به مدیر ارشد ارتقا می‌یابد.');

        return self::SUCCESS;
    }
}
