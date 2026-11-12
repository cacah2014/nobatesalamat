<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\Auth\PhoneAuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\User\DashboardController;
use Illuminate\Support\Facades\Route;

// ---------- صفحات عمومی ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.show');

// ---------- وضعیت سرویس (بررسی از راه دور) ----------
Route::get('/health', HealthController::class)->name('health');

// ---------- سئو ----------
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');

// ---------- ورود موبایلی ----------
Route::get('/login', [PhoneAuthController::class, 'showLogin'])->name('login')->middleware('guest');

// این دو مسیر JSON هستند و نباید در گروه guest باشند؛ وگرنه اگر نشستی از قبل
// وجود داشته باشد پاسخ 302 می‌آید و «تأیید کد انجام نشد» نمایش داده می‌شود.
Route::post('/auth/otp', [PhoneAuthController::class, 'sendOtp'])->name('auth.otp')->middleware('throttle:20,1');
Route::post('/auth/verify', [PhoneAuthController::class, 'verify'])->name('auth.verify')->middleware('throttle:20,1');
Route::post('/logout', [PhoneAuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---------- کاربر واردشده ----------
Route::middleware('auth')->group(function () {
    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');
    Route::patch('/panel/profile', [DashboardController::class, 'updateProfile'])->name('panel.profile');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
});

// ---------- پنل مدیریت ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // محتوای سایت ← دسترسی content
    Route::middleware('permission:content')->group(function () {
        Route::get('/content', [SiteSettingController::class, 'index'])->name('content');
        Route::patch('/content', [SiteSettingController::class, 'update'])->name('content.update');
    });

    // پزشکان ← دسترسی doctors
    Route::middleware('permission:doctors')->group(function () {
        Route::get('/doctors', [AdminDoctorController::class, 'index'])->name('doctors');
        Route::post('/doctors', [AdminDoctorController::class, 'store'])->name('doctors.store');
        Route::patch('/doctors/{doctor}', [AdminDoctorController::class, 'update'])->name('doctors.update');
        Route::delete('/doctors/{doctor}', [AdminDoctorController::class, 'destroy'])->name('doctors.destroy');
    });

    // نوبت‌ها ← دسترسی appointments
    Route::middleware('permission:appointments')->group(function () {
        Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments');
        Route::patch('/appointments/{appointment}', [AdminAppointmentController::class, 'update'])->name('appointments.update');
    });

    // مدیران و دسترسی‌ها ← فقط مدیر ارشد (بررسی در خود کنترلر)
    Route::get('/admins', [AdminUserController::class, 'index'])->name('admins');
    Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
    Route::patch('/admins/{user}/permissions', [AdminUserController::class, 'updatePermissions'])->name('admins.permissions');
    Route::delete('/admins/{user}', [AdminUserController::class, 'destroy'])->name('admins.destroy');

    // به‌روزرسانی پروژه ← دسترسی updates
    Route::middleware('permission:updates')->group(function () {
        Route::get('/update', [UpdateController::class, 'index'])->name('update');
        Route::post('/update', [UpdateController::class, 'run'])->name('update.run');
    });
});

// ---------- به‌روزرسانی خودکار سایت (EnvKit / Cron / CI) ----------
Route::get('/deploy/{token?}', [DeployController::class, 'status'])->middleware('throttle:30,1')->name('deploy.status');
Route::post('/deploy/{token?}', [DeployController::class, 'deploy'])->middleware('throttle:10,1')->name('deploy.run');
