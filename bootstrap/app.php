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

        // Atau dapat juga menggunakan cara ini:
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
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
