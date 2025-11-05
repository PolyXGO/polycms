<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        
        // Admin dashboard link
        $items[] = [
            'id' => 'admin-dashboard',
            'label' => 'Dashboard',
            'url' => $baseUrl . '/admin',
            'icon' => 'dashboard',
            'priority' => 10,
            'group' => 'left',
        ];

        // New content dropdown
        $newItems = [];
        
        if ($user->can('create', \App\Models\Post::class)) {
            $newItems[] = [
                'id' => 'new-post',
                'label' => 'Post',
                'url' => $baseUrl . '/admin/posts/create',
                'icon' => 'document',
            ];
        }
        
        if ($user->can('create', \App\Models\Post::class)) {
            $newItems[] = [
                'id' => 'new-page',
                'label' => 'Page',
                'url' => $baseUrl . '/admin/posts/create?type=page',
                'icon' => 'page',
            ];
        }
        
        if ($user->can('create', \App\Models\Product::class)) {
            $newItems[] = [
                'id' => 'new-product',
                'label' => 'Product',
                'url' => $baseUrl . '/admin/products/create',
                'icon' => 'product',
            ];
        }

        if (!empty($newItems)) {
            $items[] = [
                'id' => 'new',
                'label' => 'New',
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
                'label' => 'Customize',
                'url' => $baseUrl . '/admin/themes',
                'icon' => 'paint-brush',
                'priority' => 30,
                'group' => 'left',
            ];
        }

        // User menu
        $userItems = [
            [
                'id' => 'profile',
                'label' => 'Profile',
                'url' => url('/profile'),
                'icon' => 'user',
            ],
            [
                'id' => 'logout',
                'label' => 'Log Out',
                'url' => url('/logout'),
                'icon' => 'logout',
                'method' => 'POST',
            ],
        ];

        $items[] = [
            'id' => 'user-menu',
            'label' => $user->name ?? 'User',
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
        $routeName = Route::currentRouteName();
        $currentUrl = $request->url();
        
        // Check if we're on a post/page detail page
        if ($routeName === 'posts.show') {
            $slug = $request->route('slug');
            $post = \App\Models\Post::where('slug', $slug)->first();
            
            if ($post && $user->can('update', $post)) {
                $editUrl = url('/admin/posts/' . $post->id . '/edit');
                $items[] = [
                    'id' => 'edit-post',
                    'label' => 'Edit Post',
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 5,
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }
        
        // Check if we're on a page detail page
        if ($routeName === 'pages.show') {
            $slug = $request->route('slug');
            $page = \App\Models\Post::where('slug', $slug)
                ->where('type', 'page')
                ->first();
            
            if ($page && $user->can('update', $page)) {
                $editUrl = url('/admin/posts/' . $page->id . '/edit');
                $items[] = [
                    'id' => 'edit-page',
                    'label' => 'Edit Page',
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 5,
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }
        
        // Check if we're on a category page
        if ($routeName === 'categories.show') {
            $slug = $request->route('slug');
            $category = \App\Models\Category::where('slug', $slug)->first();
            
            if ($category && $user->can('update', $category)) {
                $editUrl = url('/admin/categories/' . $category->id . '/edit');
                $items[] = [
                    'id' => 'edit-category',
                    'label' => 'Edit Category',
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 5,
                    'group' => 'left',
                    'highlight' => true,
                ];
            }
        }
        
        // Check if we're on a product detail page
        if ($routeName === 'products.show') {
            $slug = $request->route('slug');
            $product = \App\Models\Product::where('slug', $slug)->first();
            
            if ($product && $user->can('update', $product)) {
                $editUrl = url('/admin/products/' . $product->id . '/edit');
                $items[] = [
                    'id' => 'edit-product',
                    'label' => 'Edit Product',
                    'url' => $editUrl,
                    'icon' => 'pencil',
                    'priority' => 5,
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

