<?php

namespace App\Providers;

use App\Services\HookManager;
use App\Services\ModuleManager;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Hook Manager as singleton
        $this->app->singleton('hook', function ($app) {
            return new HookManager();
        });

        // Register alias for Hook facade
        $this->app->alias('hook', HookManager::class);

        // Register Module Manager as singleton
        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register module autoloaders first
        $moduleManager = app(ModuleManager::class);
        $moduleManager->registerAutoloaders();

        // Then register module service providers
        $moduleManager->registerModules();
    }
}
