<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Sanctum middleware untuk API
        $middleware->statefulApi();

        // Pastikan request frontend dianggap stateful
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'verified'    => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'org.access'  => \App\Http\Middleware\CheckOrganizationAccess::class,
            'org.owner'   => \App\Http\Middleware\CheckOrganizationOwner::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);

        // Tambahkan ini (sesuai contoh pertama)
        // Mengizinkan API dilewati dari CSRF protection
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
