<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تعیین «مدیر اصلی» سایت (شمارهٔ مالک) و پاک‌سازی مدیر پیش‌فرض.
 *
 * این مهاجرت هنگام هر به‌روزرسانی (SiteUpdater دستور
 * `php artisan migrate --force` را اجرا می‌کند) هم اجرا می‌شود؛ بنابراین
 * حتی اگر ADMIN_PHONE در .env قدیمی مانده باشد، یا حساب مالک قبلاً به‌عنوان
 * «کاربر عادی» ساخته شده باشد، در دیتابیس به super_admin ارتقا می‌یابد.
 */
return new class extends Migration
{
    /** شمارهٔ مالک سایت — همیشه مدیر ارشد، مستقل از تنظیمات .env */
    private const OWNER_PHONES = ['09364528962'];

    /** شمارهٔ نمونهٔ نصب که نباید مدیر بماند */
    private const PLACEHOLDER_PHONES = ['09120000000'];

    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        $extra = function_exists('admin_phones') ? admin_phones() : [];

        $phones = array_values(array_unique(array_filter(array_merge(
            self::OWNER_PHONES,
            is_array($extra) ? $extra : []
        ))));

        foreach ($phones as $phone) {
            $phone = normalize_phone((string) $phone);

            if (! preg_match('/^09\d{9}$/', $phone)) {
                continue;
            }

            $row = DB::table('users')->where('phone', $phone)->first();

            $values = [
                'role' => 'super_admin',
                'status' => 'active',
                'updated_at' => now(),
            ];

            if (! $row) {
                DB::table('users')->insert($values + [
                    'phone' => $phone,
                    'full_name' => 'مدیر ارشد نوبت سلامت',
                    'permissions' => json_encode([]),
                    'created_at' => now(),
                ]);

                continue;
            }

            if (trim((string) ($row->full_name ?? '')) === '') {
                $values['full_name'] = 'مدیر ارشد نوبت سلامت';
            }

            DB::table('users')->where('id', $row->id)->update($values);
        }

        // شمارهٔ نمونهٔ نصب (اگر کسی با آن وارد نشده) از حالت مدیریت خارج می‌شود
        DB::table('users')
            ->whereIn('phone', self::PLACEHOLDER_PHONES)
            ->whereNotIn('phone', $phones)
            ->whereIn('role', ['admin', 'super_admin'])
            ->update(['role' => 'user', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // نقش‌ها دادهٔ کاربری هستند و به عقب برنمی‌گردند.
    }
};
