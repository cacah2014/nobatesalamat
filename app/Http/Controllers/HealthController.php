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

        // وضعیت ستون‌های حیاتی (اگر مایگریشن‌ها اجرا نشده باشند، اینجا دیده می‌شود)
        $schema = [];

        if (Schema::hasTable('users')) {
            foreach (['remember_token', 'status', 'role', 'permissions', 'full_name'] as $column) {
                $schema[$column] = Schema::hasColumn('users', $column);
            }
        }

        $schema['update_logs_table'] = Schema::hasTable('update_logs');
        $schema['sessions_table'] = Schema::hasTable('sessions');

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
            'schema' => $schema,
            'last_error' => $this->lastError(),
            'time' => now()->toIso8601String(),
        ]);
    }

    /**
     * آخرین خطای ثبت‌شده در storage/logs/laravel.log (فقط نوع، محل و پیام کوتاه).
     * برای تشخیص از راه دور بدون دسترسی به فایل لاگ.
     *
     * @return array{at: string|null, class: string|null, message: string}|null
     */
    private function lastError(): ?array
    {
        $path = storage_path('logs/laravel.log');

        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        try {
            $handle = fopen($path, 'rb');

            if ($handle === false) {
                return null;
            }

            $size = (int) filesize($path);
            $read = min(max($size, 1), 200_000);
            fseek($handle, -$read, SEEK_END);
            $tail = (string) fread($handle, $read);
            fclose($handle);
        } catch (\Throwable) {
            return null;
        }

        $matched = null;

        foreach (preg_split('/\R/', $tail) ?: [] as $line) {
            if (str_contains($line, '.ERROR:')) {
                $matched = $line;
            }
        }

        if ($matched === null) {
            return null;
        }

        $at = preg_match('/^\[([^\]]+)\]/', $matched, $m) ? $m[1] : null;

        $class = preg_match('/\[object\] \(([\w\\\\]+)/', $matched, $m) ? class_basename($m[1]) : null;

        $body = preg_replace('/^\[[^\]]+\]\s+\S+\.ERROR:\s*/', '', $matched) ?? $matched;
        $parts = preg_split('/\s*\{"[a-zA-Z]/', $body) ?: [];
        $body = $parts[0] ?? $body;
        $body = trim(str_replace(base_path(), '', $body));

        return [
            'at' => $at,
            'class' => $class,
            'message' => \Illuminate\Support\Str::limit($body, 180),
        ];
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
