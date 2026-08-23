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

        $this->commands([
            \App\Console\Commands\BackfillMissingLicensesCommand::class,
        ]);

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

        // Register Account Menu Registry as singleton
        $this->app->singleton(\App\Services\AccountMenuRegistry::class, function ($app) {
            return new \App\Services\AccountMenuRegistry();
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
        // Global system cache & queue settings override
        try {
            if (file_exists(storage_path('installed.lock')) && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $cacheEnabled = app(\App\Services\SettingsService::class)->get('polycms_cache_enabled', 'yes');
                if ($cacheEnabled === 'no') {
                    config(['cache.default' => 'array']);
                }

                $queueConnectionSetting = app(\App\Services\SettingsService::class)->get('queue_connection');
                if ($queueConnectionSetting) {
                    config(['queue.default' => $queueConnectionSetting]);
                }
            }
        } catch (\Throwable) {
            // Silently use default during bootstrap/migration
        }

        // Global Fail-Safe Redis Protection Guard (Cache & Queue)
        // Automatically prevents site crash when Redis PHP extension or server is missing
        try {
            $hasPhpRedis = extension_loaded('redis') || class_exists(\Redis::class);
            $hasPredis = class_exists(\Predis\Client::class);

            if (!$hasPhpRedis && !$hasPredis) {
                if (config('cache.default') === 'redis') {
                    config(['cache.default' => 'file']);
                }
                if (config('queue.default') === 'redis') {
                    config(['queue.default' => 'sync']);
                }
            } else {
                if (config('cache.default') === 'redis' || config('queue.default') === 'redis') {
                    try {
                        if ($hasPhpRedis) {
                            \Illuminate\Support\Facades\Redis::connection()->ping();
                        }
                    } catch (\Throwable $ex) {
                        if (config('cache.default') === 'redis') {
                            config(['cache.default' => 'file']);
                        }
                        if (config('queue.default') === 'redis') {
                            config(['queue.default' => 'sync']);
                        }
                        \Illuminate\Support\Facades\Log::warning('PolyCMS Redis Guard: Cannot connect to Redis server. Automatically falling back to File Cache / Sync Queue: ' . $ex->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            if (config('cache.default') === 'redis') {
                config(['cache.default' => 'file']);
            }
            if (config('queue.default') === 'redis') {
                config(['queue.default' => 'sync']);
            }
        }

        // Ensure cache directories are writable — prevents site crash
        // when artisan commands run as root create dirs with wrong ownership
        $this->ensureCacheDirectoriesWritable();

        // Force HTTPS scheme for URL generation when configured
        if (env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Dynamic API rate limit — configurable via Settings → General
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            // Bypass rate limiting in local environment
            if (config('app.env') === 'local') {
                return \Illuminate\Cache\RateLimiting\Limit::none();
            }

            $rateLimit = 300; // default fallback
            try {
                if (file_exists(storage_path('installed.lock')) && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $rateLimit = (int) app(\App\Services\SettingsService::class)->get('api_rate_limit', 300);
                }
            } catch (\Throwable) {
                // Silently use default during bootstrap/migration
            }
            return \Illuminate\Cache\RateLimiting\Limit::perMinute($rateLimit)->by($request->user()?->id ?: $request->ip());
        });

        // Dynamic session lifetime — configurable via Settings → General
        try {
            if (file_exists(storage_path('installed.lock')) && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $sessionLifetime = (int) app(\App\Services\SettingsService::class)->get('session_lifetime', 120);
                if ($sessionLifetime > 0) {
                    config(['session.lifetime' => $sessionLifetime]);
                }
            }
        } catch (\Throwable) {
            // Silently use default
        }

        Vite::prefetch(concurrency: 3);

        // Register module autoloaders - always needed for class resolution
        $moduleManager = app(ModuleManager::class);
        $moduleManager->registerAutoloaders();

        // Then register module service providers
        $moduleManager->registerModules();

        // Initialize Language Helper
        \App\Helpers\LanguageHelper::init(app(\App\Services\SettingsService::class));

        // Register multi-language URL prefix filters
        $prependLocale = function (string $url, $model = null) {
            $locale = null;
            if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                $modelLocale = $model->locale;
                $currentLanguage = \App\Helpers\LanguageHelper::getCurrentLanguage() ?: \Illuminate\Support\Facades\App::getLocale();

                // Check if the model's locale is the default locale
                $isModelLocaleDefault = cache()->remember("is_default_locale_{$modelLocale}", 3600, function () use ($modelLocale) {
                    try {
                        if (!Schema::hasTable('languages')) {
                            return true;
                        }
                        if (!$modelLocale) {
                            return true;
                        }
                        return (bool) \App\Models\Language::where('code', $modelLocale)->where('is_default', true)->exists();
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('is_default_locale exception: ' . $e->getMessage());
                        return true;
                    }
                });

                $locale = $modelLocale;

                // If the model is in default language but the current language is non-default,
                // try to find the translation in the current language and swap the slug.
                if ($isModelLocaleDefault && $currentLanguage && $currentLanguage !== $modelLocale) {
                    $isCurrentDefault = cache()->remember("is_default_locale_{$currentLanguage}", 3600, function () use ($currentLanguage) {
                        try {
                            if (!Schema::hasTable('languages')) {
                                return true;
                            }
                            return (bool) \App\Models\Language::where('code', $currentLanguage)->where('is_default', true)->exists();
                        } catch (\Exception $e) {
                            return true;
                        }
                    });

                    if (!$isCurrentDefault) {
                        if (method_exists($model, 'getTranslation')) {
                            $translation = $model->getTranslation($currentLanguage);
                            if ($translation) {
                                $locale = $currentLanguage;
                                if (!empty($model->slug) && !empty($translation->slug) && $model->slug !== $translation->slug) {
                                    $slugStr = (string) $model->slug;
                                    $pos = strrpos($url, $slugStr);
                                    if ($pos !== false && $pos + strlen($slugStr) === strlen($url)) {
                                        $url = substr_replace($url, (string) $translation->slug, $pos, strlen($slugStr));
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!$locale) {
                $locale = \App\Helpers\LanguageHelper::getCurrentLanguage();
            }

            $isDefault = cache()->remember("is_default_locale_{$locale}", 3600, function () use ($locale) {
                try {
                    if (!Schema::hasTable('languages')) {
                        return true;
                    }
                    return (bool) \App\Models\Language::where('code', $locale)->where('is_default', true)->exists();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('is_default_locale locale exception: ' . $e->getMessage());
                    return true;
                }
            });

            if (!$isDefault) {
                // Ensure we don't double prepend if it already starts with the locale prefix
                $path = ltrim($url, '/');
                if (!str_starts_with($path, $locale . '/')) {
                    return '/' . $locale . '/' . $path;
                }
            }
            return $url;
        };

        Hook::addFilter('post.frontend_url', $prependLocale, 50, 2);
        Hook::addFilter('category.frontend_url', $prependLocale, 50, 2);
        Hook::addFilter('product.frontend_url', $prependLocale, 50, 2);
        Hook::addFilter('product_category.frontend_url', $prependLocale, 50, 2);
        Hook::addFilter('product_brand.frontend_url', $prependLocale, 50, 2);
        Hook::addFilter('tag.frontend_url', $prependLocale, 50, 2);
        Hook::addFilter('product_tag.frontend_url', $prependLocale, 50, 2);
        Hook::addFilter('project.frontend_url', $prependLocale, 50, 2);

        // Dynamically rewrite hardcoded internal post links matching the posts single base permalink
        Hook::addFilter('post.content.render', function (string $html, $post) {
            if (empty($html)) {
                return $html;
            }

            // Match links like href="/posts/{slug}"
            preg_match_all('/href=["\']\/posts\/([A-Za-z0-9\-_]+)(["\'])/', $html, $matches);

            if (empty($matches[1])) {
                return $html;
            }

            $slugs = array_unique($matches[1]);
            $locale = $post->locale ?? 'en';

            // Retrieve target posts of the same locale to resolve their correct frontend_url
            try {
                $targetPosts = \App\Models\Post::whereIn('slug', $slugs)
                    ->where('locale', $locale)
                    ->get();

                // Fallback to any locale if not found in the current locale
                $foundSlugs = $targetPosts->pluck('slug')->toArray();
                $missingSlugs = array_diff($slugs, $foundSlugs);
                if (!empty($missingSlugs)) {
                    $fallbackPosts = \App\Models\Post::whereIn('slug', $missingSlugs)->get();
                    $targetPosts = $targetPosts->merge($fallbackPosts);
                }

                $targetPosts = $targetPosts->keyBy('slug');
            } catch (\Exception $e) {
                // If database query fails (e.g. during migrations/tests), fallback to manual routing
                $targetPosts = collect();
            }

            return preg_replace_callback('/(href=["\'])\/posts\/([A-Za-z0-9\-_]+)(["\'])/', function ($m) use ($targetPosts) {
                $slug = $m[2];
                if ($targetPosts->has($slug)) {
                    return $m[1] . $targetPosts->get($slug)->frontend_url . $m[3];
                }

                // Fallback using the current settings config
                try {
                    $permalinks = app(\App\Services\SettingsService::class)->getPermalinkStructure();
                } catch (\Exception $e) {
                    $permalinks = [];
                }
                $postsSingleBase = trim($permalinks['posts']['single'] ?? 'posts', '/');
                $newPath = $postsSingleBase !== '' ? '/' . $postsSingleBase . '/' . $slug : '/' . $slug;

                $locale = app()->getLocale();
                $isDefault = cache()->remember("is_default_locale_{$locale}", 3600, function () use ($locale) {
                    try {
                        if (!\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                            return true;
                        }
                        return (bool) \App\Models\Language::where('code', $locale)->where('is_default', true)->exists();
                    } catch (\Exception $e) {
                        return true;
                    }
                });

                if (!$isDefault) {
                    $newPath = '/' . $locale . $newPath;
                }

                return $m[1] . $newPath . $m[3];
            }, $html);
        }, 10, 2);

        // Core favicon URL normalization filter
        Hook::addFilter('seo.site_favicon', function (?string $siteIconUrl) {
            if ($siteIconUrl) {
                if (!str_starts_with($siteIconUrl, 'http') && !str_starts_with($siteIconUrl, '/')) {
                    if (str_starts_with($siteIconUrl, 'storage/')) {
                        return asset($siteIconUrl);
                    } else {
                        return \Illuminate\Support\Facades\Storage::disk('public')->exists($siteIconUrl)
                            ? \Illuminate\Support\Facades\Storage::url($siteIconUrl)
                            : asset($siteIconUrl);
                    }
                }
            }
            return $siteIconUrl;
        }, 99, 1);

        Hook::addFilter('admin.editor.panels', function (array $panels, string $type) {
            try {
                if (Schema::hasTable('languages') && \App\Models\Language::where('is_active', true)->count() > 1) {
                    $panels[] = [
                        'key' => 'languages',
                        'label' => 'Languages / Ngôn ngữ',
                        'area' => 'sidebar',
                        'order' => 2,
                        'component' => 'LanguageSelectorPanel',
                        'collapsible' => true,
                    ];
                }
            } catch (\Exception $e) {}
            return $panels;
        }, 10, 2);

        Hook::addAction('order.refund.completed', function ($order, $result) {
            $productIds = $order->items->pluck('product_id')->toArray();
            
            // Cancel matching active subscriptions
            $subscriptions = \App\Models\Ecommerce\UserSubscription::where('user_id', $order->user_id)
                ->whereIn('product_id', $productIds)
                ->where('status', 'active')
                ->get();
                
            foreach ($subscriptions as $sub) {
                $sub->update(['status' => 'cancelled']);
                
                // Revoke associated active licenses
                \App\Models\Ecommerce\ProductLicense::where('subscription_id', $sub->id)
                    ->where('status', 'active')
                    ->update(['status' => 'revoked']);
            }
        }, 10, 2);

        // ALWAYS SKIP database-dependent boot logic if the system is not yet installed.
        if (!file_exists(storage_path('installed.lock'))) {
            return;
        }


        $this->commands([
            \App\Console\Commands\BackfillMissingLicensesCommand::class,
        ]);

        if (app()->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            if (collect($argv)->contains(fn($arg) => str_contains($arg, 'migrate'))) {
                return;
            }
        }

        if (!Schema::hasTable('settings')) {
            return;
        }

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
            $layoutAssetManager->syncDatabase();
            $this->markBootstrapDataSynced($moduleManager);
        }

        // Register core & module email templates
        $this->registerCoreEmailTemplates();
        Hook::doAction('register_email_templates', app(\App\Services\EmailTemplateManager::class));

        // Register core atomic landing block renderers
        $this->registerCoreLandingBlocks();

        // Share settings with views
        $this->shareSettingsWithViews();

        // Configure Cache at runtime based on settings
        $this->configureCache();

        // Configure Mail at runtime based on settings
        $this->configureMail();

        // Configure Filesystem default driver
        $this->configureFilesystem();

        // Register Active Main Theme CSS Variables & Link Styling (Core Multi-Theme support)
        Hook::addAction('cms_head', function () {
            try {
                $themeOptionValues = \App\Facades\Hook::applyFilters('theme.options.values', theme_get_options());
                $primaryColor = $themeOptionValues['theme_color_primary'] ?? '#2563eb';
                $linkColor = $themeOptionValues['theme_anchor_color'] ?? $primaryColor;
                $linkHoverColor = $themeOptionValues['theme_anchor_hover_color'] ?? '#1e40af';

                echo '<style id="polycms-core-theme-vars">' . PHP_EOL;
                echo ':root {' . PHP_EOL;
                echo '  --theme-link-color: ' . htmlspecialchars($linkColor, ENT_QUOTES, 'UTF-8') . ';' . PHP_EOL;
                echo '  --theme-link-hover-color: ' . htmlspecialchars($linkHoverColor, ENT_QUOTES, 'UTF-8') . ';' . PHP_EOL;
                echo '  --color-primary: ' . htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8') . ';' . PHP_EOL;
                echo '}' . PHP_EOL;
                echo 'html.dark, html.dark body, :root.dark {' . PHP_EOL;
                echo '  --theme-link-color: #4299e1 !important;' . PHP_EOL;
                echo '  --theme-link-hover-color: #63b3ed !important;' . PHP_EOL;
                echo '}' . PHP_EOL;
                echo '</style>' . PHP_EOL;
            } catch (\Throwable $e) {}
        }, 1);

        // Register SEO meta tags in head
        Hook::addAction('cms_head', [$this, 'renderSeoMeta']);

        // Register Core CSS Animations
        Hook::addAction('cms_head', function() {
            echo '<link rel="stylesheet" href="'.\Illuminate\Support\Facades\Vite::asset('resources/css/poly-animations.css').'">' . PHP_EOL;
        });

        // PolyCMS Core Cache & Acceleration Hooks (Instant Prefetch, Speculation Rules, Execution Time Badges)
        Hook::addAction('cms_head', function () {
            $settingsService = app(\App\Services\SettingsService::class);
            if ($settingsService->get('polycms_cache_enabled', 'yes') === 'yes' &&
                $settingsService->get('speculation_rules_enabled', 'yes') === 'yes') {
                $rules = [
                    'prefetch' => [
                        [
                            'source' => 'document',
                            'where' => [
                                'and' => [
                                    ['href_matches' => '/*'],
                                    ['not' => ['href_matches' => '/admin/*']],
                                    ['not' => ['href_matches' => '/*admin*']],
                                    ['not' => ['href_matches' => '/api/*']],
                                    ['not' => ['href_matches' => '/*login*']],
                                    ['not' => ['href_matches' => '/*register*']],
                                    ['not' => ['href_matches' => '/*account*']],
                                    ['not' => ['href_matches' => '/*auth*']],
                                    ['not' => ['href_matches' => '/*logout*']],
                                    ['not' => ['href_matches' => '/*cart*']],
                                    ['not' => ['href_matches' => '/*checkout*']],
                                    ['not' => ['href_matches' => '/*password*']],
                                ]
                            ],
                            'eagerness' => 'conservative'
                        ]
                    ]
                ];
                echo '<script type="speculationrules">' . json_encode($rules, JSON_UNESCAPED_SLASHES) . '</script>' . PHP_EOL;
            }
        });

        Hook::addAction('cms_footer', function () {
            $settingsService = app(\App\Services\SettingsService::class);
            $cacheEnabled = $settingsService->get('polycms_cache_enabled', 'yes') === 'yes';
            $instantPrefetch = $settingsService->get('instant_prefetch_enabled', 'yes') === 'yes';

            if ($cacheEnabled && $instantPrefetch) {
                echo '<script>
                (function() {
                    document.addEventListener("mouseover", function(e) {
                        var link = e.target.closest("a");
                        if (!link || !link.href) return;
                        if (!link.href.startsWith(location.origin)) return;

                        // Exclude prefetching the current active page
                        var cleanLinkHref = link.href.split("#")[0].split("?")[0];
                        var cleanCurrentHref = location.href.split("#")[0].split("?")[0];
                        if (cleanLinkHref === cleanCurrentHref) return;

                        var href = link.getAttribute("href") || "";
                        
                        // 1. Explicit no-prefetch flags
                        if (link.hasAttribute("data-prefetched") || link.hasAttribute("data-no-prefetch") || link.classList.contains("no-prefetch")) return;

                        // 2. Interactive modal / popup triggers
                        if (link.hasAttribute("data-bs-toggle") || link.hasAttribute("data-toggle") || link.hasAttribute("data-modal") || link.hasAttribute("data-lightbox") || link.hasAttribute("data-fancybox")) return;

                        // 3. Target / rel attributes
                        var target = link.getAttribute("target") || "";
                        var rel = link.getAttribute("rel") || "";
                        if (target === "_blank" || rel.includes("nofollow") || rel.includes("sponsored")) return;

                        // 4. Exclude javascript links, anchors, and query strings
                        if (href.startsWith("#") || href.startsWith("javascript:") || href.includes("?")) return;

                        // 5. Path pattern exclusions
                        var lowercaseHref = href.toLowerCase();
                        var excludePaths = ["/admin", "/logout", "/login", "/register", "/auth", "/account", "/cart", "/checkout", "/add-to-cart", "/remove-cart", "/wishlist", "/compare", "/download", "/file/", "/lang/", "/locale/", "/currency/", "/switch-"];
                        for (var i = 0; i < excludePaths.length; i++) {
                            if (lowercaseHref.includes(excludePaths[i])) return;
                        }

                        // 6. Downloadable file extension exclusions
                        var fileExts = [".pdf", ".zip", ".rar", ".7z", ".tar", ".gz", ".mp4", ".mp3", ".doc", ".docx", ".xls", ".xlsx", ".exe", ".apk"];
                        for (var j = 0; j < fileExts.length; j++) {
                            if (lowercaseHref.endsWith(fileExts[j])) return;
                        }

                        var prefetchLink = document.createElement("link");
                        prefetchLink.rel = "prefetch";
                        prefetchLink.href = link.href;
                        document.head.appendChild(prefetchLink);
                        link.setAttribute("data-prefetched", "true");
                    });
                })();
                </script>' . PHP_EOL;
            }
            if (show_execution_time_badge()) {
                echo '<style>
                .execution-time-badge {
                    font-size: 0.8125rem;
                    font-weight: 500;
                    color: var(--geist-accents-5, #888);
                    white-space: nowrap;
                    display: inline-flex;
                    align-items: center;
                }
                @media (max-width: 640px) {
                    .execution-time-badge {
                        font-size: 0.54rem !important;
                        letter-spacing: -0.02em !important;
                        max-width: calc(100vw - 110px);
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    .execution-time-badge svg {
                        width: 0.52rem !important;
                        height: 0.52rem !important;
                    }
                }
                </style>' . PHP_EOL;

                echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        var perf = performance.getEntriesByType("navigation")[0];
                        if (perf) {
                            var rawMs = 0;
                            var start = perf.activationStart || perf.requestStart || perf.startTime || 0;
                            if (perf.responseEnd && perf.responseEnd > 0 && perf.responseEnd > start) {
                                rawMs = perf.responseEnd - start;
                            } else if (perf.domInteractive && perf.domInteractive > 0 && perf.domInteractive > start) {
                                rawMs = perf.domInteractive - start;
                            } else {
                                rawMs = perf.duration || 0;
                            }
                            var pageMs = isNaN(rawMs) || rawMs <= 0 ? "0.00" : parseFloat(rawMs).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            document.querySelectorAll(".execution-time-badge").forEach(function(badge) {
                                var serverMs = badge.getAttribute("data-server-ms") || "";
                                var boltSvg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 0.68rem; height: 0.68rem; display: inline-block; vertical-align: -1px; margin-right: 2px; color: var(--geist-accents-5, #888);"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.265-.723l1.992-7.289H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd" /></svg>\';
                                var rocketSvg = \'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 0.68rem; height: 0.68rem; display: inline-block; vertical-align: -1px; margin-right: 2px; color: var(--geist-accents-5, #888);"><path d="M10.5 1.5a.75.75 0 0 0-.75.75v1.5h-1.5a.75.75 0 0 0 0 1.5h1.5v1.5a.75.75 0 0 0 1.5 0V5.25h1.5a.75.75 0 0 0 0-1.5h-1.5V2.25a.75.75 0 0 0-.75-.75ZM6 6a.75.75 0 0 0-.75.75v3h-3a.75.75 0 0 0 0 1.5h3v3a.75.75 0 0 0 1.5 0v-3h3a.75.75 0 0 0 0-1.5h-3v-3A.75.75 0 0 0 6 6Zm12 1.5a.75.75 0 0 0-.75.75v3h-3a.75.75 0 0 0 0 1.5h3v3a.75.75 0 0 0 1.5 0v-3h3a.75.75 0 0 0 0-1.5h-3v-3A.75.75 0 0 0 18 7.5Z"/></svg>\';
                                badge.innerHTML = boltSvg + serverMs + " ms (Server) <span style=\"opacity: 0.4; margin: 0 3px;\">|</span> " + rocketSvg + pageMs + " ms (Page)";
                            });
                        }
                    }, 10);
                });
                </script>' . PHP_EOL;
            }
        });

        // Auto-update menu item URLs when permalink settings change
        Hook::addAction('settings.saved', function ($payload) {
            if (($payload['group'] ?? '') !== 'permalinks') {
                return;
            }
            $this->updateMenuItemUrlsAfterPermalinkChange();
        });

        // Auto-fulfill orders when status updates to processing or completed
        Hook::addAction('order_status_updated', function ($order, $oldStatus, $newStatus) {
            if (in_array($newStatus, ['processing', 'completed'])) {
                try {
                    $fulfillment = new \App\Services\Ecommerce\OrderFulfillmentService();
                    $fulfillment->fulfillOrder($order);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Fulfillment failed for order #' . $order->id . ': ' . $e->getMessage());
                }
            }
        }, 10, 3);

        // Display sales count on product page if product has sales
        Hook::addAction('theme.product.single.meta', function ($product) {
            $salesCount = $product->sales_count;
            if ($salesCount > 0) {
                echo '<span class="single-product-meta-item">
                    <i class="fas fa-shopping-cart stats-icon"></i>
                    <strong class="stats-num">' . $salesCount . '</strong> <span class="stats-label">' . _l('sold') . '</span>
                </span>';
            }
        });

        // Invalidate admin menu cache when a module is activated or deactivated
        Hook::addAction('module.activating', function() {
            try {
                \Illuminate\Support\Facades\Cache::forget('polycms.admin_menu.version');
                \App\Support\ResilientCache::put('polycms.admin_menu.version', time());
            } catch (\Throwable $e) {}
        });

        Hook::addAction('module.deactivating', function() {
            try {
                \Illuminate\Support\Facades\Cache::forget('polycms.admin_menu.version');
                \App\Support\ResilientCache::put('polycms.admin_menu.version', time());
            } catch (\Throwable $e) {}
        });

        // Register settings defaults via hook filter to avoid modifying core definitions
        Hook::addFilter('settings.defaults', function (array $defaults): array {
            $defaults['cache_optimization']['execution_time_badge_enabled'] = [
                'key' => 'execution_time_badge_enabled',
                'value' => 'yes',
                'type' => 'string',
                'label' => 'Show Execution Time Badge',
                'description' => 'Toggle displaying the server and page rendering execution time badge on frontend themes.',
            ];
            return $defaults;
        });

        // Register execution time badge rendering action listeners
        $renderBadgeCallback = function ($entity = null) {
            if (show_execution_time_badge()) {
                $execMs = defined('LARAVEL_START') ? number_format((microtime(true) - LARAVEL_START) * 1000, 2, '.', ',') : '0.00';
                echo '<span class="execution-time-badge" data-server-ms="' . $execMs . '" style="font-size: 0.72rem; font-weight: 500; color: var(--geist-accents-5, #888); white-space: nowrap; display: inline-flex; align-items: center; margin-right: 0.25rem;">' . $execMs . ' ms</span>';
            }
        };

        Hook::addAction('theme.post.single.after_title', $renderBadgeCallback);
        Hook::addAction('theme.product.single.after_title', $renderBadgeCallback);
        Hook::addAction('theme.listing.after_title', $renderBadgeCallback);
        Hook::addAction('theme.wiki.single.after_title', $renderBadgeCallback);
        Hook::addAction('project.single.after_title', $renderBadgeCallback);

        // Register ModelCacheInvalidationObserver for all core content models
        $modelsToObserve = [
            \App\Models\Product::class,
            \App\Models\ProductCategory::class,
            \App\Models\ProductBrand::class,
            \App\Models\ProductTag::class,
            \App\Models\Post::class,
            \App\Models\Category::class,
            \App\Models\Tag::class,
            \App\Models\PostTag::class,
            \App\Models\Page::class,
            \App\Models\Setting::class,
            \App\Models\Menu::class,
            \App\Models\MenuItem::class,
            \App\Models\Language::class,
            \App\Models\Theme::class,
        ];

        foreach ($modelsToObserve as $modelClass) {
            if (class_exists($modelClass)) {
                try {
                    $modelClass::observe(\App\Observers\ModelCacheInvalidationObserver::class);
                } catch (\Throwable $e) {}
            }
        }

        Hook::addAction('content.saved', function ($model) {
            if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    app(\App\Observers\ModelCacheInvalidationObserver::class)->saved($model);
                } catch (\Throwable $e) {}
            }
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

        // Canonical URL — default = current URL (without query string, normalized non-www)
        // Themes/modules can override via Hook::addFilter('seo.canonical_url', ..., priority)
        // Priority chain: Core(default) → Theme(10) → Module/MTOptimize(20+)
        $canonicalUrl = Hook::applyFilters('seo.canonical_url', canonical_url(request()->path()));

        if ($canonicalUrl) {
            echo '<link rel="canonical" href="' . e($canonicalUrl) . '">' . PHP_EOL;
        }

        // Site Favicon
        $siteIconUrl = Hook::applyFilters('seo.site_favicon', $settings->get('site_icon'));
        if ($siteIconUrl) {
            echo '<link rel="icon" type="image/png" href="' . e($siteIconUrl) . '">' . PHP_EOL;
            echo '<link rel="apple-touch-icon" href="' . e($siteIconUrl) . '">' . PHP_EOL;
        }

        // Render localized hreflang links
        $this->renderLocalizedHreflangs();
    }

    /**
     * Render alternate localized hreflang tags in HTML head
     */
    protected function renderLocalizedHreflangs(): void
    {
        $route = request()->route();
        if (!$route) {
            return;
        }

        $routeName = $route->getName();
        $slug = $route->parameter('slug');

        if ($routeName === 'home') {
            try {
                if (Schema::hasTable('languages')) {
                    $activeLanguages = \App\Models\Language::where('is_active', true)->get();
                    foreach ($activeLanguages as $lang) {
                        $url = $lang->is_default ? canonical_url('/') : canonical_url($lang->code);
                        echo '<link rel="alternate" hreflang="' . e($lang->code) . '" href="' . e($url) . '" />' . PHP_EOL;
                    }
                }
            } catch (\Exception $e) {}
            return;
        }

        $entity = null;
        if ($slug) {
            try {
                if (in_array($routeName, ['posts.show', 'pages.show'], true)) {
                    $entity = \App\Models\Post::where('slug', $slug)->first();
                } elseif ($routeName === 'products.show') {
                    $entity = \App\Models\Product::where('slug', $slug)->first();
                } elseif ($routeName === 'categories.show') {
                    $entity = \App\Models\Category::where('slug', $slug)->first();
                } elseif ($routeName === 'product-categories.show') {
                    $entity = \App\Models\ProductCategory::where('slug', $slug)->first();
                } elseif ($routeName === 'product-brands.show') {
                    $entity = \App\Models\ProductBrand::where('slug', $slug)->first();
                }
            } catch (\Exception $e) {}
        }

        if ($entity && isset($entity->translation_group_id)) {
            $groupId = $entity->translation_group_id;
            if ($groupId) {
                try {
                    $translations = $entity->newQuery()
                        ->withoutGlobalScope('locale')
                        ->where('translation_group_id', $groupId)
                        ->get();

                    foreach ($translations as $trans) {
                        $url = $trans->frontend_url;
                        $fullUrl = canonical_url($url);
                        echo '<link rel="alternate" hreflang="' . e($trans->locale) . '" href="' . e($fullUrl) . '" />' . PHP_EOL;
                    }
                } catch (\Exception $e) {}
            }
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

        // Social Links Widget
        $widgetManager->register('social_links', \App\Widgets\SocialLinksWidget::class, [
            'label' => 'Social Links',
            'description' => 'Display social media profile links',
            'category' => 'content',
            'config_schema' => [
                'layout' => [
                    'type' => 'select',
                    'label' => 'Display Layout',
                    'default' => 'list_with_labels',
                    'options' => [
                        ['value' => 'list_with_labels', 'label' => 'List with labels'],
                        ['value' => 'horizontal_icons', 'label' => 'Horizontal circular icons'],
                    ],
                ],
                'social_links_list' => [
                    'type' => 'social_links_editor',
                    'label' => 'Social Links List',
                ],
            ],
        ]);

        // Contact Form Widget
        $formOptions = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('contact_forms')) {
                $formOptions = \App\Models\ContactForm::where('is_active', true)->get()->map(fn($f) => [
                    'value' => (string) $f->id,
                    'label' => $f->name,
                ])->toArray();
            }
        } catch (\Exception $e) {
            // Ignore DB errors
        }

        $widgetManager->register('contact_form', \App\Widgets\ContactFormWidget::class, [
            'label' => 'Contact Form',
            'description' => 'Display a dynamic contact or newsletter form',
            'category' => 'content',
            'config_schema' => [
                'form_id' => [
                    'type' => 'select',
                    'label' => 'Select Form',
                    'default' => '',
                    'options' => $formOptions,
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

        // HTML Editor Widget (Rich-Text via Tiptap)
        $widgetManager->register('html_editor', \App\Widgets\HtmlEditorWidget::class, [
            'label' => 'HTML Editor',
            'description' => 'Add custom content using rich text editor',
            'category' => 'content',
            'config_schema' => [
                'content' => [
                    'type' => 'tiptap',
                    'label' => 'HTML Content',
                    'default' => '',
                ],
            ],
        ]);

        // Heading Widget
        $widgetManager->register('heading', \App\Widgets\HeadingWidget::class, [
            'label' => 'Heading',
            'description' => 'Add custom heading content',
            'category' => 'content',
            'config_schema' => [
                'text' => [
                    'type' => 'text',
                    'label' => 'Heading Text',
                    'default' => '',
                ],
                'tag' => [
                    'type' => 'select',
                    'label' => 'HTML Tag',
                    'default' => 'h3',
                    'options' => [
                        ['value' => 'h1', 'label' => 'H1'],
                        ['value' => 'h2', 'label' => 'H2'],
                        ['value' => 'h3', 'label' => 'H3'],
                        ['value' => 'h4', 'label' => 'H4'],
                        ['value' => 'h5', 'label' => 'H5'],
                        ['value' => 'h6', 'label' => 'H6'],
                        ['value' => 'div', 'label' => 'Div'],
                    ],
                ],
                'color' => [
                    'type' => 'text',
                    'label' => 'Text Color (CSS or Class)',
                    'default' => '',
                ],
                'font_weight' => [
                    'type' => 'select',
                    'label' => 'Font Weight',
                    'default' => 'font-bold',
                    'options' => [
                        ['value' => 'font-normal', 'label' => 'Normal'],
                        ['value' => 'font-semibold', 'label' => 'Semibold'],
                        ['value' => 'font-bold', 'label' => 'Bold'],
                        ['value' => 'font-extrabold', 'label' => 'Extrabold'],
                    ],
                ],
                'alignment' => [
                    'type' => 'select',
                    'label' => 'Alignment',
                    'default' => 'left',
                    'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                        ['value' => 'right', 'label' => 'Right'],
                    ],
                ],
                'custom_class' => [
                    'type' => 'text',
                    'label' => 'Custom CSS Class',
                    'default' => '',
                ],
            ],
        ]);

        // Menu Items Widget
        $widgetManager->register('menu_items', \App\Widgets\MenuItemsWidget::class, [
            'label' => 'Menu',
            'description' => 'Add custom localized link items',
            'category' => 'content',
            'config_schema' => [
                'items' => [
                    'type' => 'menu_items',
                    'label' => 'Menu Items',
                    'default' => [],
                ],
                'layout' => [
                    'type' => 'select',
                    'label' => 'Display Layout',
                    'default' => 'list_with_labels',
                    'options' => [
                        ['value' => 'list_with_labels', 'label' => 'List with labels'],
                        ['value' => 'horizontal', 'label' => 'Horizontal list'],
                    ],
                ],
                'custom_class' => [
                    'type' => 'text',
                    'label' => 'Custom CSS Class',
                    'default' => '',
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
                'show_media' => [
                    'type' => 'boolean',
                    'label' => 'Show product images',
                    'default' => true,
                ],
                'show_price' => [
                    'type' => 'boolean',
                    'label' => 'Show product prices',
                    'default' => true,
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
                'show_label' => [
                    'type' => 'boolean',
                    'label' => 'Show label text',
                    'default' => true,
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
            'category' => 'marketing',
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
            'category' => 'features',
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
            'category' => 'marketing',
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

        // Seed core template parts library
        (new \Database\Seeders\CoreTemplatePartsSeeder($manager))->seed();
        
        // Seed full-page landing templates composed from the parts
        (new \Database\Seeders\CoreTemplatesSeeder($manager))->seed();
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

    /**
     * Configure cache drivers and Redis settings at runtime.
     */
    protected function configureCache(): void
    {
        if (!file_exists(storage_path('installed.lock')) || !\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            return;
        }

        try {
            $settings = app(\App\Services\SettingsService::class);

            // 1. Get cache/session/queue configuration
            $cacheStore = $settings->get('cache_store', config('cache.default', 'file'));
            $sessionDriver = $settings->get('session_driver', config('session.driver', 'file'));
            $queueConnection = $settings->get('queue_connection', config('queue.default', 'sync'));

            // 2. Get Redis configuration
            $redisHost = $settings->get('redis_host', '127.0.0.1');
            $redisPort = $settings->get('redis_port', 6379);
            $redisPassword = $settings->get('redis_password', null);
            $redisCacheDb = $settings->get('redis_cache_db', 1);
            $redisCachePrefix = $settings->get('redis_cache_prefix', null);

            // 3. Override Redis config if specified
            if ($redisHost) {
                config(['database.redis.default.host' => $redisHost]);
                config(['database.redis.cache.host' => $redisHost]);
            }
            if ($redisPort) {
                config(['database.redis.default.port' => (int) $redisPort]);
                config(['database.redis.cache.port' => (int) $redisPort]);
            }
            if ($redisPassword !== null) {
                config(['database.redis.default.password' => $redisPassword]);
                config(['database.redis.cache.password' => $redisPassword]);
            }
            if ($redisCacheDb !== null) {
                config(['database.redis.cache.database' => (int) $redisCacheDb]);
            }
            if ($redisCachePrefix !== null && $redisCachePrefix !== '') {
                config(['cache.prefix' => $redisCachePrefix]);
            }

            // 4. Guard Redis drivers if extension/server unavailable
            $hasPhpRedis = extension_loaded('redis') || class_exists(\Redis::class);
            $hasPredis = class_exists(\Predis\Client::class);

            if ($cacheStore === 'redis' && !$hasPhpRedis && !$hasPredis) {
                $cacheStore = 'file';
            }
            if ($sessionDriver === 'redis' && !$hasPhpRedis && !$hasPredis) {
                $sessionDriver = 'file';
            }

            if (($cacheStore === 'redis' || $sessionDriver === 'redis') && $hasPhpRedis) {
                try {
                    \Illuminate\Support\Facades\Redis::connection()->ping();
                } catch (\Throwable $ex) {
                    if ($cacheStore === 'redis') $cacheStore = 'file';
                    if ($sessionDriver === 'redis') $sessionDriver = 'file';
                    \Illuminate\Support\Facades\Log::warning('PolyCMS Redis Guard: Cannot connect to Redis server in configureCache(). Falling back to File: ' . $ex->getMessage());
                }
            }

            // 5. Override cache/session/queue config
            config(['cache.default' => $cacheStore]);
            config(['session.driver' => $sessionDriver]);
            config(['queue.default' => $queueConnection]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Cache runtime configuration override failed: " . $e->getMessage());
        }
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

    /**
     * Register core atomic landing block renderers (Button, Heading, Text, etc.)
     */
    protected function registerCoreLandingBlocks(): void
    {
        $blocks = [
            'heading', 'text', 'button', 'image', 'spacer',
            'html_block', 'divider', 'row', 'section',
            'hero_section', 'what_you_get', 'cta_section', 'showcase',
            'testimonial', 'icon_box', 'counter',
            'products_slider', 'products_showcase', 'products',
        ];

        foreach ($blocks as $block) {
            Hook::addFilter("content.render.landing_block.{$block}", function($html, $attrs, $context = [], $renderer = null) use ($block) {
                $viewName = $block === 'html_block' ? 'html' : $block;
                
                // Pre-render children if 'blocks' is present and we have a renderer
                $children = '';
                if (!empty($attrs['blocks']) && $renderer) {
                    $children = $renderer->render($attrs['blocks']);
                }

                return view("core.blocks.{$viewName}", [
                    'attrs' => $attrs,
                    'context' => $context,
                    'renderer' => $renderer,
                    'children' => $children,
                ])->render();
            }, 5, 4); // Priority 5, accept 4 arguments
        }

        // Global Landing Block Post-Renderer (handles margin, padding, breakout)
        Hook::addFilter('content.render.landing_block.post', function($html, $type, $attrs) {
            $classes = [];
            $innerStyles = [];
            $isViewportFullWidth = !empty($attrs['viewport_full_width']);

            // Parse animation configs if present
            if (!empty($attrs['animation_type']) && $attrs['animation_type'] !== 'none') {
                $classes[] = 'poly-animate';
                $classes[] = 'poly-anim-' . $attrs['animation_type'];
                
                // PolyCMS core animations CSS custom properties
                if (!empty($attrs['animation_duration'])) {
                    $innerStyles[] = "--poly-anim-duration: {$attrs['animation_duration']}ms";
                }
                if (!empty($attrs['animation_delay'])) {
                    $innerStyles[] = "--poly-anim-delay: {$attrs['animation_delay']}ms";
                }
            }

            if ($isViewportFullWidth) {
                $classes[] = 'section-full-viewport';
            }

            if (!empty($attrs['margin'])) {
                $innerStyles[] = "margin: {$attrs['margin']}";
            }
            if (!empty($attrs['padding'])) {
                $innerStyles[] = "padding: {$attrs['padding']}";
            }

            if (empty($classes) && empty($innerStyles)) {
                return $html;
            }

            $classAttr = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
            $innerStyleAttr = !empty($innerStyles) ? ' style="' . implode('; ', $innerStyles) . '"' : '';

            if ($isViewportFullWidth) {
                return "<div{$classAttr}><div{$innerStyleAttr}>{$html}</div></div>";
            }

            return "<div{$classAttr}{$innerStyleAttr}>{$html}</div>";
        }, 5, 3);
    }

    /**
     * Ensure file cache directories exist and are writable.
     *
     * When `php artisan` commands run as root, the cache directories
     * (storage/framework/cache/data/xx/yy/) get re-created with root:root
     * ownership. This makes the web server user unable to write cache files,
     * crashing the entire application.
     *
     * This method runs early in boot() and:
     *   1. Creates the cache data directory if missing
     *   2. Tries to chmod it to 0775 if it's not writable
     *   3. Silently fails — the ResilientCache wrapper handles the fallback
     */
    private function ensureCacheDirectoriesWritable(): void
    {
        // Only relevant on Linux/Mac production servers
        if (PHP_OS_FAMILY === 'Windows') {
            return;
        }

        $dirs = [
            storage_path('framework/cache/data'),
            storage_path('framework/views'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $dir) {
            try {
                if (!is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                    continue;
                }

                if (!is_writable($dir)) {
                    @chmod($dir, 0775);
                }
            } catch (\Throwable) {
                // Silently continue — ResilientCache handles the fallback
            }
        }
    }
}
