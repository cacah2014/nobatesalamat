<?php

use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\CheckPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminAccess::class,
            'permission' => CheckPermission::class,
        ]);
        $middleware->trustProxies(at: '*');

        // وب‌هوک به‌روزرسانی از EnvKit توکن‌محور است، پس از CSRF مستثنی می‌شود
        $middleware->validateCsrfTokens(except: ['deploy', 'deploy/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
