<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\LayoutAssetController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\PostTagController;
use App\Http\Controllers\Api\V1\ProductTagController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductBrandController;
use App\Http\Controllers\Api\V1\ProductAttributeController;
use App\Http\Controllers\Api\V1\ProductAttributeGroupController;
use App\Http\Controllers\Api\V1\ShippingZoneController;
use App\Http\Controllers\Api\V1\TaxRateController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\AdminMenuController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\WidgetAreaController;
use App\Http\Controllers\Api\V1\WidgetController;
use App\Http\Controllers\Api\V1\WidgetInstanceController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\ThemeController;
use App\Http\Controllers\Api\V1\TopbarMenuController;
use App\Http\Controllers\Api\V1\TranslationController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\EditorPanelController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\MenuItemController;
use App\Http\Controllers\Api\V1\MenuContentController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\EmailTemplateController;
use App\Http\Controllers\Api\V1\PaymentGatewayController;
use App\Http\Controllers\Api\V1\ProductInventoryController;
use App\Http\Controllers\Api\V1\ContentVoteController;
use App\Http\Controllers\Api\V1\SystemUpdateController;
use App\Services\ModuleManager;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Public endpoints (no auth required)
    Route::get('/posts', [PostController::class, 'index'])->name('api.v1.posts.index');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('api.v1.posts.show');
    Route::get('/products', [ProductController::class, 'index'])->name('api.v1.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.v1.products.show');
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.v1.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('api.v1.categories.show');
    Route::get('/tags', [TagController::class, 'index'])->name('api.v1.tags.index');
    Route::get('/tags/{tag}', [TagController::class, 'show'])->name('api.v1.tags.show');
    Route::get('/post-tags', [PostTagController::class, 'index'])->name('api.v1.post-tags.index');
    Route::get('/post-tags/{postTag}', [PostTagController::class, 'show'])->name('api.v1.post-tags.show');
    Route::get('/product-tags', [ProductTagController::class, 'index'])->name('api.v1.product-tags.index');
    Route::get('/product-tags/{productTag}', [ProductTagController::class, 'show'])->name('api.v1.product-tags.show');
    Route::get('/product-categories', [ProductCategoryController::class, 'index'])->name('api.v1.product-categories.index');
    Route::get('/product-categories/{productCategory}', [ProductCategoryController::class, 'show'])->name('api.v1.product-categories.show');
    Route::get('/product-brands', [ProductBrandController::class, 'index'])->name('api.v1.product-brands.index');
    Route::get('/product-brands/{productBrand}', [ProductBrandController::class, 'show'])->name('api.v1.product-brands.show');
    Route::get('/product-attributes', [ProductAttributeController::class, 'index'])->name('api.v1.product-attributes.index');
    Route::get('/product-attributes/{attribute}', [ProductAttributeController::class, 'show'])->name('api.v1.product-attributes.show');

    // Public settings for login/guest views
    Route::get('/public/settings', [SettingsController::class, 'publicSettings'])->name('api.v1.public.settings');

    // Translation route (public, needed for admin panel)
    Route::get('/translations', [TranslationController::class, 'index'])->name('api.v1.translations.index');

    // Language Management (public for list, protected for others handled below)
    Route::get('/languages', [LanguageController::class, 'index'])->name('api.v1.languages.index');

    // Google OAuth Callback (Public to handle redirect from Google)
    Route::get('/email/oauth/google/callback', [\App\Http\Controllers\Admin\EmailController::class, 'handleGoogleCallback'])->name('api.v1.email.oauth.google.callback');

    // Content Votes (public — anonymous voting)
    Route::post('/content-votes', [ContentVoteController::class, 'store'])->name('api.v1.content-votes.store');



    // Authentication endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');

        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.v1.auth.refresh');
        });
    });

    // Protected API routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Global / Common
        Route::get('locations/countries', [LocationController::class, 'countries']);

        // Posts CRUD
        Route::apiResource('posts', PostController::class)->except(['index', 'show']);

        // Products CRUD
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Categories CRUD
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

        // Tags CRUD (deprecated - use post-tags and product-tags instead)
        Route::apiResource('tags', TagController::class)->except(['index', 'show']);

        // Post Tags CRUD
        Route::apiResource('post-tags', PostTagController::class)->except(['index', 'show']);

        // Product Tags CRUD
        Route::apiResource('product-tags', ProductTagController::class)->except(['index', 'show']);

        // Product Categories CRUD
        Route::apiResource('product-categories', ProductCategoryController::class)->except(['index', 'show']);

        // Product Brands CRUD
        Route::apiResource('product-brands', ProductBrandController::class)->except(['index', 'show']);

        // Product Attributes CRUD
        Route::apiResource('product-attribute-groups', ProductAttributeGroupController::class)->parameters([
            'product-attribute-groups' => 'productAttributeGroup'
        ]);
        Route::post('product-attribute-groups/{productAttributeGroup}/sync-attributes', [ProductAttributeGroupController::class, 'syncAttributes'])->name('product-attribute-groups.sync-attributes');
        Route::apiResource('product-attributes', ProductAttributeController::class)->parameters([
            'product-attributes' => 'attribute'
        ]);
        Route::post('product-attributes/{attribute}/values', [ProductAttributeController::class, 'storeValue'])->name('product-attributes.values.store');
        Route::put('product-attributes/{attribute}/values/{value}', [ProductAttributeController::class, 'updateValue'])->name('product-attributes.values.update');
        Route::delete('product-attributes/{attribute}/values/{value}', [ProductAttributeController::class, 'destroyValue'])->name('product-attributes.values.destroy');

        Route::get('/roles/meta', [RoleController::class, 'meta'])->name('api.v1.roles.meta');
        Route::post('/roles/{role}/clone', [RoleController::class, 'clone'])->name('api.v1.roles.clone');
        Route::apiResource('roles', RoleController::class);
        Route::get('/users/meta', [UserController::class, 'meta'])->name('api.v1.users.meta');
        Route::apiResource('users', UserController::class);
        Route::get('/editor-panels/{type}', [EditorPanelController::class, 'index'])->name('api.v1.editor-panels.index');
        Route::put('/editor-panels/{type}', [EditorPanelController::class, 'update'])->name('api.v1.editor-panels.update');
        Route::post('/layout-assets/{layout_asset}/duplicate', [LayoutAssetController::class, 'duplicate'])->name('api.v1.layout-assets.duplicate');
        Route::apiResource('layout-assets', LayoutAssetController::class);
        
        // E-commerce Routes
        Route::name('admin.')->group(function () {
            Route::get('admin/users/search', [UserController::class, 'search'])->name('users.search');
             // Moved orders to web.php for session access
            // Route::apiResource('orders', OrderController::class)->except(['store', 'destroy']);
            Route::apiResource('coupons', CouponController::class);
            Route::apiResource('subscriptions', \App\Http\Controllers\Api\V1\SubscriptionController::class)->only(['index', 'show']);
            Route::apiResource('licenses', \App\Http\Controllers\Api\V1\LicenseController::class)->only(['index', 'show']);
            Route::apiResource('shipping-zones', ShippingZoneController::class);
            Route::apiResource('tax-rates', TaxRateController::class);
            
            // Payments section
            Route::get('transactions', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'index'])->name('transactions.index');
            Route::get('transactions/stats', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'stats'])->name('transactions.stats');
            Route::get('transactions/{id}', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'show'])->name('transactions.show');
            Route::patch('transactions/{id}/status', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'updateStatus'])->name('transactions.update-status');
            Route::get('payment-logs', [\App\Http\Controllers\Api\Admin\PaymentLogController::class, 'index'])->name('payment-logs.index');
            Route::delete('payment-logs/clear', [\App\Http\Controllers\Api\Admin\PaymentLogController::class, 'clear'])->name('payment-logs.clear');
            
            // Settings Hub Extensions
            Route::apiResource('email-templates', EmailTemplateController::class);
            Route::post('email-templates/{id}/preview', [EmailTemplateController::class, 'preview'])->name('admin.email-templates.preview');
            Route::apiResource('payment-gateways', PaymentGatewayController::class);
            Route::post('payment-gateways/reorder', [PaymentGatewayController::class, 'reorder'])->name('admin.payment-gateways.reorder');
            Route::post('payment-gateways/{code}/set-default', [PaymentGatewayController::class, 'setDefault'])->name('admin.payment-gateways.set-default');
            Route::get('products/{id}/stock-movements', [ProductInventoryController::class, 'stockMovements'])->name('admin.products.stock-movements');
        });
        


        // Media management — folder routes MUST come before apiResource
        // to prevent DELETE /media/{media} from catching /media/folders
        Route::post('/media/folders', [MediaController::class, 'createFolder'])->name('api.v1.media.folders.create');
        Route::put('/media/folders/rename', [MediaController::class, 'renameFolder'])->name('api.v1.media.folders.rename');
        Route::delete('/media/folders', [MediaController::class, 'deleteFolder'])->name('api.v1.media.folders.delete');
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('api.v1.media.upload');
        Route::post('/media/upload-from-url', [MediaController::class, 'uploadFromUrl'])->name('api.v1.media.upload-from-url');
        Route::get('/media/{media}/serve', [MediaController::class, 'serve'])->name('api.v1.media.serve');
        Route::apiResource('media', MediaController::class)->where(['media' => '[0-9]+']);

        // Upload for editor
        Route::post('/upload/image', [UploadController::class, 'image'])->name('api.v1.upload.image');

            // Widget routes
            Route::get('/widgets/types', [WidgetController::class, 'types'])->name('api.v1.widgets.types');
            Route::get('/widgets/types/{type}', [WidgetController::class, 'show'])->name('api.v1.widgets.type');
            Route::apiResource('widget-areas', WidgetAreaController::class);
            Route::post('/widget-areas/{widgetArea}/reorder', [WidgetInstanceController::class, 'reorder'])->name('api.v1.widget-areas.reorder');
            Route::apiResource('widget-instances', WidgetInstanceController::class);

            // Webhooks & API Modules
            Route::group(['prefix' => 'webhooks'], function () {
                Route::get('/', [WebhookController::class, 'index']);
                Route::post('/', [WebhookController::class, 'store']);
                Route::get('/{webhook}', [WebhookController::class, 'show']);
                Route::put('/{webhook}', [WebhookController::class, 'update']);
                Route::delete('/{webhook}', [WebhookController::class, 'destroy']);
                Route::post('/{webhook}/ping', [WebhookController::class, 'ping']);
                Route::post('/{webhook}/token', [WebhookController::class, 'generateToken']);
                Route::get('/{webhook}/deliveries', [WebhookController::class, 'deliveries']);
                Route::patch('/{webhook}/toggle', [WebhookController::class, 'toggleStatus']);
            });

            // Modules routes
            Route::get('/modules', [ModuleController::class, 'index'])->name('api.v1.modules.index');
            Route::post('/modules/upload', [ModuleController::class, 'upload'])->name('api.v1.modules.upload');
            Route::get('/modules/{moduleKey}/download', [ModuleController::class, 'download'])->name('api.v1.modules.download');
            Route::post('/modules/{moduleKey}/enable', [ModuleController::class, 'enable'])->name('api.v1.modules.enable');
            Route::post('/modules/{moduleKey}/disable', [ModuleController::class, 'disable'])->name('api.v1.modules.disable');
            Route::delete('/modules/{moduleKey}', [ModuleController::class, 'destroy'])->name('api.v1.modules.destroy');

            // Language Management (Protected)
            Route::apiResource('languages', LanguageController::class)->except(['index']);
            Route::post('/languages/reorder', [LanguageController::class, 'reorder'])->name('api.v1.languages.reorder');
            Route::post('/languages/compile-all', [LanguageController::class, 'compileAll'])->name('api.v1.languages.compile-all');
            Route::post('/languages/{language}/sync', [LanguageController::class, 'sync'])->name('api.v1.languages.sync');
            Route::get('/languages/{language}/download', [LanguageController::class, 'download'])->name('api.v1.languages.download');
            Route::post('/languages/{language}/upload', [LanguageController::class, 'upload'])->name('api.v1.languages.upload');
            Route::get('/languages/{language}/translations', [LanguageController::class, 'getTranslations'])->name('api.v1.languages.translations');
            Route::put('/languages/{language}/translations', [LanguageController::class, 'updateTranslations'])->name('api.v1.languages.translations.update');
            Route::post('/languages/{language}/compile', [LanguageController::class, 'compileTranslations'])->name('api.v1.languages.compile');
            Route::delete('/languages/{language}/translations/key', [LanguageController::class, 'deleteTranslationKey'])->name('api.v1.languages.translations.delete');

            // Admin menu routes
            Route::get('/admin/menu', [AdminMenuController::class, 'index'])->name('api.v1.admin.menu.index');

            // Topbar menu routes
            Route::get('/topbar/menu', [TopbarMenuController::class, 'index'])->name('api.v1.topbar.menu.index');

            // Profile routes
            Route::get('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'show'])->name('api.v1.profile.show');
            Route::put('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update'])->name('api.v1.profile.update');

            // Settings routes
            Route::get('/settings', [SettingsController::class, 'index'])->name('api.v1.settings.index');
            Route::get('/settings/group/{group}', [SettingsController::class, 'getGroup'])->name('api.v1.settings.group');
            Route::get('/settings/{key}', [SettingsController::class, 'show'])->name('api.v1.settings.show');
            Route::put('/settings', [SettingsController::class, 'update'])->name('api.v1.settings.update');
            Route::put('/settings/group/{group}', [SettingsController::class, 'update'])->name('api.v1.settings.update.group');
            Route::post('/settings/initialize', [SettingsController::class, 'initialize'])->name('api.v1.settings.initialize');

            // Email Settings Routes (Authenticated part)
            Route::get('/email/protocols', [\App\Http\Controllers\Admin\EmailController::class, 'getProtocols'])->name('api.v1.email.protocols');
            Route::post('/email/test', [\App\Http\Controllers\Admin\EmailController::class, 'sendTestEmail'])->name('api.v1.email.test');
            Route::get('/email/oauth/google/redirect', [\App\Http\Controllers\Admin\EmailController::class, 'redirectToGoogle'])->name('api.v1.email.oauth.google.redirect');

            // Theme routes
            Route::get('/themes', [ThemeController::class, 'index'])->name('api.v1.themes.index');
            Route::get('/themes/templates', [ThemeController::class, 'templates'])->name('api.v1.themes.templates');
            Route::get('/themes/{slug}', [ThemeController::class, 'show'])->name('api.v1.themes.show');
            Route::get('/themes/{slug}/templates', [ThemeController::class, 'themeTemplates'])->name('api.v1.themes.theme-templates');
            Route::post('/themes/sync', [ThemeController::class, 'sync'])->name('api.v1.themes.sync');
            Route::post('/themes/upload', [ThemeController::class, 'upload'])->name('api.v1.themes.upload');
            Route::post('/themes/{slug}/activate', [ThemeController::class, 'activate'])->name('api.v1.themes.activate');
            Route::post('/themes/{slug}/set-main', [ThemeController::class, 'setMain'])->name('api.v1.themes.set-main');
            Route::post('/themes/{slug}/activate-sub', [ThemeController::class, 'activateSub'])->name('api.v1.themes.activate-sub');
            Route::post('/themes/{slug}/deactivate-sub', [ThemeController::class, 'deactivateSub'])->name('api.v1.themes.deactivate-sub');
            Route::get('/themes/{slug}/snapshot', [ThemeController::class, 'snapshot'])->name('api.v1.themes.snapshot');
            Route::delete('/themes/{slug}', [ThemeController::class, 'destroy'])->name('api.v1.themes.destroy');

            // Menu routes
            Route::apiResource('menus', MenuController::class);
            Route::post('/menus/{menu}/assign', [MenuController::class, 'assign'])->name('api.v1.menus.assign');
            Route::get('/menus/{menu}/items', [MenuItemController::class, 'index'])->name('api.v1.menus.items.index');
            Route::post('/menus/{menu}/items', [MenuItemController::class, 'store'])->name('api.v1.menus.items.store');
            Route::put('/menus/{menu}/items/reorder', [MenuItemController::class, 'reorder'])->name('api.v1.menus.items.reorder');
            Route::put('/menus/{menu}/items/{menuItem}', [MenuItemController::class, 'update'])->name('api.v1.menus.items.update');
            Route::delete('/menus/{menu}/items/{menuItem}', [MenuItemController::class, 'destroy'])->name('api.v1.menus.items.destroy');

            // Menu content browser routes
            Route::get('/menus/content/posts', [MenuContentController::class, 'posts'])->name('api.v1.menus.content.posts');
            Route::get('/menus/content/pages', [MenuContentController::class, 'pages'])->name('api.v1.menus.content.pages');
            Route::get('/menus/content/categories', [MenuContentController::class, 'categories'])->name('api.v1.menus.content.categories');
            Route::get('/menus/content/products', [MenuContentController::class, 'products'])->name('api.v1.menus.content.products');
            Route::get('/menus/content/tags', [MenuContentController::class, 'tags'])->name('api.v1.menus.content.tags');

            // Content Votes (admin — stats & listing)
            Route::get('/content-votes', [ContentVoteController::class, 'index'])->name('api.v1.content-votes.index');
            Route::get('/content-votes/stats', [ContentVoteController::class, 'stats'])->name('api.v1.content-votes.stats');

            // System Update & Info routes
            Route::prefix('system')->name('api.v1.system.')->group(function () {
                Route::get('/info', [SystemUpdateController::class, 'info'])->name('info');
                Route::get('/check-update', [SystemUpdateController::class, 'checkUpdate'])->name('check-update');
                Route::post('/update/upload', [SystemUpdateController::class, 'upload'])->name('update.upload');
                Route::post('/update/execute', [SystemUpdateController::class, 'execute'])->name('update.execute');
                Route::post('/update/migrate', [SystemUpdateController::class, 'migrate'])->name('update.migrate');
                Route::get('/update/log', [SystemUpdateController::class, 'latestLog'])->name('update.log');
                Route::get('/backups', [SystemUpdateController::class, 'backups'])->name('backups');
                Route::post('/rollback', [SystemUpdateController::class, 'rollback'])->name('rollback');
            });
        });
});

if (file_exists(storage_path('installed.lock'))) {
    $moduleManager = app(ModuleManager::class);

    foreach ($moduleManager->discoverModules() as $module) {
        if (!$module['enabled']) {
            continue;
        }

        $moduleApiRoutes = $module['path'] . '/routes/api.php';

        if (file_exists($moduleApiRoutes)) {
            Route::middleware('api')
                ->group(function () use ($moduleApiRoutes): void {
                    require $moduleApiRoutes;
                });
        }
    }
}
Route::get('/debug-menu', function () { app(\App\Services\MenuRegistry::class)->clear(); app(\App\Services\CoreMenuService::class)->registerCoreMenus(); \App\Facades\Hook::doAction('admin.menu.build'); return response()->json(app(\App\Services\MenuRegistry::class)->all()); });
