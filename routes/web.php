<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PostController as FrontendPostController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\TagController;
use App\Http\Controllers\Frontend\AuthorController;
use App\Http\Controllers\Frontend\SeoEndpointController;
use App\Http\Controllers\Admin\LayoutAssetPreviewController;
use App\Services\SettingsService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/auth.php';
require __DIR__.'/install.php';

$permalinks = [];
if (file_exists(storage_path('installed.lock')) && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
    try {
        $settingsService = app(SettingsService::class);
        $permalinks = $settingsService->getPermalinkStructure();
    } catch (\Exception $e) {
        // Fallback or ignore if DB fails during early boot
    }
}
$postsArchiveBase = trim($permalinks['posts']['archive'] ?? 'posts', '/');
$postsSingleBase = trim($permalinks['posts']['single'] ?? 'posts', '/');
$pagesBase = trim($permalinks['pages']['single'] ?? '', '/');
$productsArchiveBase = trim($permalinks['products']['archive'] ?? 'products', '/');
$productsSingleBase = trim($permalinks['products']['single'] ?? 'products', '/');
$categoryBase = trim($permalinks['categories']['single'] ?? 'categories', '/');
$productCategoryBase = trim($permalinks['product_categories']['single'] ?? 'product-categories', '/');
$productBrandBase = trim($permalinks['product_brands']['single'] ?? 'product-brands', '/');
$postTagBase = trim($permalinks['tags']['post'] ?? 'tags', '/');
$productTagBase = trim($permalinks['tags']['product'] ?? 'product-tags', '/');
$authorBase = trim($permalinks['users']['single'] ?? 'author', '/');

$reservedSlugs = collect([
    'admin',
    'api',
    'themes',
    'profile',
    'account',
    $postsArchiveBase,
    $postsSingleBase,
    $productsArchiveBase,
    $productsSingleBase,
    $categoryBase,
    $productCategoryBase,
    $productBrandBase,
    $postTagBase,
    $productTagBase,
    $authorBase,
])->filter(fn ($value) => $value !== '')->map(fn ($value) => explode('/', $value)[0])->unique()->values();

$pageSlugConstraint = '^(?!' . $reservedSlugs->map(fn ($s) => preg_quote($s, '/'))->implode('$|') . '$)[A-Za-z0-9\-_]+';

// Legacy Redirects
Route::redirect('/profile', '/account/profile');

