<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider;

use App\Facades\Hook;
use App\Services\MenuRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class BannerSliderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations from module
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Load views from module
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'banner-slider');

        // Publish module assets (CSS/JS) to public directory
        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('modules/banner-slider/css'),
            __DIR__ . '/../resources/js'  => public_path('modules/banner-slider/js'),
        ], 'banner-slider-assets');

        // Auto-publish assets if they don't exist yet (first activation)
        $this->autoPublishAssets();

        // Auto-run migrations if table doesn't exist
        if (file_exists(storage_path('installed.lock'))) {
            $this->runMigrationsIfNeeded();
        }

        // Register admin menu items
        Hook::addAction('admin.menu.build', function () {
            $menuRegistry = app(MenuRegistry::class);

            $menuRegistry->register('banner-slider', [
                'key' => 'banner-slider',
                'label' => 'Banner Slider',
                'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                'order' => 100,
                'children' => [
                    [
                        'key' => 'banner-slider-banners',
                        'label' => 'Banners',
                        'route' => 'admin.banner-slider.banners',
                        'icon' => null,
                        'order' => 1,
                    ],
                    [
                        'key' => 'banner-slider-settings',
                        'label' => 'Settings',
                        'route' => 'admin.banner-slider.settings',
                        'icon' => null,
                        'order' => 2,
                    ],
                ],
            ]);
        }, 10);

        // Register frontend hook filter for banner rendering
        Hook::addFilter('frontend.topbar.banners', function ($banners) {
            $bannerService = app(\Modules\Polyx\BannerSlider\Services\BannerService::class);
            return $bannerService->getBannersForTopbar();
        }, 10);

        // Register routes
        $this->loadRoutes();
    }

    /**
     * Run migrations automatically if table doesn't exist
     */
    protected function runMigrationsIfNeeded(): void
    {
        // Only run in non-console environment or when explicitly needed
        // Check if migrations table exists (Laravel is set up)
        if (!Schema::hasTable('migrations')) {
            return;
        }

        // Check if banner_sliders table exists
        if (!Schema::hasTable('banner_sliders')) {
            try {
                // Get the migration path relative to base_path
                $migrationPath = str_replace(base_path() . '/', '', __DIR__ . '/database/migrations');

                // Run migrations silently
                Artisan::call('migrate', [
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            } catch (\Exception $e) {
                // Log error but don't break the application
                \Log::warning('Banner Slider module: Failed to auto-run migrations', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Auto-publish module assets if they don't exist in public directory
     */
    protected function autoPublishAssets(): void
    {
        $targetCss = public_path('modules/banner-slider/css/banner-slider.css');
        $targetJs  = public_path('modules/banner-slider/js/banner-slider.js');

        // Only publish if at least one target file is missing
        if (!file_exists($targetCss) || !file_exists($targetJs)) {
            try {
                $cssDir = public_path('modules/banner-slider/css');
                $jsDir  = public_path('modules/banner-slider/js');

                if (!is_dir($cssDir)) {
                    mkdir($cssDir, 0755, true);
                }
                if (!is_dir($jsDir)) {
                    mkdir($jsDir, 0755, true);
                }

                // Copy CSS files
                $sourceCssDir = __DIR__ . '/../resources/css';
                if (is_dir($sourceCssDir)) {
                    foreach (glob($sourceCssDir . '/*.css') as $file) {
                        copy($file, $cssDir . '/' . basename($file));
                    }
                }

                // Copy JS files
                $sourceJsDir = __DIR__ . '/../resources/js';
                if (is_dir($sourceJsDir)) {
                    foreach (glob($sourceJsDir . '/*.js') as $file) {
                        copy($file, $jsDir . '/' . basename($file));
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Banner Slider module: Failed to auto-publish assets', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Load module routes
     */
    protected function loadRoutes(): void
    {
        // Register route model binding
        Route::bind('banner', function ($value) {
            return \Modules\Polyx\BannerSlider\Models\BannerSlider::findOrFail($value);
        });

        // Register API routes
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1/banner-slider')
            ->name('api.v1.banner-slider.')
            ->group(function () {
                Route::get('/banners', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'index'])->name('banners.index');
                Route::post('/banners', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'store'])->name('banners.store');
                Route::get('/banners/{banner}', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'show'])->name('banners.show');
                Route::put('/banners/{banner}', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'update'])->name('banners.update');
                Route::delete('/banners/{banner}', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'destroy'])->name('banners.destroy');
                Route::post('/banners/reorder', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'reorder'])->name('banners.reorder');
                Route::post('/banners/{banner}/toggle-active', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'toggleActive'])->name('banners.toggle-active');
                Route::post('/banners/{banner}/duplicate', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\BannerController::class, 'duplicate'])->name('banners.duplicate');

                // Settings routes
                Route::get('/settings', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\SettingsController::class, 'index'])->name('settings.index');
                Route::put('/settings', [\Modules\Polyx\BannerSlider\Http\Controllers\Api\V1\SettingsController::class, 'update'])->name('settings.update');
            });
    }
}
