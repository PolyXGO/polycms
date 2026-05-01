<?php

namespace App\Providers;

use App\Contracts\SeoRenderContract;
use App\Services\HookManager;
use App\Services\LayoutAssetManager;
use App\Services\LayoutAssetPreviewService;
use App\Services\ModuleManager;
use App\Services\WidgetManager;
use App\Services\PermissionRegistry;
use App\Facades\Hook;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

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

        // Register permission registry
        $this->app->singleton(PermissionRegistry::class, function ($app) {
            return new PermissionRegistry();
        });

        // Register Theme Manager as singleton
        $this->app->singleton(\App\Services\ThemeManager::class, function ($app) {
            return new \App\Services\ThemeManager();
        });

        // Register Template Resolver as singleton (multi-theme support)
        $this->app->singleton(\App\Services\TemplateResolver::class, function ($app) {
            return new \App\Services\TemplateResolver($app->make(\App\Services\ThemeManager::class));
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

        // Register Email Manager as singleton
        $this->app->singleton(\App\Services\Ecommerce\EmailManager::class);

        // Register Email Template Manager as singleton
        $this->app->singleton(\App\Services\EmailTemplateManager::class);

        // Register Layout Asset Manager as singleton
        $this->app->singleton(LayoutAssetManager::class, function ($app) {
            return new LayoutAssetManager($app->make(\App\Services\ContentRenderer::class));
        });

        $this->app->singleton(LayoutAssetPreviewService::class, function ($app) {
            return new LayoutAssetPreviewService($app->make(LayoutAssetManager::class));
        });

        // Register Core Payment Gateways
        $this->app->bind('core:cod', \App\Services\Gateways\CodGateway::class);
        $this->app->bind('core:bank_transfer', \App\Services\Gateways\BankTransferGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS scheme for URL generation when configured
        if (env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Register module autoloaders - always needed for class resolution
        $moduleManager = app(ModuleManager::class);
        $moduleManager->registerAutoloaders();

        // Then register module service providers
        $moduleManager->registerModules();

        // ALWAYS SKIP database-dependent boot logic if the system is not yet installed.
        if (!file_exists(storage_path('installed.lock'))) {
            return;
        }


        if (app()->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            if (collect($argv)->contains(fn($arg) => str_contains($arg, 'migrate'))) {
                return;
            }
        }

        if (!Schema::hasTable('settings')) {
            return;
        }

        // Initialize Language Helper
        \App\Helpers\LanguageHelper::init(app(\App\Services\SettingsService::class));

        // Register core widgets
        $this->registerCoreWidgets();
        $this->registerCoreWidgetAreas();
        $this->registerCorePaymentGatewaySchemas();

        /** @var WidgetManager $widgetManager */
        $widgetManager = app('widget');

        // Allow modules/themes to register their widgets & areas
        Hook::doAction('widgets.register_types', $widgetManager);
        Hook::doAction('widgets.register_areas', $widgetManager);

        // Expensive DB sync operations are only needed when bootstrap metadata changes.
        $shouldSyncBootstrapData = $this->shouldSyncBootstrapData($moduleManager);

        // Ensure registered areas exist in DB
        if ($shouldSyncBootstrapData && Schema::hasTable('widget_areas')) {
            $widgetManager->syncRegisteredAreas();
        }

        if ($shouldSyncBootstrapData) {
            $permissionRegistry = app(PermissionRegistry::class);
            $this->registerCorePermissions($permissionRegistry);
            Hook::doAction('roles.register_permissions', $permissionRegistry);
            if (Schema::hasTable('permissions')) {
                $permissionRegistry->syncDatabase();
            }

            // Register core email templates
            $this->registerCoreEmailTemplates();

            // Allow modules to register their own templates
            Hook::doAction('register_email_templates', app(\App\Services\EmailTemplateManager::class));

            // Sync to database
            if (Schema::hasTable('email_templates')) {
                app(\App\Services\EmailTemplateManager::class)->syncDatabase();
            }

            // Register reusable layout parts/templates
            $this->registerCoreLayoutAssets();
            $layoutAssetManager = app(LayoutAssetManager::class);
            Hook::doAction('layout.register_assets', $layoutAssetManager);
            $layoutAssetManager->ensureStorageReady();
            $layoutAssetManager->syncDatabase();
            $this->markBootstrapDataSynced($moduleManager);
        }

        // Share settings with views
        $this->shareSettingsWithViews();

        // Configure Mail at runtime based on settings
        $this->configureMail();

        // Configure Filesystem default driver
        $this->configureFilesystem();

        // Register SEO meta tags in head
        Hook::addAction('cms_head', [$this, 'renderSeoMeta']);

        // Auto-update menu item URLs when permalink settings change
        Hook::addAction('settings.saved', function ($payload) {
            if (($payload['group'] ?? '') !== 'permalinks') {
                return;
            }
            $this->updateMenuItemUrlsAfterPermalinkChange();
        });
    }

    /**
     * When permalink settings change, update all custom menu items whose stored URLs
     * match known permalink-based patterns (archive pages, entity URLs, etc.)
     */
    protected function updateMenuItemUrlsAfterPermalinkChange(): void
    {
        $settingsService = app(\App\Services\SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
        $baseUrl = rtrim(url('/'), '/');

        // Build a map: route purpose => new absolute URL
        $archiveMap = [
            'posts_archive' => $baseUrl . '/' . trim($permalinks['posts']['archive'] ?? 'posts', '/'),
            'products_archive' => $baseUrl . '/' . trim($permalinks['products']['archive'] ?? 'products', '/'),
        ];

        // Known archive path patterns (old values could be anything, so we match by DB content)
        // We update ALL custom-type menu items that point to an internal URL matching
        // any known archive pattern (e.g., http://domain/posts, http://domain/blog, etc.)
        $menuItems = \App\Models\MenuItem::whereNotNull('url')
            ->where('url', '!=', '')
            ->where(function ($q) use ($baseUrl) {
                // Only update URLs that start with the app's own base URL
                $q->where('url', 'LIKE', $baseUrl . '/%')
                  ->orWhere('url', 'LIKE', '/%');
            })
            ->get();

        foreach ($menuItems as $item) {
            $oldUrl = $item->url;
            $path = parse_url($oldUrl, PHP_URL_PATH) ?? '';
            $path = '/' . trim($path, '/');

            // 1) If this is a linkable item, clear the stored URL so it resolves dynamically
            if ($item->linkable_type && $item->linkable_id) {
                $item->update(['url' => null]);
                continue;
            }

            // 2) For custom items, try to detect if they match a known archive pattern
            // Match single-segment paths that could be an archive base
            // e.g., /posts, /blog, /products, /shop
            if (preg_match('#^/([A-Za-z0-9\-_]+)$#', $path, $matches)) {
                $segment = $matches[1];

                // Check if this segment was a known old archive base by seeing if the item
                // title hints at what it is (e.g., "Blog", "Posts", "Products", "Shop")
                $titleLower = strtolower(trim($item->title));

                // Posts archive detection
                $postsKeywords = ['blog', 'posts', 'bài viết', 'tin tức', 'articles', 'news'];
                if (in_array($titleLower, $postsKeywords, true)) {
                    $newUrl = $archiveMap['posts_archive'];
                    if ($oldUrl !== $newUrl) {
                        $item->update(['url' => $newUrl]);
                    }
                    continue;
                }

                // Products archive detection
                $productsKeywords = ['products', 'shop', 'store', 'sản phẩm', 'cửa hàng'];
                if (in_array($titleLower, $productsKeywords, true)) {
                    $newUrl = $archiveMap['products_archive'];
                    if ($oldUrl !== $newUrl) {
                        $item->update(['url' => $newUrl]);
                    }
                    continue;
                }
            }
        }
    }

    /**
     * Render SEO meta tags based on settings
     */
    public function renderSeoMeta(): void
    {
        if (app()->bound(SeoRenderContract::class)) {
            $rendered = app(SeoRenderContract::class)->renderHead();
            if ($rendered !== '') {
                echo $rendered;
            }

            return;
        }

        $settings = app(\App\Services\SettingsService::class);
        $isVisible = (bool)$settings->get('reading_search_engine_noindex', true);

        if (!$isVisible) {
            echo '<meta name="robots" content="noindex, nofollow">' . PHP_EOL;
        }

        // Canonical URL — default = current URL (without query string)
        // Themes/modules can override via Hook::addFilter('seo.canonical_url', ..., priority)
        // Priority chain: Core(default) → Theme(10) → Module/MTOptimize(20+)
        $canonicalUrl = Hook::applyFilters('seo.canonical_url', request()->url());

        if ($canonicalUrl) {
            echo '<link rel="canonical" href="' . e($canonicalUrl) . '">' . PHP_EOL;
        }

        // Site Favicon
        $siteIconUrl = Hook::applyFilters('seo.site_favicon', $settings->get('site_icon'));
        if ($siteIconUrl) {
            echo '<link rel="icon" type="image/png" href="' . e($siteIconUrl) . '">' . PHP_EOL;
            echo '<link rel="apple-touch-icon" href="' . e($siteIconUrl) . '">' . PHP_EOL;
        }
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
                'site_language' => \App\Helpers\LanguageHelper::getCurrentLanguage(),
                'site_language_direction' => $settingsService->get('site_language_direction', 'ltr'),
            ]);
        });
    }

    protected function registerCorePermissions(\App\Services\PermissionRegistry $permissionRegistry): void
    {
        $definitions = config('permissions.definitions', []);

        foreach ($definitions as $definition) {
            $group = $definition['group'] ?? 'core';
            foreach ($definition['permissions'] ?? [] as $permission) {
                $name = is_array($permission) ? ($permission['name'] ?? null) : $permission;
                if (!$name) {
                    continue;
                }

                $permissionRegistry->register($name, [
                    'label' => $permission['label'] ?? ucfirst($name),
                    'group' => $group,
                    'guard_name' => $permission['guard_name'] ?? 'web',
                ]);
            }
        }
    }

    protected function registerCoreEmailTemplates(): void
    {
        /** @var \App\Services\EmailTemplateManager $templateManager */
        $templateManager = app(\App\Services\EmailTemplateManager::class);

        $templateManager->register('ACCOUNT_WELCOME', [
            'label' => 'Account Welcome',
            'group' => 'core',
            'default_subject' => 'Welcome to {site_name}',
            'default_body' => '<h1>Welcome, {user_name}!</h1><p>Thank you for joining {site_name}. We are glad to have you here.</p><p><a href="{site_url}">Click here to visit our site</a></p>',
            'variables' => ['user_name', 'site_name', 'site_url', 'account_login_url', 'account_orders_url', 'account_subscriptions_url', 'account_licenses_url', 'account_profile_url', 'account_dashboard_url'],
        ]);

        $templateManager->register('PASSWORD_RESET', [
            'label' => 'Password Reset',
            'group' => 'core',
            'default_subject' => 'Reset Your Password - {site_name}',
            'default_body' => '<h1>Hello, {user_name}</h1><p>You are receiving this email because we received a password reset request for your account.</p><p><a href="{reset_url}">Reset Password</a></p><p>If you did not request a password reset, no further action is required.</p>',
            'variables' => ['user_name', 'site_name', 'reset_url', 'account_login_url'],
        ]);

        $templateManager->register('ORDER_CONFIRMATION', [
            'label' => 'Order Confirmation',
            'group' => 'ecommerce',
            'default_subject' => 'Order Confirmation - #{order_code}',
            'default_body' => '<h1>Thank you for your order!</h1><p>Hello {user_name}, your order #{order_code} has been received and is being processed.</p><p>Total: {total_amount}</p>',
            'variables' => ['user_name', 'order_code', 'total_amount', 'site_name', 'account_orders_url'],
        ]);

        $templateManager->register('ORDER_SUCCESS', [
            'label' => 'Order Payment Successful',
            'group' => 'ecommerce',
            'default_subject' => 'Payment Received - Order #{order_code}',
            'default_body' => '<h1>Payment Successful!</h1><p>Hello {user_name}, we have successfully received payment for your order #{order_code}.</p><p>Your items are now ready.</p>',
            'variables' => ['user_name', 'order_code', 'site_name', 'account_orders_url'],
        ]);

        $templateManager->register('SUBSCRIPTION_RENEWAL_REMINDER', [
            'label' => 'Subscription Renewal Reminder',
            'group' => 'ecommerce',
            'default_subject' => 'Action Required: Your subscription for {product_name} expires soon',
            'default_body' => '<h1>Subscription Renewal</h1><p>Hello {user_name}, your subscription for {product_name} will expire in {days_remaining} days.</p><p><a href="{renewal_url}">Renew Now</a></p>',
            'variables' => ['user_name', 'product_name', 'days_remaining', 'renewal_url', 'site_name', 'account_subscriptions_url'],
        ]);

        $templateManager->register('ORDER_CANCELLATION_REQUEST', [
            'label' => 'Order Cancellation Request',
            'group' => 'ecommerce',
            'default_subject' => 'Cancellation Request Received - Order #{order_code}',
            'default_body' => '<h1>Cancellation Request</h1><p>Hello {user_name}, we have received your request to cancel order #{order_code}. Our team will review it shortly.</p>',
            'variables' => ['user_name', 'order_code', 'site_name', 'account_orders_url'],
        ]);

        $templateManager->register('ORDER_CANCELLED', [
            'label' => 'Order Cancelled',
            'group' => 'ecommerce',
            'default_subject' => 'Order Cancelled - Order #{order_code}',
            'default_body' => '<h1>Order Cancelled</h1><p>Hello {user_name}, your order #{order_code} has been cancelled.</p>',
            'variables' => ['user_name', 'order_code', 'site_name', 'account_orders_url'],
        ]);

        $templateManager->register('ORDER_REFUND_PROCESSED', [
            'label' => 'Order Refund Processed',
            'group' => 'ecommerce',
            'default_subject' => 'Refund processed for order #{order_code}',
            'default_body' => '<h1>Refund Processed</h1><p>Hello {user_name}, we have processed a refund for order #{order_code}.</p><p>Refund amount: {refund_amount} {currency}</p><p>Status: {refund_status}</p>',
            'variables' => ['user_name', 'order_code', 'refund_amount', 'currency', 'refund_status', 'reason', 'site_name', 'account_orders_url'],
        ]);

        $templateManager->register('REFUND_REQUEST_RECEIVED', [
            'label' => 'Refund Request Received',
            'group' => 'ecommerce',
            'default_subject' => 'Refund request received - {request_code}',
            'default_body' => '<h1>Refund Request Received</h1><p>Hello {user_name}, we have received your refund request {request_code} for order #{order_code}.</p><p>Reason: {reason}</p>',
            'variables' => ['user_name', 'request_code', 'order_code', 'reason', 'site_name', 'account_orders_url', 'account_subscriptions_url'],
        ]);

        $templateManager->register('REFUND_REQUEST_STATUS_UPDATED', [
            'label' => 'Refund Request Status Updated',
            'group' => 'ecommerce',
            'default_subject' => 'Refund request {request_code} is now {request_status}',
            'default_body' => '<h1>Refund Request Update</h1><p>Hello {user_name}, your refund request {request_code} has been updated to: {request_status}.</p><p>{admin_note}</p>',
            'variables' => ['user_name', 'request_code', 'request_status', 'admin_note', 'site_name', 'account_orders_url', 'account_subscriptions_url'],
        ]);

        $templateManager->register('ADMIN_REFUND_REQUEST_ALERT', [
            'label' => 'Admin Refund Request Alert',
            'group' => 'ecommerce',
            'default_subject' => '[Admin] New refund request {request_code}',
            'default_body' => '<h1>New Refund Request</h1><p>Request: {request_code}</p><p>User: {user_name}</p><p>Order: #{order_code}</p><p>Reason: {reason}</p><p><a href="{admin_refund_requests_url}">Open refund request inbox</a></p>',
            'variables' => ['request_code', 'user_name', 'order_code', 'reason', 'site_name', 'admin_refund_requests_url'],
        ]);
    }

    protected function registerCorePaymentGatewaySchemas(): void
    {
        Hook::addFilter('payment.gateway.config_schema', function ($schema, $gateway = null) {
            $code = $gateway->code ?? null;

            if ($code === 'cod') {
                return [
                    ['key' => 'instructions', 'label' => 'Instructions', 'type' => 'textarea', 'order' => 10],
                    ['key' => 'min_order_amount', 'label' => 'Min Order Amount', 'type' => 'number', 'order' => 20],
                    ['key' => 'max_order_amount', 'label' => 'Max Order Amount', 'type' => 'number', 'order' => 30],
                    ['key' => 'additional_fee', 'label' => 'Additional Fee', 'type' => 'number', 'order' => 40],
                    [
                        'key' => 'fee_type',
                        'label' => 'Fee Type',
                        'type' => 'select',
                        'options' => [
                            ['label' => 'Fixed', 'value' => 'fixed'],
                            ['label' => 'Percentage', 'value' => 'percentage'],
                        ],
                        'order' => 50,
                    ],
                    ['key' => 'available_areas', 'label' => 'Available Areas', 'type' => 'text', 'order' => 60],
                ];
            }

            if ($code === 'bank_transfer') {
                return [
                    ['key' => 'instructions', 'label' => 'Instructions', 'type' => 'textarea', 'order' => 10],
                    [
                        'key' => 'banks',
                        'label' => 'Banks',
                        'type' => 'json',
                        'order' => 20,
                        'description' => 'JSON array of bank accounts. Example item: {"bank_name":"Vietcombank","account_number":"123456789","account_holder":"NGUYEN VAN A","is_primary":true}',
                    ],
                    ['key' => 'processing_fee', 'label' => 'Processing Fee', 'type' => 'number', 'order' => 30],
                ];
            }

            return $schema;
        }, 10, 2);
    }

    protected function registerCoreLayoutAssets(): void
    {
        $manager = app(LayoutAssetManager::class);

        $manager->registerPart('core.demo_showcase', [
            'name' => 'Demo Showcase',
            'slug' => 'demo-showcase',
            'description' => 'A reusable two-column demo showcase built from the core landing elements.',
            'category' => 'default',
            'preview_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80',
            'content_raw' => $this->createLandingDocument([
                [
                    'type' => 'landingBlock',
                    'attrs' => [
                        'id' => (string) Str::uuid(),
                        'type' => 'row',
                        'data' => [
                            'columns' => 2,
                            'layout_preset' => 'halves',
                            'column_widths' => ['1/2', '1/2'],
                            'gap' => 'gap-8',
                            'vertical_align' => 'center',
                            'columns_data' => [
                                [
                                    'blocks' => [
                                        [
                                            'type' => 'video',
                                            'data' => [
                                                'url' => 'https://www.youtube.com/watch?v=C_QAOi0_qpg',
                                                'preview_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80',
                                                'aspect_ratio' => '16/10',
                                                'caption' => '',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'blocks' => [
                                        [
                                            'type' => 'heading',
                                            'data' => [
                                                'text' => 'From Zero to SaaS in 10 Days',
                                                'level' => 2,
                                                'alignment' => 'left',
                                                'font_weight' => 'font-bold',
                                                'color' => '',
                                            ],
                                        ],
                                        ['type' => 'spacer', 'data' => ['height' => 24]],
                                        [
                                            'type' => 'row',
                                            'data' => [
                                                'columns' => 2,
                                                'layout_preset' => 'halves',
                                                'column_widths' => ['1/2', '1/2'],
                                                'gap' => 'gap-4',
                                                'vertical_align' => 'center',
                                                'columns_data' => [
                                                    [
                                                        'blocks' => [[
                                                            'type' => 'button',
                                                            'data' => [
                                                                'label' => 'Start Now',
                                                                'url' => '#',
                                                                'style' => 'primary',
                                                                'size' => 'px-6 py-3 text-base',
                                                                'alignment' => 'full',
                                                            ],
                                                        ]],
                                                    ],
                                                    [
                                                        'blocks' => [[
                                                            'type' => 'button',
                                                            'data' => [
                                                                'label' => 'Learn More',
                                                                'url' => '#',
                                                                'style' => 'secondary',
                                                                'size' => 'px-6 py-3 text-base',
                                                                'alignment' => 'full',
                                                            ],
                                                        ]],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        ['type' => 'spacer', 'data' => ['height' => 28]],
                                        [
                                            'type' => 'heading',
                                            'data' => [
                                                'text' => 'Branding & Setup',
                                                'level' => 4,
                                                'alignment' => 'left',
                                                'font_weight' => 'font-bold',
                                                'color' => '',
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'data' => [
                                                'content' => 'We customize everything with your logo and colors.',
                                                'font_size' => 'text-base',
                                                'alignment' => 'left',
                                                'color' => '#6b7280',
                                            ],
                                        ],
                                        ['type' => 'spacer', 'data' => ['height' => 20]],
                                        [
                                            'type' => 'heading',
                                            'data' => [
                                                'text' => 'Training & Handover',
                                                'level' => 4,
                                                'alignment' => 'left',
                                                'font_weight' => 'font-bold',
                                                'color' => '',
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'data' => [
                                                'content' => 'We walk you through the admin panel.',
                                                'font_size' => 'text-base',
                                                'alignment' => 'left',
                                                'color' => '#6b7280',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $manager->registerPart('core.what_you_get', [
            'name' => 'What You Get',
            'slug' => 'what-you-get',
            'description' => 'Built-in marketing section registered by core.',
            'category' => 'default',
            'content_raw' => $this->createSingleLandingBlockDocument('what_you_get', [
                'heading' => "Here's Exactly What You Get",
                'subheading' => 'A complete invoice SaaS solution that saves you 6+ months of development time',
                'button_text' => 'Tour Our Tool',
                'button_link' => '#',
            ]),
        ]);

        $manager->registerPart('core.cta_section', [
            'name' => 'CTA Section',
            'slug' => 'cta-section',
            'description' => 'Built-in call-to-action section registered by core.',
            'category' => 'default',
            'content_raw' => $this->createSingleLandingBlockDocument('cta_section', [
                'heading' => 'Ready to Launch Your SaaS Business?',
                'text' => 'Stop building from scratch. Get a proven, ready-to-launch invoice SaaS with your branding in days, not months.',
                'info_text' => 'Fill the form below and we will contact you with pricing and next steps.',
                'form_html' => '',
                'stats' => [
                    ['number' => '10+', 'label' => 'Successful Launches'],
                    ['number' => '100%', 'label' => 'White-Label Ready'],
                    ['number' => '7-10', 'label' => 'Days Delivery'],
                ],
            ]),
        ]);

        $manager->registerTemplate('core.landing_starter', [
            'name' => 'Landing Starter',
            'slug' => 'landing-starter',
            'description' => 'A starter landing template composed from reusable core sections.',
            'category' => 'default',
            'applies_to' => ['page', 'post'],
            'content_raw' => $this->createLandingDocument([
                [
                    'type' => 'landingBlock',
                    'attrs' => [
                        'id' => (string) Str::uuid(),
                        'type' => 'hero_section',
                        'data' => [
                            'heading' => 'Launch Faster With PolyCMS',
                            'subheading' => 'Compose landing pages from reusable parts, templates, and elements without rebuilding sections each time.',
                            'button_text' => 'Start Building',
                            'button_link' => '#',
                        ],
                    ],
                ],
                ...($this->createLandingDocumentContent($this->createLandingDocument([
                    [
                        'type' => 'landingBlock',
                        'attrs' => [
                            'id' => (string) Str::uuid(),
                            'type' => 'row',
                            'data' => $manager->getRegistered('part')['core.demo_showcase']['content_raw']['content'][0]['attrs']['data'] ?? [],
                        ],
                    ],
                ]))),
                [
                    'type' => 'landingBlock',
                    'attrs' => [
                        'id' => (string) Str::uuid(),
                        'type' => 'cta_section',
                        'data' => [
                            'heading' => 'Ship reusable design systems across themes and modules',
                            'text' => 'Build once, register centrally, and apply the same templates to posts or pages.',
                            'info_text' => 'This template is intended as a starting point.',
                            'form_html' => '',
                            'stats' => [
                                ['number' => '1', 'label' => 'Template System'],
                                ['number' => '3', 'label' => 'Reusable Parts'],
                                ['number' => '∞', 'label' => 'Theme Extensions'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }

    protected function createSingleLandingBlockDocument(string $type, array $data): array
    {
        return $this->createLandingDocument([
            [
                'type' => 'landingBlock',
                'attrs' => [
                    'id' => (string) Str::uuid(),
                    'type' => $type,
                    'data' => $data,
                ],
            ],
        ]);
    }

    protected function createLandingDocument(array $content): array
    {
        return [
            'type' => 'doc',
            'content' => $content,
        ];
    }

    protected function createLandingDocumentContent(array $doc): array
    {
        return $doc['content'] ?? [];
    }

    /**
     * Bootstrap sync fingerprint helpers.
     */
    protected function shouldSyncBootstrapData(ModuleManager $moduleManager): bool
    {
        $fingerprint = $this->buildBootstrapSyncFingerprint($moduleManager);
        return Cache::get('polycms.bootstrap.sync_fingerprint') !== $fingerprint;
    }

    protected function markBootstrapDataSynced(ModuleManager $moduleManager): void
    {
        Cache::forever(
            'polycms.bootstrap.sync_fingerprint',
            $this->buildBootstrapSyncFingerprint($moduleManager)
        );
    }

    protected function buildBootstrapSyncFingerprint(ModuleManager $moduleManager): string
    {
        $parts = [
            (string) (@filemtime(app_path('Providers/AppServiceProvider.php')) ?: 0),
            (string) (@filemtime(config_path('permissions.php')) ?: 0),
            (string) (@filemtime(config_path('modules.php')) ?: 0),
        ];

        $modules = $moduleManager->discoverModules();

        foreach ($modules as $moduleKey => $module) {
            if (!($module['enabled'] ?? false)) {
                continue;
            }

            $parts[] = (string) $moduleKey;
            $parts[] = (string) ($module['version'] ?? '');
            $parts[] = (string) (@filemtime($module['path'] . '/module.json') ?: 0);
        }

        return sha1(implode('|', $parts));
    }

    /**
     * Configure filesystem default driver at runtime.
     */
    protected function configureFilesystem(): void
    {
        $settings = app(\App\Services\SettingsService::class);
        $driver = $settings->get('media_driver', config('filesystems.default', 'local'));
        
        // If s3 is active, Ensure CloudStorage module actually set the credentials!
        // The CloudStorageServiceProvider runs hook to inject settings.
        config(['filesystems.default' => $driver]);
    }

    protected function configureMail(): void
    {
        // Don't configure during console/migration if not needed, 
        // but for artisan tinker/serve we need it.
        $settings = app(\App\Services\SettingsService::class);
        
        $driver = $settings->get('email_driver', config('mail.default'));
        config(['mail.default' => $driver]);

        \Illuminate\Support\Facades\Log::debug("Configuring mail driver: {$driver}");

        if ($driver === 'smtp') {
            config([
                'mail.mailers.smtp.host' => $settings->get('email_smtp_host', config('mail.mailers.smtp.host')),
                'mail.mailers.smtp.port' => $settings->get('email_smtp_port', config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.encryption' => $settings->get('email_smtp_encryption', config('mail.mailers.smtp.encryption')),
                'mail.mailers.smtp.username' => $settings->get('email_smtp_username', config('mail.mailers.smtp.username')),
                'mail.mailers.smtp.password' => $settings->get('email_smtp_password', config('mail.mailers.smtp.password')),
            ]);
        } elseif ($driver === 'google') {
            config([
                'mail.mailers.google' => [
                    'transport' => 'google',
                    'client_id' => $settings->get('email_google_client_id'),
                    'client_secret' => $settings->get('email_google_client_secret'),
                    'refresh_token' => $settings->get('email_google_refresh_token'),
                    'username' => $settings->get('email_google_email'),
                ]
            ]);
        }

        // Configure common from address
        $fromEmail = $settings->get('email_from_address', config('mail.from.address'));
        $fromName = $settings->get('email_from_name', config('mail.from.name'));
        config([
            'mail.from.address' => $fromEmail,
            'mail.from.name' => $fromName,
        ]);
    }
}
