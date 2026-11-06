<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * بررسی دسترسی بخشی برای مدیران.
 * استفاده: ->middleware('permission:content') , ('permission:doctors') ...
 * مدیر ارشد همیشه عبور می‌کند.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'دسترسی به این بخش برای شما فعال نیست.'], 403);
            }

            abort(403, 'دسترسی به این بخش برای شما فعال نیست.');
        }

        return $next($request);
    }
}
