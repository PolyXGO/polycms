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
use App\Http\Controllers\Api\V1\ShowcasePackageController;
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
use App\Http\Controllers\Api\V1\CacheController;
use App\Http\Controllers\Api\V1\CacheSettingsController;
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
    Route::get('/products/preview-videos', [ProductController::class, 'previewVideos'])->middleware('auth:sanctum')->name('api.v1.products.preview-videos');
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

    // Public contact submission
    Route::post('/public/contacts/submit', [\App\Http\Controllers\Api\V1\PublicContactController::class, 'submit'])->name('api.v1.public.contacts.submit');

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

    // Public License Key Domain Activation APIs
    Route::post('/licenses/activate', [\App\Http\Controllers\Api\V1\LicenseController::class, 'activatePublic'])->name('api.v1.licenses.activate-public');
    Route::post('/licenses/verify', [\App\Http\Controllers\Api\V1\LicenseController::class, 'verifyPublic'])->name('api.v1.licenses.verify-public');
    Route::post('/licenses/deactivate', [\App\Http\Controllers\Api\V1\LicenseController::class, 'deactivatePublic'])->name('api.v1.licenses.deactivate-public');
    Route::match(['get', 'post'], '/licenses/check-update', [\App\Http\Controllers\Api\V1\LicenseController::class, 'checkUpdatePublic'])->name('api.v1.licenses.check-update-public');
    Route::match(['get', 'post'], '/licenses/download-release', [\App\Http\Controllers\Api\V1\LicenseController::class, 'downloadReleasePublic'])->name('api.v1.licenses.download-release-public');

    // Module frontend manifest (public — needed at admin SPA bootstrap before auth)
    Route::get('/modules/active-frontend', [ModuleController::class, 'activeFrontend'])->name('api.v1.modules.active-frontend');


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

    // Public locations API
    Route::get('locations/countries', [LocationController::class, 'countries']);

    // Protected API routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {

        // Posts CRUD
        Route::post('posts/{post}/translate', [PostController::class, 'translate'])->name('api.v1.posts.translate');
        Route::post('posts/{id}/restore', [PostController::class, 'restore'])->name('api.v1.posts.restore');
        Route::delete('posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('api.v1.posts.force-delete');
        Route::post('posts/bulk-delete', [PostController::class, 'bulkDestroy'])->name('api.v1.posts.bulk-delete');
        Route::post('posts/bulk-restore', [PostController::class, 'bulkRestore'])->name('api.v1.posts.bulk-restore');
        Route::post('posts/bulk-force-delete', [PostController::class, 'bulkForceDelete'])->name('api.v1.posts.bulk-force-delete');
        Route::apiResource('posts', PostController::class)->except(['index', 'show']);

        // Products CRUD
        Route::post('products/{product}/translate', [ProductController::class, 'translate'])->name('api.v1.products.translate');
        Route::post('products/{id}/restore', [ProductController::class, 'restore'])->name('api.v1.products.restore');
        Route::delete('products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('api.v1.products.force-delete');
        Route::post('products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('api.v1.products.bulk-delete');
        Route::post('products/bulk-restore', [ProductController::class, 'bulkRestore'])->name('api.v1.products.bulk-restore');
        Route::post('products/bulk-force-delete', [ProductController::class, 'bulkForceDelete'])->name('api.v1.products.bulk-force-delete');
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Categories CRUD
        Route::post('categories/{category}/translate', [CategoryController::class, 'translate'])->name('api.v1.categories.translate');
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

        // Tags CRUD (deprecated - use post-tags and product-tags instead)
        Route::post('tags/{tag}/translate', [TagController::class, 'translate'])->name('api.v1.tags.translate');
        Route::apiResource('tags', TagController::class)->except(['index', 'show']);

        // Post Tags CRUD
        Route::post('post-tags/{postTag}/translate', [PostTagController::class, 'translate'])->name('api.v1.post-tags.translate');
        Route::apiResource('post-tags', PostTagController::class)->except(['index', 'show']);

        // Product Tags CRUD
        Route::post('product-tags/{productTag}/translate', [ProductTagController::class, 'translate'])->name('api.v1.product-tags.translate');
        Route::apiResource('product-tags', ProductTagController::class)->except(['index', 'show']);

        // Product Categories CRUD
        Route::post('product-categories/{productCategory}/translate', [ProductCategoryController::class, 'translate'])->name('api.v1.product-categories.translate');
        Route::apiResource('product-categories', ProductCategoryController::class)->except(['index', 'show']);

        // Capability Presets CRUD
        Route::apiResource('capability-presets', \App\Http\Controllers\Api\V1\Admin\CapabilityPresetController::class);

        // Product Brands CRUD
        Route::post('product-brands/{productBrand}/translate', [ProductBrandController::class, 'translate'])->name('api.v1.product-brands.translate');
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
        Route::post('/layout-assets/{layout_asset}/thumbnail', [LayoutAssetController::class, 'uploadThumbnail'])->name('api.v1.layout-assets.thumbnail');
        Route::apiResource('layout-assets', LayoutAssetController::class);
        
        // E-commerce Routes
        Route::name('admin.')->group(function () {
            Route::get('admin/users/search', [UserController::class, 'search'])->name('users.search');
             // Moved orders to web.php for session access
            // Route::apiResource('orders', OrderController::class)->except(['store', 'destroy']);
            Route::apiResource('coupons', CouponController::class);
            Route::apiResource('subscriptions', \App\Http\Controllers\Api\V1\SubscriptionController::class)->only(['index', 'show']);
            Route::apiResource('licenses', \App\Http\Controllers\Api\V1\LicenseController::class)->only(['index', 'show', 'update']);
            Route::delete('licenses/{license}/activations/{activation}', [\App\Http\Controllers\Api\V1\LicenseController::class, 'deleteActivation'])->name('licenses.activations.destroy');
            Route::apiResource('shipping-zones', ShippingZoneController::class);
            Route::apiResource('tax-rates', TaxRateController::class);
            Route::post('settings/ecommerce/currencies/sync', [\App\Http\Controllers\Api\V1\CurrencySyncController::class, 'sync'])->name('settings.ecommerce.currencies.sync');
            
            // Payments section
            Route::get('transactions', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'index'])->name('transactions.index');
            Route::get('transactions/stats', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'stats'])->name('transactions.stats');
            Route::get('transactions/{id}', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'show'])->name('transactions.show');
            Route::patch('transactions/{id}/status', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'updateStatus'])->name('transactions.update-status');
            Route::post('transactions/{id}/status', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'updateStatus']);
            Route::get('transactions/{id}/proof', [\App\Http\Controllers\Api\Admin\TransactionController::class, 'serveProof'])->name('transactions.proof');
            Route::get('payment-logs', [\App\Http\Controllers\Api\Admin\PaymentLogController::class, 'index'])->name('payment-logs.index');
            Route::delete('payment-logs/clear', [\App\Http\Controllers\Api\Admin\PaymentLogController::class, 'clear'])->name('payment-logs.clear');
            
            // Settings Hub Extensions
            Route::apiResource('email-templates', EmailTemplateController::class);
            Route::post('email-templates/{id}/preview', [EmailTemplateController::class, 'preview'])->name('admin.email-templates.preview');
            Route::apiResource('payment-gateways', PaymentGatewayController::class);
            Route::post('payment-gateways/reorder', [PaymentGatewayController::class, 'reorder'])->name('admin.payment-gateways.reorder');
            Route::post('payment-gateways/{code}/set-default', [PaymentGatewayController::class, 'setDefault'])->name('admin.payment-gateways.set-default');
            Route::post('payment-gateways/paypal/test-connection', [PaymentGatewayController::class, 'testPaypalConnection'])->name('admin.payment-gateways.paypal.test-connection');
            Route::get('products/{id}/stock-movements', [ProductInventoryController::class, 'stockMovements'])->name('admin.products.stock-movements');
        });
        


        // Media management — folder routes MUST come before apiResource
        // to prevent DELETE /media/{media} from catching /media/folders
        Route::post('/media/folders', [MediaController::class, 'createFolder'])->name('api.v1.media.folders.create');
        Route::put('/media/folders/rename', [MediaController::class, 'renameFolder'])->name('api.v1.media.folders.rename');
        Route::delete('/media/folders', [MediaController::class, 'deleteFolder'])->name('api.v1.media.folders.delete');
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('api.v1.media.upload');
        Route::post('/media/upload-from-url', [MediaController::class, 'uploadFromUrl'])->name('api.v1.media.upload-from-url');
        Route::post('/media/regenerate-thumbnails', [MediaController::class, 'regenerateThumbnails'])->name('api.v1.media.regenerate-thumbnails');
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
            Route::post('/modules/check-updates', [ModuleController::class, 'checkUpdates'])->name('api.v1.modules.check-updates');
            Route::post('/modules/update/execute', [ModuleController::class, 'executeModuleUpdate'])->name('api.v1.modules.update.execute');
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

            // Core Presets
            Route::get('/presets', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'index'])->name('api.v1.presets.index');
            Route::post('/presets', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'store'])->name('api.v1.presets.store');
            Route::put('/presets/{preset}', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'update'])->name('api.v1.presets.update');
            Route::delete('/presets/{preset}', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'destroy'])->name('api.v1.presets.destroy');
            Route::get('/presets/categories', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'categories'])->name('api.v1.presets.categories');
            Route::post('/presets/categories', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'storeCategory'])->name('api.v1.presets.categories.store');
            Route::put('/presets/categories/{category}', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'updateCategory'])->name('api.v1.presets.categories.update');
            Route::delete('/presets/categories/{category}', [\App\Http\Controllers\Api\Admin\CorePresetController::class, 'destroyCategory'])->name('api.v1.presets.categories.destroy');

            // Topbar menu routes
            Route::get('/topbar/menu', [TopbarMenuController::class, 'index'])->name('api.v1.topbar.menu.index');

            // Profile routes
            Route::get('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'show'])->name('api.v1.profile.show');
            Route::put('/profile', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update'])->name('api.v1.profile.update');

            // Settings routes
            Route::get('/settings/hub', [SettingsController::class, 'getSettingsHub'])->name('api.v1.settings.hub');
            Route::get('/settings', [SettingsController::class, 'index'])->name('api.v1.settings.index');
            Route::get('/settings/group/{group}', [SettingsController::class, 'getGroup'])->name('api.v1.settings.group');
            Route::get('/settings/{key}', [SettingsController::class, 'show'])->name('api.v1.settings.show');
            Route::put('/settings', [SettingsController::class, 'update'])->name('api.v1.settings.update');
            Route::put('/settings/group/{group}', [SettingsController::class, 'update'])->name('api.v1.settings.update.group');
            Route::post('/settings/initialize', [SettingsController::class, 'initialize'])->name('api.v1.settings.initialize');

            // Custom Icons CRUD routes
            Route::apiResource('custom-icons', \App\Http\Controllers\Api\V1\CustomIconController::class);

            // Email Settings Routes (Authenticated part)
            Route::get('/email/protocols', [\App\Http\Controllers\Admin\EmailController::class, 'getProtocols'])->name('api.v1.email.protocols');
            Route::post('/email/test', [\App\Http\Controllers\Admin\EmailController::class, 'sendTestEmail'])->name('api.v1.email.test');
            Route::get('/email/oauth/google/redirect', [\App\Http\Controllers\Admin\EmailController::class, 'redirectToGoogle'])->name('api.v1.email.oauth.google.redirect');

            // Theme routes
            Route::get('/themes', [ThemeController::class, 'index'])->name('api.v1.themes.index');
            Route::get('/themes/templates', [ThemeController::class, 'templates'])->name('api.v1.themes.templates');
            Route::post('/themes/switch-template', [ThemeController::class, 'switchTemplate'])->name('api.v1.themes.switch-template');
            Route::get('/themes/{slug}/download', [ThemeController::class, 'download'])->name('api.v1.themes.download');
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

            // Register dynamic api routes (e.g. from themes or modules)
            \App\Facades\Hook::doAction('routes.api.register');

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
                Route::delete('/backups', [SystemUpdateController::class, 'cleanBackups'])->name('backups.clean');
                Route::post('/rollback', [SystemUpdateController::class, 'rollback'])->name('rollback');

                // Cache Management
                Route::get('/cache/status', [CacheController::class, 'status'])->name('cache.status');
                Route::get('/cache/routes', [CacheController::class, 'routes'])->name('cache.routes');
                Route::get('/cache/detail/{type}', [CacheController::class, 'detail'])->name('cache.detail');
                Route::post('/cache/clear', [CacheController::class, 'clear'])->name('cache.clear');
                Route::post('/cache/fix-permissions', [CacheController::class, 'fixPermissions'])->name('cache.fix-permissions');
                Route::post('/cache/test-redis', [CacheSettingsController::class, 'testRedisConnection'])->name('cache.test-redis');
            });

            // Contacts & Forms CRUD
            Route::prefix('contacts')->name('api.v1.contacts.')->group(function () {
                Route::get('/submissions', [\App\Http\Controllers\Api\V1\ContactSubmissionController::class, 'index'])->name('submissions.index');
                Route::put('/submissions/{id}/status', [\App\Http\Controllers\Api\V1\ContactSubmissionController::class, 'updateStatus'])->name('submissions.update-status');
                Route::delete('/submissions/{id}', [\App\Http\Controllers\Api\V1\ContactSubmissionController::class, 'destroy'])->name('submissions.destroy');
                Route::get('/reports', [\App\Http\Controllers\Api\V1\ContactSubmissionController::class, 'reports'])->name('reports');
                Route::apiResource('forms', \App\Http\Controllers\Api\V1\ContactFormController::class);
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
