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

        // If user is not authenticated, return empty array
        if (!$user) {
            return [];
        }

        $items = [];

        // Core menu items
        $items = $this->addCoreMenuItems($items, $request, $user);

        // Context-aware edit links
        $items = $this->addContextEditLinks($items, $request, $user);

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
            ];
        }

        // Profile URL based on current context
        if ($isAdmin) {
            $profileUrl = url('/admin/profile');
        } else {
            $profileUrl = url('/account/profile');
        }

        // User menu
        $userItems = [
            [
                'id' => 'profile',
                'label' => _l('Profile'),
                'url' => $profileUrl,
                'icon' => 'user',
            ],
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
        ];

        $items[] = [
            'id' => 'user-menu',
            'label' => $user->name ?: ($user->email ?: 'User'),
            'url' => $profileUrl,
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
        } else {
            $slug = $request->route('slug') ?? $request->get('slug');
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
            if (!$page || !$user->can('update', $page)) {
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
        if ($routeName === 'posts.show' && $slug) {
            $post = \App\Models\Post::where('slug', $slug)
                ->where('type', 'post')
                ->first();

            if ($post && $user->can('update', $post)) {
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
            $page = \App\Models\Post::where('slug', $slug)
                ->where('type', 'page')
                ->first();

            if ($page) {
                $appendEditPageItem($page);
            } else {
                // Fallback: When posts use root-level permalinks (no base prefix),
                // client-side route detection cannot distinguish /{slug} as post vs page,
                // so it defaults to 'pages.show'. Try finding a post with this slug.
                $post = \App\Models\Post::where('slug', $slug)
                    ->where('type', 'post')
                    ->first();

                if ($post && $user->can('update', $post)) {
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
                $homepage = \App\Models\Post::where('id', $homepageId)
                    ->where('type', 'page')
                    ->where('status', 'published')
                    ->first();

                $appendEditPageItem($homepage);
            }
        }

        // Check if we're on a category page
        if (($routeName === 'categories.show' || $routeName === 'product-categories.show') && $slug) {
            // Try to find as product category first if route matches
            $category = null;
            if ($routeName === 'product-categories.show') {
                $category = \App\Models\ProductCategory::where('slug', $slug)->first();
            } else {
                $category = \App\Models\Category::where('slug', $slug)->first();
            }

            if ($category && $user->can('update', $category)) {
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
        if ($routeName === 'product-brands.show' && $slug) {
            $brand = \App\Models\ProductBrand::where('slug', $slug)->first();

            if ($brand && $user->can('update', $brand)) {
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
        if ($routeName === 'products.show' && $slug) {
            $product = \App\Models\Product::where('slug', $slug)->first();

            if ($product && $user->can('update', $product)) {
                $editUrl = url('/admin/products/' . $product->id . '/edit');
                $items[] = [
                    'id' => 'edit-product',
                    'label' => _l('Edit Product'),
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 35, // After Customize (30)
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }

        // Check if we're on a post tag page
        if ($routeName === 'tags.show' && $slug) {
            $tag = \App\Models\PostTag::where('slug', $slug)->first();

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
        if ($routeName === 'product-tags.show' && $slug) {
            $tag = \App\Models\ProductTag::where('slug', $slug)->first();

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
