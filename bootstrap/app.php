<?php

use App\Http\Middleware\CheckSubscriptionLimits;
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
        $middleware->alias([
            'subscription.limits' => CheckSubscriptionLimits::class,
            'auth.web'            => \App\Http\Middleware\WebAuthMiddleware::class,
        ]);

        // Don't let browsers cache pages: keeps stale JS/HTML (e.g. old
        // API URLs) from breaking uploads after a deploy.
        $middleware->append(\App\Http\Middleware\NoStoreCacheHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
