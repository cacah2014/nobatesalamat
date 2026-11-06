<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key', 80)->primary();
            $table->text('value');
            $table->string('label', 120);
            $table->string('group', 40)->default('general')->index();
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 180)->unique();
            $table->text('excerpt');
            $table->text('image_url');
            $table->unsignedTinyInteger('read_time')->default(5);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('update_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 24)->default('running'); // running | success | failed
            $table->string('current_step', 40)->default('git_pull');
            $table->longText('output')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_logs');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('site_settings');
    }
};
