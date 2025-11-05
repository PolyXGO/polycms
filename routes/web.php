<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

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
    
    $mimeType = mime_content_type($themePath);
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
