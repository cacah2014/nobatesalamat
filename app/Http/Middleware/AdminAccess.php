<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /** فقط کاربران با نقش admin یا super_admin */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // شمارهٔ نمونهٔ نصب (09120000000) هرگز مدیر محسوب نمی‌شود
        if (! $user || ! $user->isAdmin() || ! $user->isActive() || is_placeholder_phone($user->phone)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'دسترسی غیرمجاز'], 403);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
