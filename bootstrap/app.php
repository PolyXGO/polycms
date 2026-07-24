<?php

// Prevent permission conflicts when commands are run as root in CLI (e.g. during deploy/cron)
if (PHP_SAPI === 'cli' && function_exists('posix_getuid') && posix_getuid() === 0) {
    @umask(0000); // Root-created files will be world-writable (0777/0666) so the web server user can read/write them
} else {
    @umask(0002); // Standard group-writable (0775/0664) for normal runtime
}

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

        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\RedirectIfNotInstalled::class,
            \App\Http\Middleware\LanguageRoutingMiddleware::class,
            \App\Http\Middleware\SetLanguageMiddleware::class,
            \App\Http\Middleware\PageCacheMiddleware::class,
            \App\Http\Middleware\AddCacheDebugCommentMiddleware::class,
        ]);

        // Add language middleware to api group as well so Sanctum users get correct language
        $middleware->api(append: [
            \App\Http\Middleware\LanguageRoutingMiddleware::class,
            \App\Http\Middleware\SetLanguageMiddleware::class,
        ]);

        $middleware->replace(\Illuminate\Foundation\Http\Middleware\TrimStrings::class, \App\Http\Middleware\TrimStrings::class);

        $middleware->redirectTo('/account/login');
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
