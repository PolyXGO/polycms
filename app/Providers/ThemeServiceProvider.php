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
        // Skip during migration
        if (app()->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            if (collect($argv)->contains(fn($arg) => str_contains($arg, 'migrate'))) {
                return;
            }
        }

        $themeManager = app(ThemeManager::class);

        // Register view paths for active theme
        $this->registerThemeViews($themeManager);
    }

    /**
     * Register theme view paths for multi-theme support.
     *
     * - Main Theme views: prepended location (default fallback)
     * - All active themes: registered as 'theme.{slug}' namespace
     * - Only Main Theme functions.php loaded at boot
     * - Sub Theme functions.php lazy-loaded by TemplateResolver
     */
    protected function registerThemeViews(ThemeManager $themeManager): void
    {
        try {
            // Check if themes table exists (might not exist during initial setup)
            if (!\Illuminate\Support\Facades\Schema::hasTable('themes')) {
                return;
            }

            // Get all active themes (Main + Subs)
            $allActiveThemes = $themeManager->getAllActiveThemes();
            $mainTheme = null;

            foreach ($allActiveThemes as $theme) {
                $viewsPath = $theme->full_path . '/resources/views';

                if (!is_dir($viewsPath)) {
                    continue;
                }

                // Register every active theme as a named namespace: theme.{slug}
                View::addNamespace("theme.{$theme->slug}", [$viewsPath]);

                if ($theme->isMain()) {
                    $mainTheme = $theme;

                    // Main Theme views are prepended — they become the default fallback
                    View::getFinder()->prependLocation($viewsPath);

                    // Also register generic 'theme' namespace pointing to Main
                    View::addNamespace('theme', [$viewsPath]);
                }
            }

            // Load ALL active themes (Main + Subs) functions.php at boot
            // Essential: Sub-themes must be able to hook into Theme Options, Permalinks, and Global APIs
            foreach ($allActiveThemes as $theme) {
                $functionsPath = $theme->full_path . '/functions.php';
                if (file_exists($functionsPath)) {
                    require_once $functionsPath;
                }
            }

            // Trigger hook for Main Theme activation
            if ($mainTheme) {
                \App\Facades\Hook::doAction('theme.activated', $mainTheme);
            }
        } catch (\Exception $e) {
            // Silently fail if themes table doesn't exist yet
            // This can happen during initial setup or migrations
        }
    }

}