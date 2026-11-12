<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * ارتقای خودکار «مدیر اصلی» به مدیر ارشد.
 *
 * شمارهٔ تنظیم‌شده در ADMIN_PHONE / OWNER_PHONE همیشه مدیر ارشد است. اگر حساب
 * آن شماره قبلاً به‌عنوان «کاربر عادی» ساخته شده باشد (یا نقشش دستی عوض شده
 * باشد)، با اولین درخواست بعد از به‌روزرسانی اصلاح می‌شود — حتی اگر کاربر با
 * نشست قبلی وارد شده باشد و دوباره کد پیامکی نزند.
 */
class PromoteMainAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && is_admin_phone($user->phone) && ! $user->isSuperAdmin()) {
            $user->forceFill([
                'role' => User::ROLE_SUPER,
                'status' => 'active',
                'full_name' => $user->full_name ?: 'مدیر ارشد نوبت سلامت',
            ])->save();

            Log::info('[ADMIN] ارتقای خودکار مدیر اصلی به مدیر ارشد', ['phone' => mask_phone($user->phone)]);
        }

        return $next($request);
    }
}