Route::middleware('auth')->group(function () {
    // Account Dashboard
    // Account Dashboard
    Route::get('/account/dashboard', [\App\Http\Controllers\Account\DashboardController::class, 'index'])->name('dashboard');

    // Account Profile
    Route::get('/account/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/account/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/account/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Account Resources
    Route::get('/account/orders', [\App\Http\Controllers\Frontend\AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{code}', [\App\Http\Controllers\Frontend\AccountController::class, 'orderDetail'])->name('account.orders.show');
    Route::get('/account/subscriptions', [\App\Http\Controllers\Frontend\AccountController::class, 'subscriptions'])->name('account.subscriptions');
    Route::get('/account/licenses', [\App\Http\Controllers\Frontend\AccountController::class, 'licenses'])->name('account.licenses');
    Route::post('/account/licenses/deactivate/{id}', [\App\Http\Controllers\Frontend\AccountController::class, 'deactivateLicense'])->name('account.licenses.deactivate');
});

// Authenticated Checkout Routes (Hybrid) - Move to web middleware to access Session
Route::prefix('api/v1')->group(function () {
    Route::get('/checkout/coupons', [\App\Http\Controllers\Api\V1\CheckoutController::class, 'getAvailableCoupons'])->name('api.v1.checkout.coupons');
    Route::post('/checkout/calculate', [\App\Http\Controllers\Api\V1\CheckoutController::class, 'calculate'])->name('api.v1.checkout.calculate');
    Route::get('/checkout/coupons', [\App\Http\Controllers\Api\V1\CheckoutController::class, 'getAvailableCoupons'])->name('api.v1.checkout.coupons');
    Route::post('/checkout/calculate', [\App\Http\Controllers\Api\V1\CheckoutController::class, 'calculate'])->name('api.v1.checkout.calculate');
    Route::post('/checkout/process', [\App\Http\Controllers\Api\V1\CheckoutController::class, 'process'])->name('api.v1.checkout.process');
    
    // Cart API (session-based for guests, auth-based for users)
    Route::prefix('cart')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\CartApiController::class, 'show'])->name('api.v1.cart.show');
        Route::post('/items', [\App\Http\Controllers\Api\V1\CartApiController::class, 'addItem'])->name('api.v1.cart.add');
        Route::put('/items/{itemId}', [\App\Http\Controllers\Api\V1\CartApiController::class, 'updateItem'])->name('api.v1.cart.update');
        Route::delete('/items/{itemId}', [\App\Http\Controllers\Api\V1\CartApiController::class, 'removeItem'])->name('api.v1.cart.remove');
        Route::delete('/', [\App\Http\Controllers\Api\V1\CartApiController::class, 'clear'])->name('api.v1.cart.clear');
        Route::get('/count', [\App\Http\Controllers\Api\V1\CartApiController::class, 'count'])->name('api.v1.cart.count');
    });

    // Wishlist API (requires auth)
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\WishlistController::class, 'index'])->name('api.v1.wishlist.index');
        Route::post('/toggle', [\App\Http\Controllers\Api\V1\WishlistController::class, 'toggle'])->name('api.v1.wishlist.toggle');
        Route::get('/check/{productId}', [\App\Http\Controllers\Api\V1\WishlistController::class, 'check'])->name('api.v1.wishlist.check');
    });

    // Product Reviews API
    Route::get('products/{productId}/reviews', [\App\Http\Controllers\Api\V1\ReviewController::class, 'index'])->name('api.v1.reviews.index');
    Route::post('products/{productId}/reviews', [\App\Http\Controllers\Api\V1\ReviewController::class, 'store'])->name('api.v1.reviews.store');

    // Admin Reviews (moderation)
    Route::get('reviews', [\App\Http\Controllers\Api\V1\ReviewController::class, 'adminIndex'])->name('api.v1.reviews.admin');
    Route::post('reviews/{reviewId}/approve', [\App\Http\Controllers\Api\V1\ReviewController::class, 'approve'])->name('api.v1.reviews.approve');
    Route::post('reviews/{reviewId}/reject', [\App\Http\Controllers\Api\V1\ReviewController::class, 'reject'])->name('api.v1.reviews.reject');

    // Shipping Methods (for checkout)
    Route::post('/shipping/methods', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'country' => 'required|string|size:2',
            'province' => 'nullable|string|max:100',
        ]);
        $calculator = app(\App\Services\Ecommerce\ShippingCalculator::class);
        $cart = app(\App\Services\Ecommerce\CartService::class)->getCart($request);
        $methods = $calculator->getAvailableMethods($validated, $cart);
        return response()->json(['methods' => $methods]);
    })->name('api.v1.shipping.methods');

    // Tax Calculation (for checkout)
    Route::post('/tax/calculate', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'subtotal' => 'required|numeric|min:0',
            'country' => 'required|string|size:2',
            'province' => 'nullable|string|max:100',
        ]);
        $calculator = app(\App\Services\Ecommerce\TaxCalculator::class);
        $result = $calculator->calculate(
            (float) $validated['subtotal'],
            ['country' => $validated['country'], 'province' => $validated['province'] ?? '']
        );
        return response()->json($result);
    })->name('api.v1.tax.calculate');

    // Dashboard Stats API
    Route::get('dashboard/stats', [\App\Http\Controllers\Api\V1\DashboardStatsController::class, 'index'])->name('api.v1.dashboard.stats');

    // Global Search API
    Route::get('search', [\App\Http\Controllers\Api\V1\GlobalSearchController::class, 'search'])->name('api.v1.search');

    Route::middleware('auth:sanctum')->group(function () {
        // Invoice API
        Route::get('invoices', [\App\Http\Controllers\Api\V1\OrderInvoiceController::class, 'getAllInvoices'])->name('api.v1.invoices.all');
        Route::get('orders/{orderId}/invoices', [\App\Http\Controllers\Api\V1\OrderInvoiceController::class, 'index'])->name('api.v1.orders.invoices.index');
        Route::post('orders/{orderId}/invoices', [\App\Http\Controllers\Api\V1\OrderInvoiceController::class, 'store'])->name('api.v1.orders.invoices.store');
        Route::get('invoices/{invoiceId}/download', [\App\Http\Controllers\Api\V1\OrderInvoiceController::class, 'download'])->name('api.v1.invoices.download');
        Route::patch('invoices/{invoiceId}/void', [\App\Http\Controllers\Api\V1\OrderInvoiceController::class, 'voidInvoice'])->name('api.v1.invoices.void');

        // Admin Orders (web session access)
        Route::apiResource('orders', \App\Http\Controllers\Api\V1\OrderController::class)->except(['store', 'destroy']);
        Route::post('orders/{id}/refund/preview', [\App\Http\Controllers\Api\V1\OrderController::class, 'previewRefund'])->name('api.v1.orders.refund.preview');
        Route::post('orders/{id}/refund', [\App\Http\Controllers\Api\V1\OrderController::class, 'refund'])->name('api.v1.orders.refund');
        Route::get('orders/{id}/stock-movements', [\App\Http\Controllers\Api\V1\OrderController::class, 'stockMovements'])->name('api.v1.orders.stock-movements');
    });
});

// Standard SEO endpoints
Route::get('/robots.txt', [SeoEndpointController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoEndpointController::class, 'sitemapIndex'])->name('seo.sitemap');
Route::get('/sitemap-index.xml', [SeoEndpointController::class, 'sitemapIndex'])->name('seo.sitemap-index');
Route::get('/sitemap-{type}-page-{page}.xml', [SeoEndpointController::class, 'sitemap'])
    ->where('type', '[A-Za-z0-9\\-]+')
    ->where('page', '[0-9]+')
    ->name('seo.sitemap.type.page');
