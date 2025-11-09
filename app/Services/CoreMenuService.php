<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Core Menu Service - Registers core admin menu items
 */
class CoreMenuService
{
    public function __construct(
        protected MenuRegistry $menuRegistry
    ) {}

    /**
     * Register all core menu items
     */
    public function registerCoreMenus(): void
    {
        // Dashboard
        $this->menuRegistry->register('dashboard', [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'order' => 10,
            'children' => [],
        ]);

        // Posts Menu Group
        $this->menuRegistry->register('posts', [
            'key' => 'posts',
            'label' => 'Posts',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'order' => 20,
            'children' => [
                [
                    'key' => 'posts-all',
                    'label' => 'All Posts',
                    'route' => 'admin.posts.index',
                    'icon' => null,
                    'order' => 1,
                ],
                [
                    'key' => 'posts-create',
                    'label' => 'Add New',
                    'route' => 'admin.posts.create',
                    'icon' => null,
                    'order' => 2,
                ],
                [
                    'key' => 'posts-categories',
                    'label' => 'Categories',
                    'route' => 'admin.categories.index',
                    'routeParams' => ['type' => 'post'],
                    'icon' => null,
                    'order' => 3,
                ],
                [
                    'key' => 'posts-tags',
                    'label' => 'Tags',
                    'route' => 'admin.post-tags.index',
                    'icon' => null,
                    'order' => 4,
                ],
            ],
        ]);

        // Products Menu Group
        $this->menuRegistry->register('products', [
            'key' => 'products',
            'label' => 'Products',
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'order' => 30,
            'children' => [
                [
                    'key' => 'products-all',
                    'label' => 'All Products',
                    'route' => 'admin.products.index',
                    'icon' => null,
                    'order' => 1,
                ],
                [
                    'key' => 'products-create',
                    'label' => 'Add New',
                    'route' => 'admin.products.create',
                    'icon' => null,
                    'order' => 2,
                ],
                [
                    'key' => 'products-categories',
                    'label' => 'Categories',
                    'route' => 'admin.categories.index',
                    'routeParams' => ['type' => 'product'],
                    'icon' => null,
                    'order' => 3,
                ],
                [
                    'key' => 'products-tags',
                    'label' => 'Tags',
                    'route' => 'admin.product-tags.index',
                    'icon' => null,
                    'order' => 4,
                ],
            ],
        ]);

        // Media
        $this->menuRegistry->register('media', [
            'key' => 'media',
            'label' => 'Media',
            'route' => 'admin.media.index',
            'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'order' => 40,
            'children' => [],
        ]);

        // Modules
        $this->menuRegistry->register('modules', [
            'key' => 'modules',
            'label' => 'Modules',
            'route' => 'admin.modules.index',
            'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
            'order' => 60,
            'children' => [],
        ]);

        // Appearance (Themes)
        $this->menuRegistry->register('appearance', [
            'key' => 'appearance',
            'label' => 'Appearance',
            'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
            'order' => 65,
            'children' => [
                [
                    'key' => 'appearance-themes',
                    'label' => 'Themes',
                    'route' => 'admin.themes.index',
                    'icon' => null,
                    'order' => 1,
                ],
                [
                    'key' => 'appearance-widgets',
                    'label' => 'Widgets',
                    'route' => 'admin.widgets.index',
                    'icon' => null,
                    'order' => 2,
                ],
            ],
        ]);

        // Settings
        $this->menuRegistry->register('settings', [
            'key' => 'settings',
            'label' => 'Settings',
            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'order' => 70,
            'children' => [
                [
                    'key' => 'settings-general',
                    'label' => 'General',
                    'route' => 'admin.options-general',
                    'icon' => null,
                    'order' => 1,
                ],
            ],
        ]);
    }
}
