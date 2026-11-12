<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * زیرساخت ویرایش کامل محتوا از پنل:
 *
 * - ستون body برای متن کامل مقالات
 * - ستون type برای تنظیمات سایت (text | textarea | image)
 * - جدول faqs برای سؤالات متداول قابل ویرایش
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articles') && ! Schema::hasColumn('articles', 'body')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->longText('body')->nullable()->after('excerpt');
            });
        }

        if (Schema::hasTable('site_settings') && ! Schema::hasColumn('site_settings', 'type')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->string('type', 16)->default('text')->after('label');
            });
        }

        if (! Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->string('question', 255);
                $table->text('answer');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');

        if (Schema::hasTable('site_settings') && Schema::hasColumn('site_settings', 'type')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'body')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn('body');
            });
        }
    }
};
