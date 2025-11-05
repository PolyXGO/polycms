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

        // Register Theme Manager as singleton
        $this->app->singleton(\App\Services\ThemeManager::class, function ($app) {
            return new \App\Services\ThemeManager();
        });

        // Register Topbar Menu Service as singleton
        $this->app->singleton(\App\Services\TopbarMenuService::class);

        // Register Settings Service as singleton
        $this->app->singleton(\App\Services\SettingsService::class);

        // Register Media Service as singleton
        $this->app->singleton('media.service', function ($app) {
            return new \App\Services\MediaService();
        });

        // Register alias for MediaService facade
        $this->app->alias('media.service', \App\Services\MediaService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Initialize Language Helper
        \App\Helpers\LanguageHelper::init(app(\App\Services\SettingsService::class));

        // Register module autoloaders first
        $moduleManager = app(ModuleManager::class);
        $moduleManager->registerAutoloaders();

        // Then register module service providers
        $moduleManager->registerModules();

        // Register core widgets
        $this->registerCoreWidgets();

        // Share settings with views
        $this->shareSettingsWithViews();
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

    /**
     * Share settings with views
     */
    protected function shareSettingsWithViews(): void
    {
        $settingsService = app(\App\Services\SettingsService::class);
        
        // Share common settings with all views
        view()->composer('*', function ($view) use ($settingsService) {
            $view->with([
                'site_title' => $settingsService->get('site_title', config('app.name', 'PolyCMS')),
                'tagline' => $settingsService->get('tagline', ''),
                'brand_logo' => $settingsService->get('brand_logo'),
                'brand_name' => $settingsService->get('brand_name', 'POLYCMS'),
                'site_language' => $settingsService->get('site_language', 'en'),
                'site_language_direction' => $settingsService->get('site_language_direction', 'ltr'),
            ]);
        });
    }
}
