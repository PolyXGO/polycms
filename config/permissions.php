<?php

return [
    'definitions' => [
        [
            'group' => 'content.posts',
            'label' => 'Posts',
            'permissions' => [
                ['name' => 'view-any post', 'label' => 'View Any Post'],
                ['name' => 'view post', 'label' => 'View Post'],
                ['name' => 'create post', 'label' => 'New Post'],
                ['name' => 'update post', 'label' => 'Update Post'],
                ['name' => 'delete post', 'label' => 'Delete Post'],
                ['name' => 'restore post', 'label' => 'Restore Post'],
                ['name' => 'force-delete post', 'label' => 'Force Delete Post'],
            ],
        ],
        [
            'group' => 'content.products',
            'label' => 'Products',
            'permissions' => [
                ['name' => 'view-any product', 'label' => 'View Any Product'],
                ['name' => 'view product', 'label' => 'View Product'],
                ['name' => 'create product', 'label' => 'New Product'],
                ['name' => 'update product', 'label' => 'Update Product'],
                ['name' => 'delete product', 'label' => 'Delete Product'],
                ['name' => 'restore product', 'label' => 'Restore Product'],
                ['name' => 'force-delete product', 'label' => 'Force Delete Product'],
            ],
        ],
        [
            'group' => 'taxonomy.categories',
            'label' => 'Categories',
            'permissions' => [
                ['name' => 'view-any category', 'label' => 'View Any Category'],
                ['name' => 'view category', 'label' => 'View Category'],
                ['name' => 'create category', 'label' => 'New Category'],
                ['name' => 'update category', 'label' => 'Update Category'],
                ['name' => 'delete category', 'label' => 'Delete Category'],
                ['name' => 'restore category', 'label' => 'Restore Category'],
                ['name' => 'force-delete category', 'label' => 'Force Delete Category'],
            ],
        ],
        [
            'group' => 'taxonomy.tags',
            'label' => 'Tags',
            'permissions' => [
                ['name' => 'view-any tag', 'label' => 'View Any Tag'],
                ['name' => 'view tag', 'label' => 'View Tag'],
                ['name' => 'create tag', 'label' => 'New Tag'],
                ['name' => 'update tag', 'label' => 'Update Tag'],
                ['name' => 'delete tag', 'label' => 'Delete Tag'],
                ['name' => 'restore tag', 'label' => 'Restore Tag'],
                ['name' => 'force-delete tag', 'label' => 'Force Delete Tag'],
            ],
        ],
        [
            'group' => 'media.library',
            'label' => 'Media',
            'permissions' => [
                ['name' => 'view-any media', 'label' => 'View Any Media'],
                ['name' => 'view media', 'label' => 'View Media'],
                ['name' => 'create media', 'label' => 'New Media'],
                ['name' => 'update media', 'label' => 'Update Media'],
                ['name' => 'delete media', 'label' => 'Delete Media'],
                ['name' => 'restore media', 'label' => 'Restore Media'],
                ['name' => 'force-delete media', 'label' => 'Force Delete Media'],
            ],
        ],
        [
            'group' => 'appearance.widgets',
            'label' => 'Widgets',
            'permissions' => [
                ['name' => 'view widgets', 'label' => 'View Widgets'],
                ['name' => 'manage widgets', 'label' => 'Manage Widgets'],
            ],
        ],
        [
            'group' => 'appearance.themes',
            'label' => 'Themes',
            'permissions' => [
                ['name' => 'view themes', 'label' => 'View Themes'],
                ['name' => 'activate themes', 'label' => 'Activate Themes'],
                ['name' => 'manage theme options', 'label' => 'Manage Theme Options'],
            ],
        ],
        [
            'group' => 'appearance.layouts',
            'label' => 'Template Parts & Templates',
            'permissions' => [
                ['name' => 'view layout assets', 'label' => 'View Template Parts & Templates'],
                ['name' => 'create layout assets', 'label' => 'Create Template Parts & Templates'],
                ['name' => 'update layout assets', 'label' => 'Update Template Parts & Templates'],
                ['name' => 'delete layout assets', 'label' => 'Delete Template Parts & Templates'],
                ['name' => 'apply layout templates', 'label' => 'Apply Layout Templates'],
            ],
        ],
        [
            'group' => 'modules.core',
            'label' => 'Modules',
            'permissions' => [
                ['name' => 'view modules', 'label' => 'View Modules'],
                ['name' => 'install modules', 'label' => 'Install Modules'],
                ['name' => 'update modules', 'label' => 'Update Modules'],
                ['name' => 'delete modules', 'label' => 'Delete Modules'],
            ],
        ],
        [
            'group' => 'settings.core',
            'label' => 'Settings',
            'permissions' => [
                ['name' => 'view settings', 'label' => 'View Settings'],
                ['name' => 'manage settings', 'label' => 'Manage Settings'],
            ],
        ],
        [
            'group' => 'users.management',
            'label' => 'Users',
            'permissions' => [
                ['name' => 'view users', 'label' => 'View Users'],
                ['name' => 'create users', 'label' => 'New User'],
                ['name' => 'update users', 'label' => 'Update Users'],
                ['name' => 'delete users', 'label' => 'Delete Users'],
                ['name' => 'manage roles', 'label' => 'Manage Roles'],
            ],
        ],
        [
            'group' => 'security.tokens',
            'label' => 'API Tokens',
            'permissions' => [
                ['name' => 'view api tokens', 'label' => 'View API Tokens'],
                ['name' => 'create api tokens', 'label' => 'New API Token'],
                ['name' => 'update api tokens', 'label' => 'Update API Tokens'],
                ['name' => 'delete api tokens', 'label' => 'Delete API Tokens'],
            ],
        ],
        [
            'group' => 'ecommerce.orders',
            'label' => 'Orders',
            'permissions' => [
                ['name' => 'view-any order', 'label' => 'View Any Order'],
                ['name' => 'view order', 'label' => 'View Order'],
                ['name' => 'update order', 'label' => 'Update Order'],
                ['name' => 'refund order', 'label' => 'Refund Order'],
                ['name' => 'delete order', 'label' => 'Delete Order'],
            ],
        ],
        [
            'group' => 'ecommerce.inventory',
            'label' => 'Inventory',
            'permissions' => [
                ['name' => 'view inventory logs', 'label' => 'View Inventory Logs'],
                ['name' => 'adjust inventory', 'label' => 'Adjust Inventory'],
            ],
        ],
        [
            'group' => 'ecommerce.coupons',
            'label' => 'Coupons',
            'permissions' => [
                ['name' => 'view-any coupon', 'label' => 'View Any Coupon'],
                ['name' => 'view coupon', 'label' => 'View Coupon'],
                ['name' => 'create coupon', 'label' => 'New Coupon'],
                ['name' => 'update coupon', 'label' => 'Update Coupon'],
                ['name' => 'delete coupon', 'label' => 'Delete Coupon'],
            ],
        ],
        [
            'group' => 'ecommerce.subscriptions',
            'label' => 'Subscriptions',
            'permissions' => [
                ['name' => 'view-any subscription', 'label' => 'View Any Subscription'],
                ['name' => 'update subscription', 'label' => 'Update Subscription'],
            ],
        ],
        [
            'group' => 'ecommerce.licenses',
            'label' => 'Licenses',
            'permissions' => [
                ['name' => 'view-any license', 'label' => 'View Any License'],
                ['name' => 'update license', 'label' => 'Update License'],
            ],
        ],
    ],
];
