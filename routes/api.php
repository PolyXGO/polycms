<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\PostTagController;
use App\Http\Controllers\Api\V1\ProductTagController;
use App\Http\Controllers\Api\V1\AdminMenuController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\WidgetAreaController;
use App\Http\Controllers\Api\V1\WidgetController;
use App\Http\Controllers\Api\V1\WidgetInstanceController;
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

        // Media management
                    Route::apiResource('media', MediaController::class);
            Route::post('/media/upload', [MediaController::class, 'upload'])->name('api.v1.media.upload');

            // Widget routes
            Route::get('/widgets/types', [WidgetController::class, 'types'])->name('api.v1.widgets.types');
            Route::get('/widgets/types/{type}', [WidgetController::class, 'show'])->name('api.v1.widgets.type');
            Route::apiResource('widget-areas', WidgetAreaController::class);
            Route::apiResource('widget-instances', WidgetInstanceController::class);

            // Module routes
            Route::get('/modules', [ModuleController::class, 'index'])->name('api.v1.modules.index');
            Route::post('/modules/{moduleKey}/enable', [ModuleController::class, 'enable'])->name('api.v1.modules.enable');
            Route::post('/modules/{moduleKey}/disable', [ModuleController::class, 'disable'])->name('api.v1.modules.disable');
            Route::delete('/modules/{moduleKey}', [ModuleController::class, 'destroy'])->name('api.v1.modules.destroy');

            // Admin menu routes
            Route::get('/admin/menu', [AdminMenuController::class, 'index'])->name('api.v1.admin.menu.index');
        });
});
