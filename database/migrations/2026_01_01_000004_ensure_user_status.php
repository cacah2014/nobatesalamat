<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * اطمینان از وجود ستون status در جدول users و اصلاح ردیف‌های قدیمی.
 *
 * اگر جدول users پیش‌تر با ساختار دیگری ساخته شده باشد (مثلاً مهاجرت انجام
 * نشده و جدول از نسخهٔ قبلی پروژه مانده باشد) مقدار status برای ردیف‌های
 * موجود NULL می‌ماند و ورود با پیام «حساب شما غیرفعال است» رد می‌شد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->index();
            });
        }

        DB::table('users')
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '');
            })
            ->update(['status' => 'active']);
    }

    public function down(): void
    {
        // ستون status بخشی از ساختار اصلی است و حذف نمی‌شود.
    }
};
