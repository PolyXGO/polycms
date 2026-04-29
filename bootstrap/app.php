<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

// Bootstrap configuration removed in favor of public/index.php early hijack

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('ecommerce:check-expirations')->daily();
        $schedule->command('ecommerce:process-auto-renews')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            'install',
            'install/*',
        ]);

        $middleware->validateCsrfTokens(except: [
            'install/*',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\RedirectIfNotInstalled::class,
            \App\Http\Middleware\SetLanguageMiddleware::class,
        ]);

        // Add language middleware to api group as well so Sanctum users get correct language
        $middleware->api(append: [
            \App\Http\Middleware\SetLanguageMiddleware::class,
        ]);

        $middleware->redirectTo('/account/login');
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
