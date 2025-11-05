<?php

namespace App\Providers;

use App\Services\HookManager;
use App\Services\ModuleManager;
use App\Services\WidgetManager;
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

        // Register Widget Manager as singleton
        $this->app->singleton('widget', function ($app) {
            return new WidgetManager();
        });

        // Register alias for Widget facade
        $this->app->alias('widget', WidgetManager::class);

        // Register Menu Registry as singleton
        $this->app->singleton(\App\Services\MenuRegistry::class, function ($app) {
            return new \App\Services\MenuRegistry();
        });

        // Register Core Menu Service
        $this->app->singleton(\App\Services\CoreMenuService::class);
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

        // Register core widgets
        $this->registerCoreWidgets();
    }

    /**
     * Register core widget types
     */
    protected function registerCoreWidgets(): void
    {
        $widgetManager = app('widget');

        // Recent Posts Widget
        $widgetManager->register('recent_posts', \App\Widgets\RecentPostsWidget::class, [
            'label' => 'Recent Posts',
            'description' => 'Display a list of recent posts',
            'category' => 'content',
            'config_schema' => [
                'limit' => [
                    'type' => 'number',
                    'label' => 'Number of posts',
                    'default' => 5,
                    'min' => 1,
                    'max' => 20,
                ],
            ],
        ]);

        // HTML Block Widget
        $widgetManager->register('html_block', \App\Widgets\HtmlBlockWidget::class, [
            'label' => 'HTML Block',
            'description' => 'Add custom HTML content',
            'category' => 'content',
            'config_schema' => [
                'content' => [
                    'type' => 'textarea',
                    'label' => 'HTML Content',
                    'default' => '',
                    'rows' => 10,
                ],
            ],
        ]);
    }
}
