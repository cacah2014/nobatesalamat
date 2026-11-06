<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 16)->unique();
            $table->string('full_name', 120)->default('کاربر نوبت سلامت');
            $table->string('role', 24)->default('user')->index(); // user | admin | super_admin
            $table->string('status', 20)->default('active')->index();
            $table->json('permissions')->nullable(); // ["content","doctors","appointments","updates"]
            $table->timestamps();
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 16)->index();
            $table->string('code_hash', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('slug', 120)->unique();
            $table->string('icon', 32)->default('stethoscope');
            $table->string('color', 32)->default('teal');
            $table->text('description');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('slug', 140)->unique();
            $table->foreignId('specialty_id')->constrained()->restrictOnDelete();
            $table->string('title', 160);
            $table->string('city', 80)->default('تهران')->index();
            $table->text('address');
            $table->double('rating')->default(5);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('visit_price')->default(0);
            $table->unsignedTinyInteger('experience_years')->default(1);
            $table->string('next_available', 80)->default('امروز');
            $table->text('image_url');
            $table->boolean('is_online')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->text('about');
            $table->string('medical_code', 30)->default('');
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->string('appointment_date', 30);
            $table->string('appointment_time', 10);
            $table->string('visit_type', 20)->default('in_person');
            $table->string('status', 24)->default('confirmed')->index();
            $table->unsignedInteger('price')->default(0);
            $table->string('tracking_code', 20)->unique();
            $table->string('patient_name', 120);
            $table->string('patient_phone', 16);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('specialties');
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('users');
    }
};
