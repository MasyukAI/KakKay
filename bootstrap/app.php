<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load development routes in local environment
            if (app()->environment('local')) {
                Route::middleware('web')
                    ->group(base_path('routes/dev.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Exclude CHIP webhook routes from CSRF protection
        // External payment gateway webhooks cannot provide CSRF tokens
        $middleware->validateCsrfTokens(except: [
            'webhooks/chip',
            'webhooks/chip/*',
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
