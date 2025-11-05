<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PostController as FrontendPostController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\PageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Frontend routes - Theme-based rendering
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/posts', [FrontendPostController::class, 'index'])->name('posts.index');
Route::get('/posts/{slug}', [FrontendPostController::class, 'show'])->name('posts.show');
Route::get('/products', [FrontendProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [FrontendProductController::class, 'show'])->name('products.show');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show')->where('slug', '^(?!admin|api|themes).*');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

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
    Route::get('/{any?}', function () {
        return view('admin.app');
    })->where('any', '.*')->name('dashboard');
});