Route::get('/sitemap-{type}.xml', [SeoEndpointController::class, 'sitemap'])
    ->where('type', '[A-Za-z0-9\\-]+')
    ->name('seo.sitemap.type');

// Frontend routes - Theme-based rendering
Route::get('/', [HomeController::class, 'index'])->name('home');

if ($postsArchiveBase !== '') {
    Route::get('/' . $postsArchiveBase, [FrontendPostController::class, 'index'])->name('posts.index');
}

if ($postsSingleBase !== '') {
    Route::get('/' . $postsSingleBase . '/{slug}', [FrontendPostController::class, 'show'])
        ->where('slug', '^[^/]+$')
        ->name('posts.show');
}

if ($productsArchiveBase !== '') {
    Route::get('/' . $productsArchiveBase, [FrontendProductController::class, 'index'])->name('products.index');
}

Route::get('/' . $productsSingleBase . '/{slug}', [FrontendProductController::class, 'show'])
    ->where('slug', '^[^/]+$')
    ->name('products.show');

Route::get('/cart', [\App\Http\Controllers\Frontend\CartController::class, 'index'])->name('cart');
Route::get('/checkout', [\App\Http\Controllers\Frontend\CheckoutController::class, 'index'])->name('checkout');
Route::get('/checkout/success/{code}', [\App\Http\Controllers\Frontend\CheckoutController::class, 'success'])->name('checkout.success');

// Allow themes/modules to register custom frontend routes via Hook
// Each theme can hook into 'routes.frontend.register' in its functions.php
\App\Facades\Hook::doAction('routes.frontend.register');


Route::get('/' . $categoryBase . '/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-_/]+')
    ->name('categories.show');

if ($postTagBase !== '') {
    Route::get('/' . $postTagBase . '/{slug}', [TagController::class, 'showPost'])
        ->where('slug', '[A-Za-z0-9\-_/]+')
        ->name('tags.show');
}

if ($productTagBase !== '') {
    Route::get('/' . $productTagBase . '/{slug}', [TagController::class, 'showProduct'])
        ->where('slug', '[A-Za-z0-9\-_/]+')
        ->name('product-tags.show');
}

if ($authorBase !== '') {
    Route::get('/' . $authorBase . '/{user}', [AuthorController::class, 'show'])
        ->where('user', '[A-Za-z0-9\-_/]+')
        ->name('authors.show');
}

if ($productCategoryBase !== '') {
    Route::get('/' . $productCategoryBase . '/{slug}', [CategoryController::class, 'showProductCategory'])
        ->where('slug', '[A-Za-z0-9\-_/]+')
        ->name('product-categories.show');
}

if ($productBrandBase !== '') {
    Route::get('/' . $productBrandBase . '/{slug}', [\App\Http\Controllers\Frontend\BrandController::class, 'show'])
        ->where('slug', '[A-Za-z0-9\-_/]+')
        ->name('product-brands.show');
}

// Universal fallback catch-all route for pages (and posts when postsSingleBase is empty)
if ($pagesBase !== '') {
    Route::get('/' . $pagesBase . '/{slug}', [PageController::class, 'show'])
        ->where('slug', '[A-Za-z0-9\-_/]+')
        ->name('pages.show');
} else {
    // Universal fallback: serves both pages and posts (when postsSingleBase is empty)
    // Posts use $post->frontend_url for URL generation, not route('posts.show')
    Route::get('/{slug}', [PageController::class, 'show'])
        ->where('slug', $pageSlugConstraint)
        ->name('pages.show');
}







// Theme assets route - serve theme public files
Route::get('/themes/{themeSlug}/{path}', function (string $themeSlug, string $path) {
    $themePath = base_path("themes/{$themeSlug}/public/{$path}");
    
    if (!file_exists($themePath) || !is_file($themePath)) {
        abort(404);
    }
    
    // Security: prevent directory traversal
    $realPath = realpath($themePath);
    $themePublicPath = realpath(base_path("themes/{$themeSlug}/public"));
    
    if (!$realPath || strpos($realPath, $themePublicPath) !== 0) {
        abort(404);
    }
    
    // Determine MIME type based on file extension
    $extension = pathinfo($themePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
    ];
    
    $mimeType = $mimeTypes[$extension] ?? mime_content_type($themePath) ?? 'application/octet-stream';
    $content = file_get_contents($themePath);
    
    return response($content, 200)
        ->header('Content-Type', $mimeType)
        ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
})->where('path', '.*')->name('theme.asset');

// Admin SPA routes - Vue Router handles all routing and authentication
Route::middleware(['web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/appearance/layout-assets/{layoutAsset}/preview', [LayoutAssetPreviewController::class, 'show'])
        ->name('appearance.layout-assets.preview');

    Route::get('/{any?}', function () {
        return view('admin.app');
    })->where('any', '.*')->name('dashboard');
});
