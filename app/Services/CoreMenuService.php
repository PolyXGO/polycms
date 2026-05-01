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
            'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
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

        // Pages Menu Group
        $this->menuRegistry->register('pages', [
            'key' => 'pages',
            'label' => 'Pages',
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'order' => 25,
            'children' => [
                [
                    'key' => 'pages-all',
                    'label' => 'All Pages',
                    'route' => 'admin.pages.index',
                    'icon' => null,
                    'order' => 1,
                ],
                [
                    'key' => 'pages-create',
                    'label' => 'Add New',
                    'route' => 'admin.pages.create',
                    'icon' => null,
                    'order' => 2,
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
                    'route' => 'admin.product-categories.index',
                    'icon' => null,
                    'order' => 3,
                ],
                [
                    'key' => 'products-brands',
                    'label' => 'Brands',
                    'route' => 'admin.product-brands.index',
                    'icon' => null,
                    'order' => 4,
                ],
                [
                    'key' => 'products-tags',
                    'label' => 'Tags',
                    'route' => 'admin.product-tags.index',
                    'icon' => null,
                    'order' => 5,
                ],
                [
                    'key' => 'products-attributes',
                    'label' => 'Attributes',
                    'route' => 'admin.products.attributes',
                    'icon' => null,
                    'order' => 6,
                ],
                [
                    'key' => 'products-attribute-groups',
                    'label' => 'Attribute Groups',
                    'route' => 'admin.products.attribute-groups',
                    'icon' => null,
                    'order' => 7,
                ],
            ],
        ]);

        // E-commerce Menu Group
        $this->menuRegistry->register('ecommerce', [
            'key' => 'ecommerce',
            'label' => 'E-commerce',
            'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
            'order' => 35,
            'children' => [
                [
                    'key' => 'orders',
                    'label' => 'Orders',
                    'route' => 'admin.orders.index',
                    'icon' => null,
                    'order' => 1,
                ],
                [
                    'key' => 'invoices',
                    'label' => 'Invoices',
                    'route' => 'admin.invoices.index',
                    'icon' => null,
                    'order' => 2,
                ],
                [
                    'key' => 'coupons',
                    'label' => 'Coupons',
                    'route' => 'admin.coupons.index',
                    'icon' => null,
                    'order' => 2,
                ],
                [
                    'key' => 'subscriptions',
                    'label' => 'Subscriptions',
                    'route' => 'admin.subscriptions.index',
                    'icon' => null,
                    'order' => 3,
                ],
                [
                    'key' => 'licenses',
                    'label' => 'Licenses',
                    'route' => 'admin.licenses.index',
                    'icon' => null,
                    'order' => 4,
                ],
                [
                    'key' => 'shipping',
                    'label' => 'Shipping',
                    'route' => 'admin.ecommerce.shipping-zones.index',
                    'icon' => null,
                    'order' => 5,
                ],
                [
                    'key' => 'taxes',
                    'label' => 'Taxes',
                    'route' => 'admin.ecommerce.tax-rates.index',
                    'icon' => null,
                    'order' => 6,
                ],
                [
                    'key' => 'reviews',
                    'label' => 'Reviews',
                    'route' => 'admin.reviews.index',
                    'icon' => null,
                    'order' => 7,
                ],
            ],
        ]);

        // Payments Menu Group
        $this->menuRegistry->register('payments', [
            'key' => 'payments',
            'label' => 'Payments',
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
            'order' => 36,
            'children' => [
                [
                    'key' => 'transactions',
                    'label' => 'Transactions',
                    'route' => 'admin.transactions.index',
                    'icon' => null,
                    'order' => 1,
                ],
                [
                    'key' => 'payment-logs',
                    'label' => 'Payment Logs',
                    'route' => 'admin.payment-logs.index',
                    'icon' => null,
                    'order' => 2,
                ],
                [
                    'key' => 'payment-methods',
                    'label' => 'Payment methods',
                    'route' => 'admin.settings.gateways',
                    'icon' => null,
                    'order' => 3,
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

        // Reports (empty shell — themes/modules register children via admin.menu.build hook)
        $this->menuRegistry->register('reports', [
            'key' => 'reports',
            'label' => 'Reports',
            'route' => 'admin.reports.index',
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'order' => 68,
            'children' => [],
        ]);

        // Users
        $this->menuRegistry->register('users', [
            'key' => 'users',
            'label' => 'Users',
            'icon' => 'M17 20h5v-2a3 3 0 00-3-3h-2m-4 5h-6a3 3 0 01-3-3v-1a3 3 0 013-3h6a3 3 0 013 3v1a3 3 0 01-3 3zm3-11a4 4 0 11-8 0 4 4 0 018 0zm6 4v-1a3 3 0 00-3-3h-1',
            'order' => 75,
            'children' => [
                [
                    'key' => 'users-all',
                    'label' => 'All Users',
                    'route' => 'admin.users.index',
                    'icon' => null,
                    'order' => 1,
                ],
                [
                    'key' => 'users-create',
                    'label' => 'Add New',
                    'route' => 'admin.users.create',
                    'icon' => null,
                    'order' => 2,
                ],
                [
                    'key' => 'users-roles',
                    'label' => 'Roles & Permissions',
                    'route' => 'admin.roles.index',
                    'icon' => null,
                    'order' => 3,
                ],
            ],
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
                    'key' => 'appearance-theme-options',
                    'label' => 'Theme Options',
                    'route' => 'admin.themes.options',
                    'icon' => null,
                    'order' => 2,
                ],
                [
                    'key' => 'appearance-template-parts',
                    'label' => 'Template Parts',
                    'route' => 'admin.appearance.parts.index',
                    'icon' => null,
                    'order' => 3,
                ],
                [
                    'key' => 'appearance-templates',
                    'label' => 'Templates',
                    'route' => 'admin.appearance.templates.index',
                    'icon' => null,
                    'order' => 4,
                ],
                [
                    'key' => 'appearance-widgets',
                    'label' => 'Widgets',
                    'route' => 'admin.widgets.index',
                    'icon' => null,
                    'order' => 5,
                ],
                [
                    'key' => 'appearance-menus',
                    'label' => 'Menus',
                    'route' => 'admin.menus.index',
                    'icon' => null,
                    'order' => 6,
                ],
            ],
        ]);

        // Settings
        $this->menuRegistry->register('settings', [
            'key' => 'settings',
            'label' => 'Settings',
            'route' => 'admin.settings.index',
            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'order' => 70,
            'children' => [
                [
                    'key' => 'settings-hub',
                    'label' => 'Settings Hub',
                    'route' => 'admin.settings.index',
                    'order' => 1,
                ],
            ],
        ]);

        // Inject Pinned Settings Dynamically
        try {
            $settingModel = \App\Models\Setting::where('key', 'admin_pinned_settings')->first();
            $pinnedJson = $settingModel ? $settingModel->value : [];
            
            if ($pinnedJson && is_array($pinnedJson)) {
                $orderOffset = 10;
                $activeModules = array_keys(app(\App\Services\ModuleManager::class)->getEnabledModules());

                $legacyModuleMap = [
                    'mtoptimize' => 'Polyx.MTOptimize',
                    'cookie_consent' => 'Polyx.CookieConsent'
                ];

                foreach ($pinnedJson as $pinnedItem) {
                    $moduleReq = $pinnedItem['module'] ?? ($legacyModuleMap[$pinnedItem['key'] ?? ''] ?? null);
                    if ($moduleReq && !in_array($moduleReq, $activeModules, true)) {
                        continue;
                    }

                    $this->menuRegistry->addChild('settings', [
                        'key' => 'settings-' . ($pinnedItem['key'] ?? uniqid()),
                        'label' => $pinnedItem['label'] ?? 'Unknown',
                        'route' => isset($pinnedItem['route']['name']) ? $pinnedItem['route']['name'] : ($pinnedItem['route'] ?? null),
                        // Pass params to urlParams for Vue Router interpolation
                        'urlParams' => $pinnedItem['route']['params'] ?? null,
                        'order' => $orderOffset++,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database settings are unavailable during installation
        }
    }
}
