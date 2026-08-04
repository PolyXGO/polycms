<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/**
 * Topbar Menu Service - WordPress-like admin bar
 *
 * Manages the topbar menu items that appear on both frontend and admin
 * with extensibility through hooks.
 */
class TopbarMenuService
{
    /**
     * Get menu items for the topbar
     *
     * @param Request|null $request Current request
     * @return array Array of menu items
     */
    public function getMenuItems(?Request $request = null): array
    {
        $request = $request ?? request();

        // Check request user first (works for Sanctum and all auth guards)
        $user = $request->user();

        // Check web session auth (for frontend)
        if (!$user) {
            $user = Auth::guard('web')->user();
        }

        // If no web session, check Sanctum token (for API/admin)
        if (!$user) {
            $user = Auth::guard('sanctum')->user();
        }

        // If still no user, try default guard
        if (!$user) {
            $user = Auth::user();
        }

        // If user is not authenticated, return empty array
        if (!$user) {
            return [];
        }

        $items = [];

        // Core menu items
        $items = $this->addCoreMenuItems($items, $request, $user);

        // Context-aware edit links
        $items = $this->addContextEditLinks($items, $request, $user);

        // Add Template Switcher link
        $items = $this->addTemplateSwitcherLink($items, $request, $user);

        // Allow modules/themes to add items via hook
        $items = Hook::applyFilters('topbar.menu.items', $items, $request, $user);

        // Sort by priority
        usort($items, fn($a, $b) => ($a['priority'] ?? 10) <=> ($b['priority'] ?? 10));

        return $items;
    }

