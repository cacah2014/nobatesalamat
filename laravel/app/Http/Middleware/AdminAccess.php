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

        if (! $user || ! $user->isAdmin() || $user->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'دسترسی غیرمجاز'], 403);
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
