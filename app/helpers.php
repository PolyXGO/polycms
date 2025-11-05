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

if (!function_exists('_l')) {
    /**
     * Global helper function _l() - Similar to WordPress __()
     * 
     * @param string $text The text to translate
     * @param string|null $locale Optional locale override
     * @return string Translated text
     */
    function _l(string $text, ?string $locale = null): string
    {
        return \App\Helpers\LanguageHelper::translate($text, $locale);
    }
}

if (!function_exists('_e')) {
    /**
     * Global helper function _e() - Similar to WordPress _e()
     * 
     * @param string $text The text to translate and echo
     * @param string|null $locale Optional locale override
     */
    function _e(string $text, ?string $locale = null): void
    {
        \App\Helpers\LanguageHelper::echo($text, $locale);
    }
}
