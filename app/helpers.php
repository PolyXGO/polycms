<?php

/**
 * Helper functions for PolyCMS
 */

if (!function_exists('theme_asset')) {
    /**
     * Get the URL to a theme asset
     *
     * @param string $path
     * @param string $type
     * @return string
     */
    function theme_asset(string $path, string $type = 'frontend'): string
    {
        $themeManager = app(\App\Services\ThemeManager::class);
        $activeTheme = $themeManager->getActiveTheme($type);
        
        if (!$activeTheme) {
            // Fallback to default assets
            return asset($path);
        }
        
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // Return theme asset URL using route
        return route('theme.asset', ['themeSlug' => $activeTheme->slug, 'path' => $path]);
    }
}
