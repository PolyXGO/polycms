<?php

namespace App\Providers;

use App\Services\ThemeManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Theme Manager as singleton
        $this->app->singleton(ThemeManager::class, function ($app) {
            return new ThemeManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $themeManager = app(ThemeManager::class);

        // Register view paths for active theme
        $this->registerThemeViews($themeManager);
    }

    /**
     * Register theme view paths
     */
    protected function registerThemeViews(ThemeManager $themeManager): void
    {
        try {
            // Check if themes table exists (might not exist during initial setup)
            if (!\Illuminate\Support\Facades\Schema::hasTable('themes')) {
                return;
            }

            // Get view paths for frontend theme
            $viewPaths = $themeManager->getViewPaths('frontend');

            // Register view namespace for theme
            // Views can be accessed as 'theme::view-name' or just 'view-name' (Laravel will check theme first)
            if (!empty($viewPaths)) {
                // Add theme views as additional paths (Laravel will check them in order)
                View::getFinder()->prependLocation($viewPaths[0]);

                // Also register as namespace for explicit theme view access
                View::addNamespace('theme', $viewPaths);
            }

            // Load theme functions.php if exists
            $activeTheme = $themeManager->getActiveTheme('frontend');
            if ($activeTheme) {
                $functionsPath = $activeTheme->full_path . '/functions.php';
                if (file_exists($functionsPath)) {
                    require_once $functionsPath;
                }
                
                // Trigger hook for theme activation
                \App\Facades\Hook::doAction('theme.activated', $activeTheme);
            }
        } catch (\Exception $e) {
            // Silently fail if themes table doesn't exist yet
            // This can happen during initial setup or migrations
        }
    }

}