    /**
     * Add core menu items
     *
     * @param array $items Current items
     * @param Request $request Current request
     * @param \App\Models\User $user Current user
     * @return array
     */
    protected function addCoreMenuItems(array $items, Request $request, $user): array
    {
        $baseUrl = url('/');
        $siteName = app(\App\Services\SettingsService::class)->get('brand_name', 'PolyCMS');

        // Check current context to avoid redundant links
        $currentPath = trim($request->path(), '/');
        // If we are on frontend home, currentPath is empty
        $isHome = $currentPath === '' || $currentPath === 'home';
        // Check if we are in admin or account
        $isAdmin = str_starts_with($currentPath, 'admin');
        $isAccount = str_starts_with($currentPath, 'account');

        // Site home link - hide if already on home page
        if (!$isHome) {
            $items[] = [
                'id' => 'visit-site',
                'label' => $siteName,
                'url' => $baseUrl,
                'icon' => 'home',
                'priority' => 5,
                'group' => 'left',
            ];
        }

        // Admin dashboard link - only for admin/editor
        // Hide if already in admin or account area
        $routeName = $request->get('route') ?? Route::currentRouteName();
        $isHomeRoute = $routeName === 'home';
        $isAdminRoute = str_starts_with((string)$routeName, 'admin.');
        $isAccountRoute = str_starts_with((string)$routeName, 'profile.') || str_starts_with((string)$routeName, 'account.');

        if ($user->hasRole(['admin', 'editor', 'author'])) {
            if (!$isAdmin && !$isAccount && !$isAdminRoute && !$isAccountRoute) {
                $items[] = [
                    'id' => 'admin-dashboard',
                    'label' => _l('Dashboard'),
                    'url' => $baseUrl . '/admin',
                    'icon' => 'dashboard',
                    'priority' => 10,
                    'group' => 'left',
                ];
            }
        } else {
            // For customers, we can add a 'Shop' link or other customer-specific link here if needed
        }

        // New content dropdown
        $newItems = [];

        if ($user->can('create', \App\Models\Post::class)) {
            $newItems[] = [
                'id' => 'new-post',
                'label' => _l('Post'),
                'url' => $baseUrl . '/admin/posts/create',
                'icon' => 'document',
            ];
        }

        if ($user->can('create', \App\Models\Post::class)) {
            $newItems[] = [
                'id' => 'new-page',
                'label' => _l('Page'),
                'url' => $baseUrl . '/admin/posts/create?type=page',
                'icon' => 'page',
                'priority' => 20,
            ];
        }

        if ($user->can('create', \App\Models\Product::class)) {
            $newItems[] = [
                'id' => 'new-product',
                'label' => _l('Product'),
                'url' => $baseUrl . '/admin/products/create',
                'icon' => 'product',
            ];
        }

        if ($user->can('create', \App\Models\Category::class)) {
            $newItems[] = [
                'id' => 'new-product-category',
                'label' => _l('Product Category'),
                'url' => $baseUrl . '/admin/product-categories/create',
                'icon' => 'category',
                'priority' => 30,
            ];
        }

        if ($user->can('create', \App\Models\ProductBrand::class)) {
            $newItems[] = [
                'id' => 'new-product-brand',
                'label' => _l('Product Brand'),
                'url' => $baseUrl . '/admin/product-brands/create',
                'icon' => 'brand',
                'priority' => 40,
            ];
        }

        if (!empty($newItems)) {
            $items[] = [
                'id' => 'new',
                'label' => _l('New'),
                'icon' => 'plus',
                'priority' => 20,
                'group' => 'left',
                'children' => $newItems,
            ];
        }

        // Site customization
        if ($user->hasRole(['admin', 'editor'])) {
            $items[] = [
                'id' => 'customize',
                'label' => _l('Customize'),
                'url' => $baseUrl . '/admin/themes',
                'icon' => 'paint-brush',
                'priority' => 30,
                'group' => 'left',
                'children' => [
                    [
                        'id' => 'customize-themes',
                        'label' => _l('Customize / Themes'),
                        'url' => $baseUrl . '/admin/themes',
                        'icon' => 'paint-brush',
                    ],
                    [
                        'id' => 'customize-menus',
                        'label' => _l('Menus'),
                        'url' => $baseUrl . '/admin/menus',
                        'icon' => 'menu',
                    ],
                    [
                        'id' => 'customize-appearance',
                        'label' => _l('Admin Appearance'),
                        'url' => $baseUrl . '/admin/settings/admin_appearance',
                        'icon' => 'eye',
                    ],
                    [
                        'id' => 'customize-modules',
                        'label' => _l('Modules'),
                        'url' => $baseUrl . '/admin/modules',
                        'icon' => 'puzzle',
                    ],
                    [
                        'id' => 'customize-settings',
                        'label' => _l('Settings Hub'),
                        'url' => $baseUrl . '/admin/settings',
                        'icon' => 'settings',
                    ],
                ]
            ];
        }

        // Active theme options
        if ($user->hasRole(['admin', 'editor'])) {
            $themeManager = app(\App\Services\ThemeManager::class);
            $activeTheme = $themeManager->getMainTheme() ?? $themeManager->getActiveTheme('frontend');
            if ($activeTheme) {
                $items[] = [
                    'id' => 'theme-options',
                    'label' => _l('Theme: :name', [':name' => $activeTheme->name]),
                    'url' => $baseUrl . '/admin/themes/options',
                    'icon' => 'SwatchIcon',
                    'priority' => 31,
                    'group' => 'left',
                ];
            }
        }

        // User menu
        $userItems = [
            [
                'id' => 'profile',
                'label' => _l('Profile'),
                'url' => url('/account/profile'),
                'icon' => 'user',
            ],
        ];

        if ($user->hasRole(['admin', 'editor', 'author'])) {
            $userItems[] = [
                'id' => 'admin-profile',
                'label' => _l('Admin Profile'),
                'url' => url('/admin/profile'),
                'icon' => 'user-circle',
            ];
        }

        $userItems = array_merge($userItems, [
            [
                'id' => 'my-orders',
                'label' => _l('My Orders'),
                'url' => url('/account/orders'),
                'icon' => 'shopping-cart',
            ],
            [
                'id' => 'my-subscriptions',
                'label' => _l('My Subscriptions'),
                'url' => url('/account/subscriptions'),
                'icon' => 'refresh',
            ],
            [
                'id' => 'my-licenses',
                'label' => _l('My Licenses'),
                'url' => url('/account/licenses'),
                'icon' => 'key',
            ],
            [
                'id' => 'logout',
                'label' => _l('Log Out'),
                'url' => route('logout'),
                'icon' => 'logout',
                'method' => 'POST',
            ],
        ]);

        // Allow modules to inject items into the user dropdown menu.
        // Hook: 'topbar.user_menu.items' — filters the $userItems array.
        // Modules should insert items with 'priority' key for ordering.
        // Items without 'priority' default to 50. Logout is always last.
        $userItems = Hook::applyFilters('topbar.user_menu.items', $userItems, $user);

        $items[] = [
            'id' => 'user-menu',
            'label' => $user->name ?: ($user->email ?: 'User'),
            'url' => url('/account/profile'),
            'icon' => 'user-circle',
            'priority' => 100,
            'group' => 'right',
            'children' => $userItems,
        ];

        return $items;
    }

