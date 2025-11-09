<?php

namespace App\Providers;

use App\Services\HookManager;
use App\Services\ModuleManager;
use App\Services\WidgetManager;
use App\Facades\Hook;
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
        $this->registerCoreWidgetAreas();

        /** @var WidgetManager $widgetManager */
        $widgetManager = app('widget');

        // Allow modules/themes to register their widgets & areas
        Hook::doAction('widgets.register_types', $widgetManager);
        Hook::doAction('widgets.register_areas', $widgetManager);

        // Ensure registered areas exist in DB
        $widgetManager->syncRegisteredAreas();

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

        // Blog Categories Widget
        $widgetManager->register('blog_categories', \App\Widgets\BlogCategoriesWidget::class, [
            'label' => 'Blog Categories',
            'description' => 'Display a list of blog categories',
            'category' => 'blog',
            'config_schema' => [
                'display_count' => [
                    'type' => 'boolean',
                    'label' => 'Show post counts',
                    'default' => false,
                ],
                'hierarchical' => [
                    'type' => 'boolean',
                    'label' => 'Show hierarchy',
                    'default' => true,
                ],
                'hide_empty' => [
                    'type' => 'boolean',
                    'label' => 'Hide empty categories',
                    'default' => true,
                ],
            ],
        ]);

        // Blog Search Widget
        $widgetManager->register('blog_search', \App\Widgets\BlogSearchWidget::class, [
            'label' => 'Blog Search',
            'description' => 'Display blog search form',
            'category' => 'blog',
            'config_schema' => [
                'placeholder' => [
                    'type' => 'text',
                    'label' => 'Placeholder text',
                    'default' => 'Search blog...',
                ],
            ],
        ]);

        // Blog Posts Widget
        $widgetManager->register('blog_posts', \App\Widgets\BlogPostsWidget::class, [
            'label' => 'Blog Posts',
            'description' => 'Display posts with advanced filters',
            'category' => 'blog',
            'config_schema' => [
                'limit' => [
                    'type' => 'number',
                    'label' => 'Number of posts',
                    'default' => 5,
                    'min' => 1,
                    'max' => 20,
                ],
                'order_by' => [
                    'type' => 'select',
                    'label' => 'Order By',
                    'default' => 'published_at',
                    'options' => [
                        ['value' => 'published_at', 'label' => 'Publish Date'],
                        ['value' => 'title', 'label' => 'Title'],
                    ],
                ],
                'order_direction' => [
                    'type' => 'select',
                    'label' => 'Order Direction',
                    'default' => 'desc',
                    'options' => [
                        ['value' => 'desc', 'label' => 'Descending'],
                        ['value' => 'asc', 'label' => 'Ascending'],
                    ],
                ],
                'category_ids' => [
                    'type' => 'tags',
                    'label' => 'Filter by Categories (IDs)',
                    'default' => [],
                ],
            ],
        ]);

        // Blog Tags Widget
        $widgetManager->register('blog_tags', \App\Widgets\BlogTagsWidget::class, [
            'label' => 'Blog Tags',
            'description' => 'Display a tag cloud for blog posts',
            'category' => 'blog',
            'config_schema' => [
                'limit' => [
                    'type' => 'number',
                    'label' => 'Number of tags',
                    'default' => 20,
                    'min' => 1,
                    'max' => 50,
                ],
                'order_by' => [
                    'type' => 'select',
                    'label' => 'Order by',
                    'default' => 'count',
                    'options' => [
                        ['value' => 'name', 'label' => 'Name'],
                        ['value' => 'count', 'label' => 'Usage Count'],
                    ],
                ],
                'order_direction' => [
                    'type' => 'select',
                    'label' => 'Order Direction',
                    'default' => 'desc',
                    'options' => [
                        ['value' => 'desc', 'label' => 'Descending'],
                        ['value' => 'asc', 'label' => 'Ascending'],
                    ],
                ],
            ],
        ]);

        // Product Categories Widget
        $widgetManager->register('product_categories', \App\Widgets\ProductCategoriesWidget::class, [
            'label' => 'Product Categories',
            'description' => 'Display product categories list',
            'category' => 'commerce',
            'config_schema' => [
                'display_count' => [
                    'type' => 'boolean',
                    'label' => 'Show product counts',
                    'default' => false,
                ],
                'hide_empty' => [
                    'type' => 'boolean',
                    'label' => 'Hide empty categories',
                    'default' => true,
                ],
            ],
        ]);

        // Products Widget
        $widgetManager->register('products', \App\Widgets\ProductsWidget::class, [
            'label' => 'Products',
            'description' => 'Display products with filtering options',
            'category' => 'commerce',
            'config_schema' => [
                'limit' => [
                    'type' => 'number',
                    'label' => 'Number of products',
                    'default' => 4,
                    'min' => 1,
                    'max' => 20,
                ],
                'order_by' => [
                    'type' => 'select',
                    'label' => 'Order By',
                    'default' => 'created_at',
                    'options' => [
                        ['value' => 'created_at', 'label' => 'Newest'],
                        ['value' => 'price', 'label' => 'Price'],
                        ['value' => 'name', 'label' => 'Name'],
                    ],
                ],
                'order_direction' => [
                    'type' => 'select',
                    'label' => 'Order Direction',
                    'default' => 'desc',
                    'options' => [
                        ['value' => 'desc', 'label' => 'Descending'],
                        ['value' => 'asc', 'label' => 'Ascending'],
                    ],
                ],
                'category_ids' => [
                    'type' => 'tags',
                    'label' => 'Filter by Categories (IDs)',
                    'default' => [],
                ],
            ],
        ]);

        // Language Switcher Widget
        $widgetManager->register('language_switcher', \App\Widgets\LanguageSwitcherWidget::class, [
            'label' => 'Language Switcher',
            'description' => 'Display a list of available languages',
            'category' => 'general',
            'config_schema' => [
                'display_style' => [
                    'type' => 'select',
                    'label' => 'Display style',
                    'default' => 'list',
                    'options' => [
                        ['value' => 'list', 'label' => 'List'],
                        ['value' => 'dropdown', 'label' => 'Dropdown'],
                    ],
                ],
                'show_flags' => [
                    'type' => 'boolean',
                    'label' => 'Show flags (if available)',
                    'default' => false,
                ],
            ],
        ]);
    }

    /**
     * Register core widget areas
     */
    protected function registerCoreWidgetAreas(): void
    {
        /** @var WidgetManager $widgetManager */
        $widgetManager = app('widget');

        $widgetManager->registerArea('sidebar_primary', [
            'name' => 'Primary Sidebar',
            'description' => 'Main sidebar for blog pages.',
            'order' => 10,
            'locked' => true,
        ]);

        $widgetManager->registerArea('sidebar_blog', [
            'name' => 'Blog Sidebar',
            'description' => 'Sidebar shown on blog posts and archives.',
            'order' => 20,
            'locked' => true,
        ]);

        $widgetManager->registerArea('sidebar_shop', [
            'name' => 'Shop Sidebar',
            'description' => 'Sidebar for product listing pages.',
            'order' => 30,
            'locked' => true,
        ]);

        $widgetManager->registerArea('footer_col_1', [
            'name' => 'Footer Column 1',
            'description' => 'First footer widget area.',
            'order' => 40,
            'locked' => true,
        ]);

        $widgetManager->registerArea('footer_col_2', [
            'name' => 'Footer Column 2',
            'description' => 'Second footer widget area.',
            'order' => 50,
            'locked' => true,
        ]);

        $widgetManager->registerArea('footer_col_3', [
            'name' => 'Footer Column 3',
            'description' => 'Third footer widget area.',
            'order' => 60,
            'locked' => true,
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
