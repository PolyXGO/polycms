<?php

declare(strict_types=1);

namespace Modules\Polyx\SampleModule;

use App\Facades\Hook;
use App\Services\MenuRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║             POLYCMS — SAMPLE MODULE SERVICE PROVIDER            ║
 * ╠══════════════════════════════════════════════════════════════════╣
 * ║  This is the MAIN entry point for a PolyCMS module.             ║
 * ║  Everything starts here: hooks, filters, routes, menus, etc.    ║
 * ║                                                                  ║
 * ║  HOW MODULES WORK:                                               ║
 * ║  1. ModuleManager scans modules/ directory for module.json       ║
 * ║  2. If module is enabled (config/modules.php), its provider      ║
 * ║     class (this file) is registered with Laravel's container     ║
 * ║  3. register() → bind services to container                      ║
 * ║  4. boot() → register hooks, routes, migrations, views          ║
 * ╚══════════════════════════════════════════════════════════════════╝
 *
 * HOOKS vs FILTERS:
 * ─────────────────
 * • Action Hook (doAction/addAction):
 *   Fire-and-forget. Used for side effects: logging, sending emails,
 *   syncing data. Your callback receives args but returns nothing.
 *
 * • Filter Hook (applyFilters/addFilter):
 *   Value transformer. Your callback receives a value (+ extra args),
 *   modifies it, and MUST return the modified value.
 *
 * PRIORITY:
 * ─────────
 * Lower number = runs first. Default is 10.
 * Use 1-9 for early execution, 11-99 for late execution.
 */
class SampleModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services into Laravel's service container.
     *
     * Use this for:
     * - Binding interfaces to implementations
     * - Registering singleton services
     * - Merging configuration files
     *
     * Do NOT use for hooks/routes (use boot() instead).
     */
    public function register(): void
    {
        // Example: Merge module config with app config
        // $this->mergeConfigFrom(__DIR__ . '/../config/sample-module.php', 'sample-module');
    }

    /**
     * Bootstrap module services.
     *
     * This is where ALL the magic happens:
     * - Register hooks & filters
     * - Load migrations, views, routes
     * - Register admin menu items
     * - Set up permissions, email templates, etc.
     */
    public function boot(): void
    {
        // ┌──────────────────────────────────────────────────────┐
        // │ STEP 1: Load module infrastructure                   │
        // └──────────────────────────────────────────────────────┘
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sample-module');

        // Auto-run migrations if module tables don't exist yet
        if (file_exists(storage_path('installed.lock'))) {
            $this->runMigrationsIfNeeded();
        }

        // ┌──────────────────────────────────────────────────────┐
        // │ STEP 2: Register hooks & filters                     │
        // └──────────────────────────────────────────────────────┘
        $this->registerAdminMenu();
        $this->registerPermissions();
        $this->registerContentHooks();
        $this->registerMediaHooks();
        $this->registerEcommerceHooks();
        $this->registerWidgetAreas();
        $this->registerEmailTemplates();
        $this->registerLayoutAssets();
        $this->registerSettingsDefaults();
        $this->registerTopbarMenu();
        $this->registerThemeDataInjection();
        $this->registerSeoHooks();

        // ┌──────────────────────────────────────────────────────┐
        // │ STEP 3: Register routes                              │
        // └──────────────────────────────────────────────────────┘
        $this->loadRoutes();
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                    ADMIN MENU REGISTRATION                  ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register admin sidebar menu items.
     *
     * Hook: admin.menu.build (Action)
     * Fired in: AdminMenuController when building the sidebar
     *
     * The MenuRegistry accepts:
     * - key: unique identifier (used for active state)
     * - label: display text
     * - icon: SVG path data (Heroicons format)
     * - order: sort position (lower = higher in sidebar)
     * - children: array of sub-menu items with route names
     */
    protected function registerAdminMenu(): void
    {
        Hook::addAction('admin.menu.build', function () {
            $menuRegistry = app(MenuRegistry::class);

            $menuRegistry->register('sample-module', [
                'key'      => 'sample-module',
                'label'    => 'Sample Module',
                'icon'     => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                'order'    => 100,
                'children' => [
                    [
                        'key'   => 'sample-module-dashboard',
                        'label' => 'Dashboard',
                        'route' => 'admin.sample-module.dashboard',
                        'icon'  => null,
                        'order' => 0,
                    ],
                    [
                        'key'   => 'sample-module-notes',
                        'label' => 'Notes (CRUD)',
                        'route' => 'admin.sample-module.notes',
                        'icon'  => null,
                        'order' => 1,
                    ],
                    [
                        'key'   => 'sample-module-settings',
                        'label' => 'Settings',
                        'route' => 'admin.sample-module.settings',
                        'icon'  => null,
                        'order' => 2,
                    ],
                ],
            ]);
        }, 10); // priority 10 (default)
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                   PERMISSION REGISTRATION                   ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register custom permissions for this module.
     *
     * Hook: roles.register_permissions (Action)
     * Fired in: AppServiceProvider during bootstrap
     *
     * Permissions are checked via: $user->hasPermission('sample_module.manage')
     * They appear in the admin Roles & Permissions page for assignment.
     */
    protected function registerPermissions(): void
    {
        Hook::addAction('roles.register_permissions', function ($permissionRegistry) {
            $permissionRegistry->register('sample_module', [
                [
                    'key'         => 'sample_module.manage',
                    'label'       => 'Manage Sample Module',
                    'description' => 'Full access to Sample Module settings and notes',
                ],
                [
                    'key'         => 'sample_module.notes.view',
                    'label'       => 'View Notes',
                    'description' => 'Can view notes list and details',
                ],
                [
                    'key'         => 'sample_module.notes.create',
                    'label'       => 'Create Notes',
                    'description' => 'Can create new notes',
                ],
                [
                    'key'         => 'sample_module.notes.delete',
                    'label'       => 'Delete Notes',
                    'description' => 'Can delete notes',
                ],
            ]);
        }, 10);
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                      CONTENT HOOKS                          ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register hooks that modify content rendering.
     *
     * These hooks allow modules to inject, modify, or transform
     * post/page content before it reaches the user.
     */
    protected function registerContentHooks(): void
    {
        /**
         * Filter: post.content.render
         * Fired in: PostResource when serializing post for API response
         *
         * Modify the rendered HTML content of a post.
         * This does NOT change stored content — only the output.
         *
         * @param string $content  The rendered HTML content
         * @param Post   $post     The post model instance
         * @return string          Modified HTML content
         */
        Hook::addFilter('post.content.render', function (string $content, $post): string {
            // Get module setting (from real database via SettingsService)
            $settings = app(\App\Services\SettingsService::class);
            $enabled = $settings->get('sample_module_content_badge', 'no');

            if ($enabled !== 'yes') {
                return $content;
            }

            // Add a subtle "reading time" badge after the first heading
            $wordCount = str_word_count(strip_tags($content));
            $readingTime = max(1, (int) ceil($wordCount / 200));

            $badge = '<div class="sample-module-reading-time" style="'
                . 'display:inline-block;padding:4px 12px;margin:8px 0 16px;'
                . 'background:#eef2ff;color:#4f46e5;border-radius:9999px;font-size:13px;'
                . '">'
                . '<svg style="width:16px;height:16px;display:inline-block;vertical-align:middle;margin-right:4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>'
                . " {$readingTime} min read · {$wordCount} words"
                . '</div>';

            // Insert after first heading, or prepend
            if (preg_match('/<h[1-3][^>]*>.*?<\/h[1-3]>/is', $content, $matches)) {
                $content = str_replace($matches[0], $matches[0] . $badge, $content);
            } else {
                $content = $badge . $content;
            }

            return $content;
        }, 10, 2);

        /**
         * DEMO: Filter: content.render.blocks
         * Fired in: ContentRenderer before processing blocks
         *
         * Modify the block array before it's rendered to HTML.
         * Useful for injecting blocks, reordering, or removing blocks.
         *
         * @param array $blocks  Array of content blocks
         * @return array         Modified blocks array
         *
         * Wrapped in debug check to avoid affecting production.
         */
        if (config('app.debug')) {
            Hook::addFilter('content.render.blocks', function (array $blocks): array {
                // DEMO: Log block types for debugging (only in debug mode)
                $types = array_map(fn($b) => $b['type'] ?? 'unknown', $blocks);
                \Log::debug('[SampleModule] Content blocks being rendered', ['types' => $types]);

                return $blocks; // Always return the value in a filter!
            }, 99); // priority 99 = runs after other filters
        }
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                       MEDIA HOOKS                           ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register hooks for media upload/management events.
     */
    protected function registerMediaHooks(): void
    {
        /**
         * Action: media.uploaded
         * Fired in: MediaService after a file is successfully uploaded
         *
         * Use cases: auto-tag, compress, sync to CDN, log, etc.
         *
         * @param Media              $media  The created media record
         * @param UploadedFile       $file   The uploaded file
         * @param array              $data   Upload metadata
         */
        Hook::addAction('media.uploaded', function ($media, $file, $data) {
            // DEMO: Log media uploads (useful for analytics)
            if (config('app.debug')) {
                \Log::info('[SampleModule] Media uploaded', [
                    'id'        => $media->id,
                    'filename'  => $media->filename,
                    'mime_type' => $media->mime_type,
                    'size'      => $media->size,
                ]);
            }
        }, 20, 3); // priority 20, accepts 3 arguments

        /**
         * DEMO: Filter: media.url
         * Fired in: MediaService::getUrl() when generating media URLs
         *
         * Modify the URL of a media file. Common use: CDN URL rewriting.
         *
         * @param string $url    The original media URL
         * @param Media  $media  The media model
         * @return string        Modified URL
         */
        // DEMO: Uncomment to enable CDN URL rewriting
        // Hook::addFilter('media.url', function (string $url, $media): string {
        //     $cdnDomain = config('sample-module.cdn_domain');
        //     if ($cdnDomain) {
        //         return str_replace(config('app.url'), $cdnDomain, $url);
        //     }
        //     return $url;
        // }, 10, 2);
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                    E-COMMERCE HOOKS                         ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register hooks for e-commerce events.
     *
     * These hooks fire during cart, product, and order lifecycle events.
     * Use them for: analytics tracking, inventory sync, notifications, etc.
     */
    protected function registerEcommerceHooks(): void
    {
        /**
         * Action: cart.item.added
         * Fired in: CartService after an item is added to cart
         *
         * @param CartItem $item  The cart item that was added
         * @param Cart     $cart  The cart model
         */
        Hook::addAction('cart.item.added', function ($item, $cart) {
            if (config('app.debug')) {
                \Log::info('[SampleModule] Cart item added', [
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'cart_total' => $cart->items()->count(),
                ]);
            }
        }, 20, 2);

        /**
         * Action: product.saved
         * Fired in: CreateProduct / UpdateProduct after product is persisted
         *
         * Runs on both create and update. Use $product->wasRecentlyCreated
         * to distinguish between the two.
         *
         * @param Product $product  The saved product model
         */
        Hook::addAction('product.saved', function ($product) {
            if (config('app.debug')) {
                $action = $product->wasRecentlyCreated ? 'created' : 'updated';
                \Log::info("[SampleModule] Product {$action}", [
                    'id'   => $product->id,
                    'name' => $product->name,
                    'sku'  => $product->sku ?? 'N/A',
                ]);
            }
        }, 20);

        /**
         * Action: post.saved
         * Fired in: CreatePost / UpdatePost after post is persisted
         *
         * @param Post $post  The saved post model
         */
        Hook::addAction('post.saved', function ($post) {
            if (config('app.debug')) {
                \Log::info('[SampleModule] Post saved', [
                    'id'     => $post->id,
                    'title'  => $post->title,
                    'status' => $post->status,
                ]);
            }
        }, 20);

        /**
         * DEMO: Filter: cart.totals
         * Fired in: CartService::calculateTotals()
         *
         * Modify cart totals (subtotal, tax, shipping, grand_total).
         * Use cases: loyalty discounts, membership pricing, surcharges.
         *
         * @param array $totals  ['subtotal' => float, 'tax' => float, ...]
         * @param Cart  $cart    The cart model
         * @return array         Modified totals
         */
        // DEMO: Uncomment to add a $5 handling fee
        // Hook::addFilter('cart.totals', function (array $totals, $cart): array {
        //     $totals['handling_fee'] = 5.00;
        //     $totals['grand_total'] = ($totals['grand_total'] ?? 0) + 5.00;
        //     return $totals;
        // }, 10, 2);
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                    WIDGET REGISTRATION                      ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register widget areas provided by this module.
     *
     * Hook: widgets.register_areas (Action)
     * Fired in: AppServiceProvider during bootstrap
     *
     * Widget areas are regions in the frontend theme where users can
     * drag & drop widgets via the admin panel.
     *
     * @param WidgetManager $widgets  The widget manager service
     */
    protected function registerWidgetAreas(): void
    {
        Hook::addAction('widgets.register_areas', function ($widgets): void {
            // Modules can register their own widget areas
            // These appear in admin → Appearance → Widgets
            $widgets->registerArea('sample_module_sidebar', [
                'name'        => 'Sample Module Sidebar',
                'description' => 'A widget area registered by the Sample Module for demonstration.',
                'order'       => 500, // High order = appears later in admin
            ]);
        }, 10);
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                  EMAIL TEMPLATE REGISTRATION                 ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register email templates for this module.
     *
     * Hook: register_email_templates (Action)
     * Fired in: AppServiceProvider during bootstrap
     *
     * Email templates are editable in admin → Settings → Email Templates.
     * Users can customize the subject and body with merge tags.
     *
     * @param EmailTemplateManager $manager
     */
    protected function registerEmailTemplates(): void
    {
        Hook::addAction('register_email_templates', function ($manager) {
            $manager->register('sample_module_note_created', [
                'name'        => 'Sample Module: Note Created',
                'description' => 'Sent when a new note is created via Sample Module',
                'subject'     => 'New Note Created: {note_title}',
                'body'        => '<p>A new note was created:</p><p><strong>{note_title}</strong></p><p>{note_content}</p>',
                'merge_tags'  => [
                    '{note_title}'   => 'The title of the note',
                    '{note_content}' => 'The content of the note',
                    '{created_by}'   => 'The user who created the note',
                ],
            ]);
        }, 10);
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                   LAYOUT ASSETS REGISTRATION                ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register CSS/JS assets to be loaded in the frontend layout.
     *
     * Hook: layout.register_assets (Action)
     * Fired in: AppServiceProvider during bootstrap
     *
     * Use this to inject your module's styles/scripts into the
     * frontend theme layout without modifying theme files.
     *
     * @param LayoutAssetManager $assetManager
     */
    protected function registerLayoutAssets(): void
    {
        Hook::addAction('layout.register_assets', function ($assetManager) {
            // DEMO: Register a small CSS snippet for the reading-time badge
            // In production, you'd register a CSS/JS file URL instead
            $assetManager->addInlineStyle(
                'sample-module-badge',
                '.sample-module-reading-time { transition: opacity 0.3s; }' .
                '.sample-module-reading-time:hover { opacity: 0.8; }'
            );
        }, 10);
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                  SETTINGS DEFAULTS                          ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register default settings values for this module.
     *
     * Filter: settings.defaults
     * Fired in: SettingsService when building default settings
     *
     * This ensures your module's settings have sensible defaults
     * even before the user configures anything.
     *
     * @param array            $defaults  Current defaults array
     * @param SettingsService  $service   The settings service
     * @return array                      Modified defaults
     */
    protected function registerSettingsDefaults(): void
    {
        Hook::addFilter('settings.defaults', function (array $defaults, $service): array {
            $defaults['sample_module'] = [
                'sample_module_content_badge' => [
                    'key'         => 'sample_module_content_badge',
                    'value'       => 'no',
                    'type'        => 'string',
                    'label'       => 'Show Reading Time Badge',
                    'description' => 'Adds a "X min read" badge after the first heading in post content.',
                ],
                'sample_module_notes_per_page' => [
                    'key'         => 'sample_module_notes_per_page',
                    'value'       => '10',
                    'type'        => 'number',
                    'label'       => 'Notes Per Page',
                    'description' => 'Pagination size for the Notes list.',
                ],
            ];
            return $defaults;
        }, 10, 2);
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                   TOPBAR MENU ITEMS                         ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Add items to the admin topbar menu.
     *
     * Filter: topbar.menu.items
     * Fired in: TopbarMenuService when building the topbar
     *
     * The topbar is the horizontal bar at the top of the admin panel.
     *
     * @param array   $items    Current menu items
     * @param Request $request  The current request
     * @param User    $user     The authenticated user
     * @return array            Modified menu items
     */
    protected function registerTopbarMenu(): void
    {
        // DEMO: Only add topbar item in debug mode
        if (config('app.debug')) {
            Hook::addFilter('topbar.menu.items', function (array $items, $request, $user): array {
                $items[] = [
                    'key'   => 'sample-module-debug',
                    'label' => 'Sample Module',
                    'url'   => '/admin/sample-module/dashboard',
                    'order' => 999,
                ];
                return $items;
            }, 10, 3);
        }
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                 THEME VIEW DATA INJECTION                   ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Inject additional data into frontend theme views.
     *
     * Filter: theme.view.data
     * Fired in: All frontend controllers (PostController, PageController, etc.)
     *
     * This is how modules pass data to Blade templates without
     * modifying core controllers. The data is available as variables
     * in ALL theme views.
     *
     * @param array  $data      Current view data
     * @param string $viewName  The view being rendered (e.g., 'posts.show')
     * @return array            Modified view data
     */
    protected function registerThemeDataInjection(): void
    {
        Hook::addFilter('theme.view.data', function (array $data, string $viewName): array {
            // Inject module data into theme views
            $data['sample_module_version'] = '2.0.0';

            // Example: inject note count on homepage
            if ($viewName === 'home' && Schema::hasTable('sample_notes')) {
                $data['sample_module_note_count'] = Models\SampleNote::count();
            }

            return $data;
        }, 20, 2); // priority 20 = after theme's own data injection
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                       SEO HOOKS                             ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register SEO-related hooks.
     */
    protected function registerSeoHooks(): void
    {
        /**
         * DEMO: Filter: seo.canonical_url
         * Fired in: AppServiceProvider when generating <link rel="canonical">
         *
         * Modify the canonical URL of any page. Useful for:
         * - Removing query params from canonical
         * - Redirecting to preferred domain
         * - Handling pagination canonical URLs
         *
         * @param string $url  The current canonical URL
         * @return string      Modified canonical URL
         */
        // DEMO: Uncomment to force HTTPS on canonical URLs
        // Hook::addFilter('seo.canonical_url', function (string $url): string {
        //     return str_replace('http://', 'https://', $url);
        // }, 5); // priority 5 = runs early
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                       ROUTES                                ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Register module routes.
     *
     * PolyCMS modules register their own API routes here.
     * Convention: /api/v1/{module-slug}/...
     *
     * Available middleware:
     * - 'api' → stateless, JSON responses
     * - 'auth:sanctum' → requires authentication token
     * - 'web' → stateful, sessions, CSRF
     */
    protected function loadRoutes(): void
    {
        // Register API routes (for Vue admin panel)
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1/sample-module')
            ->name('api.v1.sample-module.')
            ->group(function () {
                // Settings endpoints
                Route::get('/settings', [Controllers\Api\V1\SettingsController::class, 'index'])
                    ->name('settings.index');
                Route::put('/settings', [Controllers\Api\V1\SettingsController::class, 'update'])
                    ->name('settings.update');

                // Notes CRUD endpoints
                Route::apiResource('notes', Controllers\Api\V1\NoteController::class);
            });
    }

    // ╔══════════════════════════════════════════════════════════════╗
    // ║                  DATABASE MIGRATIONS                        ║
    // ╚══════════════════════════════════════════════════════════════╝

    /**
     * Auto-run migrations if module table doesn't exist.
     *
     * This pattern ensures the module works immediately after activation
     * without requiring manual `php artisan migrate`.
     *
     * The check for `installed.lock` prevents migrations from running
     * during the initial system installation process.
     */
    protected function runMigrationsIfNeeded(): void
    {
        if (!Schema::hasTable('migrations') || Schema::hasTable('sample_notes')) {
            return;
        }

        try {
            $migrationPath = str_replace(
                base_path() . DIRECTORY_SEPARATOR,
                '',
                __DIR__ . '/database/migrations'
            );

            Artisan::call('migrate', [
                '--path'  => $migrationPath,
                '--force' => true,
            ]);
        } catch (\Exception $e) {
            \Log::warning('[SampleModule] Auto-migration failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
