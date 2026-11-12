<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SiteUpdater;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * وضعیت سریع سرویس — بدون اطلاعات حساس.
 *
 * برای بررسی از راه دور (نسخهٔ مستقرشده، نقش مدیر اصلی، تعداد رکوردها)
 * بدون نیاز به دسترسی به لاگ یا ترمینال سرور:
 *
 *   GET https://nobatesalamat.ir/health
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $counts = [];
        $owner = null;

        if (Schema::hasTable('users')) {
            $phone = admin_phones()[0] ?? null;

            if ($phone !== null) {
                $row = DB::table('users')->where('phone', $phone)->first();

                $owner = [
                    'phone' => mask_phone($phone),
                    'exists' => (bool) $row,
                    'role' => $row->role ?? null,
                    'active' => $row ? ! in_array(strtolower((string) $row->status), ['blocked', 'inactive', 'disabled', 'suspended'], true) : null,
                ];
            }

            $counts['users'] = DB::table('users')->count();
            $counts['admins'] = DB::table('users')->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER])->count();
        }

        foreach (['doctors', 'appointments', 'articles', 'site_settings'] as $table) {
            if (Schema::hasTable($table)) {
                $counts[$table] = DB::table($table)->count();
            }
        }

        return response()->json([
            'ok' => true,
            'name' => config('app.name'),
            'env' => config('app.env'),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'commit' => $this->commit(),
            'admin_phone_config' => array_map('mask_phone', admin_phones()),
            'owner' => $owner,
            'counts' => $counts,
            'time' => now()->toIso8601String(),
        ]);
    }

    private function commit(): ?string
    {
        try {
            return app(SiteUpdater::class)->currentCommit();
        } catch (\Throwable) {
            return null;
        }
    }
}
