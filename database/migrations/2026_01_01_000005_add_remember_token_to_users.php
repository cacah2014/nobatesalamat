<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ستون remember_token برای «مرا به خاطر بسپار».
 *
 * جدول users در این پروژه با ورود پیامکی ساخته شد و این ستون را نداشت؛
 * نتیجه این بود که Auth::login($user, remember: true) هنگام ذخیرهٔ توکن
 * خطای SQL می‌داد → پاسخ ۵۰۰ («Server error») در حالی که نشست کاربر
 * از قبل ثبت شده بود و با رفرش وارد می‌شد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->rememberToken();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