    /**
     * Add context-aware edit links based on current page
     *
     * @param array $items Current items
     * @param Request $request Current request
     * @param \App\Models\User $user Current user
     * @return array
     */
    protected function addContextEditLinks(array $items, Request $request, $user): array
    {
        // Get route name from request parameter (from frontend) or current route
        $routeName = $request->get('route') ?? Route::currentRouteName();
        $routeParams = $request->get('route_params', []);

        // If route params are provided as JSON string, decode them
        if (is_string($routeParams)) {
            $routeParams = json_decode($routeParams, true) ?? [];
        }

        // If route params are provided, merge them into request for route() method
        if (is_array($routeParams) && !empty($routeParams)) {
            foreach ($routeParams as $key => $value) {
                $request->merge([$key => $value]);
            }
        }

        $currentUrl = $request->url();

        // Get slug from route params (from frontend) or route() method
        $slug = null;
        if (is_array($routeParams) && isset($routeParams['slug'])) {
            $slug = $routeParams['slug'];
        } elseif (is_array($routeParams) && isset($routeParams['postSlug'])) {
            $slug = $routeParams['postSlug'];
        } else {
            $slug = $request->route('slug') ?? $request->get('slug') ?? $request->route('postSlug') ?? $request->get('postSlug');
        }

        // Debug logging (remove in production if needed)
        if (config('app.debug')) {
            Log::debug('TopbarMenuService::addContextEditLinks', [
                'routeName' => $routeName,
                'routeParams' => $routeParams,
                'slug' => $slug,
                'currentUrl' => $currentUrl,
            ]);
        }

        $appendEditPageItem = function ($page) use (&$items, $user) {
            if (!$page || !($user->hasRole(['admin', 'editor']) || $user->can('update', $page))) {
                return;
            }

            $items[] = [
                'id' => 'edit-page',
                'label' => _l('Edit Page'),
                'url' => url('/admin/pages/' . $page->id . '/edit'),
                'icon' => 'pencil',
                'priority' => 35, // After Customize (30)
                'group' => 'left',
                'highlight' => true,
            ];
        };

        // Check if we're on a post detail page
        if (($routeName === 'posts.show' || $routeName === 'theme.flexidocs.show' || $routeName === 'theme.flexidocs.category') && $slug) {
            $currentLocale = app()->getLocale();
            $post = \App\Models\Post::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('type', 'post')
                ->where('locale', $currentLocale)
                ->first();

            if (!$post) {
                $post = \App\Models\Post::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->where('type', 'post')
                    ->first();
            }

            if ($post && ($user->hasRole(['admin', 'editor']) || $user->can('update', $post))) {
                $editUrl = url('/admin/posts/' . $post->id . '/edit');
                $items[] = [
                    'id' => 'edit-post',
                    'label' => _l('Edit Post'),
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 35, // After Customize (30)
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }

        // Check if we're on a page detail page
        if ($routeName === 'pages.show' && $slug) {
            $currentLocale = app()->getLocale();
            $page = \App\Models\Post::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('type', 'page')
                ->where('locale', $currentLocale)
                ->first();

            if (!$page) {
                $page = \App\Models\Post::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->where('type', 'page')
                    ->first();
            }

            if ($page) {
                $appendEditPageItem($page);
            } else {
                // Fallback: When posts use root-level permalinks (no base prefix),
                // client-side route detection cannot distinguish /{slug} as post vs page,
                // so it defaults to 'pages.show'. Try finding a post with this slug.
                $post = \App\Models\Post::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->where('type', 'post')
                    ->where('locale', $currentLocale)
                    ->first();

                if (!$post) {
                    $post = \App\Models\Post::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->where('type', 'post')
                        ->first();
                }

                if ($post && ($user->hasRole(['admin', 'editor']) || $user->can('update', $post))) {
                    $editUrl = url('/admin/posts/' . $post->id . '/edit');
                    $items[] = [
                        'id' => 'edit-post',
                        'label' => _l('Edit Post'),
                        'url' => $editUrl,
                        'icon' => 'pencil',
                        'priority' => 35,
                        'group' => 'left',
                        'highlight' => true,
                    ];
                }
            }
        }

        // Support static homepage configured via Reading settings
        if ($routeName === 'home') {
            $settingsService = app(\App\Services\SettingsService::class);
            $showOnFront = $settingsService->get('reading_show_on_front', 'posts');
            $homepageId = $settingsService->get('reading_page_on_front');

            if ($showOnFront === 'page' && $homepageId) {
                $originalPage = \App\Models\Post::withoutGlobalScope('locale')
                    ->where('id', $homepageId)
                    ->where('type', 'page')
                    ->where('status', 'published')
                    ->first();

                if ($originalPage) {
                    $homepage = $originalPage;
                    $currentLocale = app()->getLocale();
                    if ($originalPage->locale !== $currentLocale) {
                        $translatedPage = $originalPage->getTranslation($currentLocale);
                        if ($translatedPage) {
                            $homepage = $translatedPage;
                        }
                    }
                    $appendEditPageItem($homepage);
                }
            }
        }

        // Check if we're on a category page
        if (($routeName === 'categories.show' || $routeName === 'product-categories.show' || $routeName === 'pages.show' || $routeName === 'theme.flexidocs.category') && $slug) {
            $currentLocale = app()->getLocale();
            $category = null;
            if ($routeName === 'product-categories.show') {
                $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->where('locale', $currentLocale)
                    ->first();
                if (!$category) {
                    $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            } elseif ($routeName === 'categories.show') {
                $category = \App\Models\Category::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->where('locale', $currentLocale)
                    ->first();
                if (!$category) {
                    $category = \App\Models\Category::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            } else {
                // For pages.show (fallback) or custom routes, try both
                $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                            ->where('slug', $slug)
                            ->where('locale', $currentLocale)
                            ->first() 
                            ?? \App\Models\Category::withoutGlobalScope('locale')
                            ->where('slug', $slug)
                            ->where('locale', $currentLocale)
                            ->first();
                
                if (!$category) {
                    $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                                ->where('slug', $slug)
                                ->first() 
                                ?? \App\Models\Category::withoutGlobalScope('locale')
                                ->where('slug', $slug)
                                ->first();
                }
            }

            if ($category && ($user->hasRole(['admin', 'editor']) || $user->can('update', $category))) {
                $editUrl = '';
                $label = '';
                
                if ($category instanceof \App\Models\ProductCategory) {
                    $editUrl = url('/admin/product-categories/' . $category->id . '/edit');
                    $label = _l('Edit Product Category');
                } else {
                    $editUrl = url('/admin/categories/' . $category->id . '/edit');
                    $type = $request->get('type', $category->type ?? 'post');
                    $label = $type === 'product' ? _l('Edit Product Category') : _l('Edit Post Category');
                }

                $items[] = [
                    'id' => 'edit-category',
                    'label' => $label,
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 35, // After Customize (30)
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }

        // Check if we're on a product brand page
        if (($routeName === 'product-brands.show' || $routeName === 'pages.show') && $slug) {
            $currentLocale = app()->getLocale();
            $brand = \App\Models\ProductBrand::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('locale', $currentLocale)
                ->first();

            if (!$brand) {
                $brand = \App\Models\ProductBrand::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->first();
            }

            if ($brand && ($user->hasRole(['admin', 'editor']) || $user->can('update', $brand))) {
                $editUrl = url('/admin/product-brands/' . $brand->id . '/edit');
                $items[] = [
                    'id' => 'edit-brand',
                    'label' => _l('Edit Product Brand'),
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 35, // After Customize (30)
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }

        // Check if we're on a product detail page
        if (($routeName === 'products.show' || $routeName === 'pages.show') && $slug) {
            $currentLocale = app()->getLocale();
            $product = \App\Models\Product::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('locale', $currentLocale)
                ->first();

            if (!$product) {
                $product = \App\Models\Product::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->first();
            }

            if ($product && ($user->hasRole(['admin', 'editor']) || $user->can('update', $product))) {
                $editUrl = url('/admin/products/' . $product->id . '/edit');
                $productEditItem = [
                    'id' => 'edit-product',
                    'label' => _l('Edit Product'),
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 35, // After Customize (30)
                    'group' => 'left',
                    'highlight' => true,
                ];

                $editChildren = [
                    [
                        'id' => 'edit-product-item',
                        'label' => _l('Edit Product'),
                        'url' => $editUrl,
                        'icon' => 'pencil',
                    ]
                ];

                $editChildren = Hook::applyFilters('topbar.product_edit_children', $editChildren, $product, $user);

                if (count($editChildren) > 1) {
                    $productEditItem['children'] = $editChildren;
                }

                $items[] = $productEditItem;
            }
        }

        // Check if we're on a post tag page
        if (($routeName === 'tags.show' || $routeName === 'pages.show') && $slug) {
            $currentLocale = app()->getLocale();
            $tag = \App\Models\PostTag::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('locale', $currentLocale)
                ->first();

            if (!$tag) {
                $tag = \App\Models\PostTag::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->first();
            }

            // Check permission: admin/editor can edit tags
            if ($tag && ($user->hasRole(['admin', 'editor']) || $user->can('update', $tag))) {
                $editUrl = url('/admin/post-tags/' . $tag->id . '/edit');
                $items[] = [
                    'id' => 'edit-post-tag',
                    'label' => _l('Edit Post Tag'),
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 35, // After Customize (30)
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }

        // Check if we're on a product tag page
        if (($routeName === 'product-tags.show' || $routeName === 'pages.show') && $slug) {
            $currentLocale = app()->getLocale();
            $tag = \App\Models\ProductTag::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('locale', $currentLocale)
                ->first();

            if (!$tag) {
                $tag = \App\Models\ProductTag::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->first();
            }

            // Check permission: admin/editor can edit tags
            if ($tag && ($user->hasRole(['admin', 'editor']) || $user->can('update', $tag))) {
                $editUrl = url('/admin/product-tags/' . $tag->id . '/edit');
                $items[] = [
                    'id' => 'edit-product-tag',
                    'label' => _l('Edit Product Tag'),
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 35, // After Customize (30)
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }

        // Allow hooks to add more context-aware links
        // Note: Hooks should use the filter hook 'topbar.menu.items' to add items
        Hook::doAction('topbar.menu.context', $request, $user);

        return $items;
    }

    /**
     * Add template switcher link to topbar
     */
    protected function addTemplateSwitcherLink(array $items, Request $request, $user): array
    {
        if (!$user->hasRole(['admin', 'editor'])) {
            return $items;
        }

        $routeName = $request->get('route') ?? Route::currentRouteName();
        $routeParams = $request->get('route_params', []);

        if (is_string($routeParams)) {
            $routeParams = json_decode($routeParams, true) ?? [];
        }

        $slug = null;
        if (is_array($routeParams) && isset($routeParams['slug'])) {
            $slug = $routeParams['slug'];
        } elseif (is_array($routeParams) && isset($routeParams['postSlug'])) {
            $slug = $routeParams['postSlug'];
        } else {
            $slug = $request->route('slug') ?? $request->get('slug') ?? $request->route('postSlug') ?? $request->get('postSlug');
        }

        if (!$slug && $routeName !== 'home') {
            return $items;
        }

        $model = null;
        $entityType = null;
        $viewType = null;

        // 1. Product
        if (($routeName === 'products.show' || $routeName === 'pages.show') && $slug) {
            $currentLocale = app()->getLocale();
            $product = \App\Models\Product::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('locale', $currentLocale)
                ->first();
            if (!$product) {
                $product = \App\Models\Product::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->first();
            }
            if ($product) {
                $model = $product;
                $entityType = 'product';
                $viewType = 'products.show';
            }
        }

        // 2. Post / Page (except home page if not static page)
        if (!$model && ($routeName === 'posts.show' || $routeName === 'pages.show' || $routeName === 'theme.flexidocs.show') && $slug) {
            $currentLocale = app()->getLocale();
            $post = \App\Models\Post::withoutGlobalScope('locale')
                ->where('slug', $slug)
                ->where('locale', $currentLocale)
                ->first();
            if (!$post) {
                $post = \App\Models\Post::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->first();
            }
            if ($post) {
                $model = $post;
                $entityType = $post->type; // 'post' or 'page'
                $viewType = $post->type === 'page' ? 'pages.show' : 'posts.show';
            }
        }

        // 3. Category / ProductCategory
        if (!$model && ($routeName === 'categories.show' || $routeName === 'product-categories.show' || $routeName === 'pages.show' || $routeName === 'theme.flexidocs.category') && $slug) {
            $currentLocale = app()->getLocale();
            if ($routeName === 'product-categories.show') {
                $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->where('locale', $currentLocale)
                    ->first();
                if (!$category) {
                    $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            } elseif ($routeName === 'categories.show') {
                $category = \App\Models\Category::withoutGlobalScope('locale')
                    ->where('slug', $slug)
                    ->where('locale', $currentLocale)
                    ->first();
                if (!$category) {
                    $category = \App\Models\Category::withoutGlobalScope('locale')
                        ->where('slug', $slug)
                        ->first();
                }
            } else {
                $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                            ->where('slug', $slug)
                            ->where('locale', $currentLocale)
                            ->first() 
                            ?? \App\Models\Category::withoutGlobalScope('locale')
                            ->where('slug', $slug)
                            ->where('locale', $currentLocale)
                            ->first();
                
                if (!$category) {
                    $category = \App\Models\ProductCategory::withoutGlobalScope('locale')
                                ->where('slug', $slug)
                                ->first() 
                                ?? \App\Models\Category::withoutGlobalScope('locale')
                                ->where('slug', $slug)
                                ->first();
                }
            }
            if ($category) {
                $model = $category;
                $entityType = $category instanceof \App\Models\ProductCategory ? 'product_category' : 'category';
                $viewType = $category instanceof \App\Models\ProductCategory ? 'product-categories.show' : 'categories.show';
            }
        }

        if (!$model || !$entityType || !$viewType) {
            return $items;
        }

        // Fetch templates
        $templateResolver = app(\App\Services\TemplateResolver::class);
        $templates = $templateResolver->getAvailableTemplates($viewType);

        if (empty($templates)) {
            return $items;
        }

        // Group templates by theme
        $themeGroups = [];
        foreach ($templates as $tpl) {
            $slugKey = $tpl['theme_slug'];
            if (!isset($themeGroups[$slugKey])) {
                $themeGroups[$slugKey] = [
                    'theme_name' => $tpl['theme_name'],
                    'theme_role' => $tpl['theme_role'],
                    'templates' => [],
                ];
            }
            // Add standard theme template or custom templates
            if (!$tpl['is_group'] || $tpl['template_id'] === $slugKey) {
                $themeGroups[$slugKey]['templates'][] = $tpl;
            }
        }

        $activeTemplateId = $model->template_theme;
        $activeTemplateLabel = _l('Default');
        if ($activeTemplateId) {
            foreach ($templates as $t) {
                if ($t['template_id'] === $activeTemplateId) {
                    $activeTemplateLabel = $t['template_name'];
                    // Clean up label
                    $prefix = $t['theme_name'] . ' — ';
                    if (str_starts_with($activeTemplateLabel, $prefix)) {
                        $activeTemplateLabel = substr($activeTemplateLabel, strlen($prefix));
                    }
                    break;
                }
            }
        }

        $switcherChildren = [];

        // 1. Default Option
        $switcherChildren[] = [
            'id' => 'template-opt-default',
            'label' => _l('Default (Main Theme)'),
            'url' => "javascript:switchTopbarTemplate('{$entityType}', {$model->id}, '')",
            'highlight' => empty($activeTemplateId),
        ];

        // 2. Group options
        foreach ($themeGroups as $tSlug => $group) {
            if (empty($group['templates'])) {
                continue;
            }

            $roleLabel = $group['theme_role'] === 'main' ? _l('Main Theme') : _l('Sub Theme');
            $groupLabel = "{$group['theme_name']} ({$roleLabel})";

            $groupTemplates = [];
            foreach ($group['templates'] as $tpl) {
                $tplName = $tpl['template_name'];
                $prefix = $group['theme_name'] . ' — ';
                if (str_starts_with($tplName, $prefix)) {
                    $tplName = substr($tplName, strlen($prefix));
                }

                $groupTemplates[] = [
                    'id' => 'template-opt-' . str_replace('::', '-', $tpl['template_id']),
                    'label' => $tplName,
                    'url' => "javascript:switchTopbarTemplate('{$entityType}', {$model->id}, '{$tpl['template_id']}')",
                    'highlight' => $activeTemplateId === $tpl['template_id'],
                ];
            }

            if (!empty($groupTemplates)) {
                $switcherChildren[] = [
                    'id' => 'template-group-' . $tSlug,
                    'label' => $groupLabel,
                    'url' => '#',
                    'children' => $groupTemplates,
                ];
            }
        }

        $items[] = [
            'id' => 'theme-template-switcher',
            'label' => _l('Template: :name', [':name' => $activeTemplateLabel]),
            'url' => '#',
            'icon' => 'SwatchIcon',
            'priority' => 32, // Right next to Theme Options (31)
            'group' => 'left',
            'children' => $switcherChildren,
        ];

        return $items;
    }

    /**
     * Get menu items grouped by position
     *
     * @param Request|null $request Current request
     * @return array Array with 'left', 'right' keys
     */
    public function getGroupedMenuItems(?Request $request = null): array
    {
        $items = $this->getMenuItems($request);

        $grouped = [
            'left' => [],
            'right' => [],
        ];

        foreach ($items as $item) {
            $group = $item['group'] ?? 'left';
            $grouped[$group][] = $item;
        }

        return $grouped;
    }

    /**
     * Check if topbar should be shown
     *
     * @return bool
     */
    public function shouldShow(): bool
    {
        // Check web session auth (for frontend)
        $user = Auth::guard('web')->user();

        // If no web session, check Sanctum token (for API/admin)
        if (!$user) {
            $user = Auth::guard('sanctum')->user();
        }

        // If still no user, try default guard
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            return false;
        }

        // Allow filtering via hook
        return Hook::applyFilters('topbar.menu.should_show', true, $user);
    }
}
