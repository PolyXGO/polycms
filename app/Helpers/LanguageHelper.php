<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Services\SettingsService;
use App\Services\ModuleManager;
use App\Services\ThemeManager;
use Illuminate\Support\Facades\App;

/**
 * Language Helper - Provides translation functions similar to WordPress
 * 
 * Usage:
 *   _l('Hello World') - Returns translated string
 *   _l('Hello World', 'en') - Returns translation for specific language
 */
class LanguageHelper
{
    protected static ?SettingsService $settingsService = null;
    protected static ?string $currentLanguage = null;
    protected static string $direction = 'ltr';
    protected static array $translations = [];

    /**
     * Initialize the language helper
     */
    public static function init(?SettingsService $settingsService = null): void
    {
        if (self::$settingsService === null) {
            self::$settingsService = $settingsService ?? app(SettingsService::class);
        }

        // Load current language from settings
        $lang = self::$settingsService->get('site_language', 'en');
        $dir = self::$settingsService->get('site_language_direction', 'ltr');
        
        // Try to validate against database if table exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                 $activeLang = \App\Models\Language::where('code', $lang)->where('is_active', true)->first();
                 if (!$activeLang) {
                     // Fallback to default
                     $default = \App\Models\Language::where('is_default', true)->first();
                     if ($default) {
                         $lang = $default->code;
                         $dir = $default->direction;
                     }
                 } else {
                     $dir = $activeLang->direction;
                 }
            }
        } catch (\Exception $e) {
            // Fallback to settings
        }

        self::$currentLanguage = $lang;
        self::$direction = $dir;

        // Load translations
        self::loadTranslations();
    }

    /**
     * Translate a string (similar to WordPress __())
     * 
     * @param string $text The text to translate
     * @param array|string|null $replace Optional replacement array or locale override
     * @param string|null $locale Optional locale override if $replace is an array
     * @return string Translated text or original if translation not found
     */
    public static function translate(string $text, array|string|null $replace = null, ?string $locale = null): string
    {
        if (is_string($replace)) {
            $locale = $replace;
            $replace = null;
        }

        if (self::$settingsService === null) {
            self::init();
        }

        $locale = $locale ?? self::$currentLanguage ?? 'en';

        $translated = $text;

        // If English, only translate if explicitly registered
        if ($locale === 'en' && !isset(self::$translations['en'][$text])) {
            $translated = $text;
        } else {
            // Ensure translations are loaded for this locale
            if (!isset(self::$translations[$locale])) {
                $oldLocale = self::$currentLanguage;
                self::$currentLanguage = $locale;
                self::loadTranslations();
                self::$currentLanguage = $oldLocale;
            }

            // Check if translation exists - use text directly as key
            if (isset(self::$translations[$locale][$text])) {
                $translated = self::$translations[$locale][$text];
            }
        }

        // Handle replacements if array provided
        if (is_array($replace) && !empty($replace)) {
            return strtr($translated, $replace);
        }

        return $translated;
    }

    /**
     * Echo translated string (similar to WordPress _e())
     * 
     * @param string $text The text to translate and echo
     * @param array|string|null $replace Optional replacement array or locale override
     * @param string|null $locale Optional locale override if $replace is an array
     */
    public static function echo(string $text, array|string|null $replace = null, ?string $locale = null): void
    {
        echo self::translate($text, $replace, $locale);
    }

    /**
     * Load translations from files using deep scope discovery
     */
    protected static function loadTranslations(): void
    {
        $locale = self::$currentLanguage ?? 'en';
        
        if ($locale === 'en') {
            // Clear translations for English (default, no translation needed)
            if (isset(self::$translations[$locale])) {
                unset(self::$translations[$locale]);
            }
            return; // English serves as fallback key
        }

        // Reset translations for this locale to ensure fresh load
        self::$translations[$locale] = [];

        // Load core translations
        $corePath = base_path("lang/{$locale}.php");
        if (file_exists($corePath)) {
            $coreTranslations = require $corePath;
            if (is_array($coreTranslations)) {
                self::$translations[$locale] = array_merge(
                    self::$translations[$locale],
                    $coreTranslations
                );
            }
        }


        // Load module translations (deeply discovered via Manager)
        if (app()->bound(ModuleManager::class)) {
             $moduleManager = app(ModuleManager::class);
             $modules = $moduleManager->discoverModules();
             foreach ($modules as $module) {
                 if ($module['enabled']) {
                     $file = $module['path'] . "/lang/{$locale}.php";
                     if (file_exists($file)) {
                         $moduleTranslations = require $file;
                         if (is_array($moduleTranslations)) {
                              self::$translations[$locale] = array_merge(
                                  self::$translations[$locale],
                                  $moduleTranslations
                              );
                         }
                     }

                 }
             }
        }

        // Load theme translations
        if (app()->bound(ThemeManager::class)) {
            $themeManager = app(ThemeManager::class);
            
            $activeFrontend = $themeManager->getActiveTheme('frontend');
            $activeAdmin = $themeManager->getActiveTheme('admin');
            
            $themesToLoad = [];
            if ($activeFrontend) $themesToLoad[] = $activeFrontend;
            if ($activeAdmin) $themesToLoad[] = $activeAdmin;
            
            foreach ($themesToLoad as $theme) {
                 $file = base_path($theme->path . "/lang/{$locale}.php");
                 if (file_exists($file)) {
                     $themeTranslations = require $file;
                     if (is_array($themeTranslations)) {
                          self::$translations[$locale] = array_merge(
                              self::$translations[$locale],
                              $themeTranslations
                          );
                     }
                 }

            }
        }
    }

    /**
     * Register translations programmatically
     * 
     * @param array $translations Array of ['original' => 'translated']
     * @param string|null $locale Optional locale override
     */
    public static function register(array $translations, ?string $locale = null): void
    {
        $locale = $locale ?? self::$currentLanguage ?? 'en';
        
        if (!isset(self::$translations[$locale])) {
            self::$translations[$locale] = [];
        }

        self::$translations[$locale] = array_merge(
            self::$translations[$locale],
            $translations
        );
    }

    /**
     * Get current language
     */
    public static function getCurrentLanguage(): string
    {
        if (self::$settingsService === null) {
            self::init();
        }
        return self::$currentLanguage ?? 'en';
    }

    /**
     * Set current language dynamically
     */
    public static function setCurrentLanguage(string $lang): void
    {
        self::$currentLanguage = $lang;
        self::loadTranslations();
    }

    /**
     * Get language direction (ltr or rtl)
     */
    public static function getLanguageDirection(): string
    {
        if (self::$settingsService === null) {
            self::init();
        }
        
        return self::$direction;
    }

    /**
     * Get all active languages sorted by sort_order
     *
     * @return \Illuminate\Support\Collection|\App\Models\Language[]
     */
    public static function getActiveLanguages()
    {
        static $activeLanguages = null;
        if ($activeLanguages === null) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                    $activeLanguages = \App\Models\Language::where('is_active', true)
                        ->orderBy('sort_order', 'asc')
                        ->get();
                }
            } catch (\Exception $e) {}

            if (!$activeLanguages || $activeLanguages->isEmpty()) {
                // Return default english as fallback
                $activeLanguages = collect([
                    new \App\Models\Language([
                        'code' => 'en',
                        'name' => 'English',
                        'native_name' => 'English',
                        'is_default' => true,
                        'is_active' => true,
                        'direction' => 'ltr',
                    ])
                ]);
            }
        }
        return $activeLanguages;
    }

    /**
     * Get a circular SVG country flag for a locale code
     *
     * @param string $code
     * @return string
     */
    public static function getFlagSvg(string $code): string
    {
        $code = strtolower(trim($code));
        $uniq = uniqid('flag_');

        if ($code === 'en' || $code === 'us' || $code === 'gb') {
            return '<svg class="flag-svg flag-en" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="16" height="16" style="vertical-align: middle; display: inline-block; border-radius: 50%; width: 1.1em; height: 1.1em; object-fit: cover;">
                <clipPath id="us-clip-' . $uniq . '"><circle cx="18" cy="18" r="18"/></clipPath>
                <g clip-path="url(#us-clip-' . $uniq . ')">
                    <rect width="36" height="36" fill="#B22234"/>
                    <rect y="2.77" width="36" height="2.77" fill="#FFF"/>
                    <rect y="8.31" width="36" height="2.77" fill="#FFF"/>
                    <rect y="13.85" width="36" height="2.77" fill="#FFF"/>
                    <rect y="19.38" width="36" height="2.77" fill="#FFF"/>
                    <rect y="24.92" width="36" height="2.77" fill="#FFF"/>
                    <rect y="30.46" width="36" height="2.77" fill="#FFF"/>
                    <rect width="18" height="19.38" fill="#3C3B6E"/>
                    <g fill="#FFF">
                        <circle cx="3" cy="3" r="0.6"/><circle cx="6" cy="3" r="0.6"/><circle cx="9" cy="3" r="0.6"/><circle cx="12" cy="3" r="0.6"/><circle cx="15" cy="3" r="0.6"/>
                        <circle cx="4.5" cy="6.5" r="0.6"/><circle cx="7.5" cy="6.5" r="0.6"/><circle cx="10.5" cy="6.5" r="0.6"/><circle cx="13.5" cy="6.5" r="0.6"/>
                        <circle cx="3" cy="10" r="0.6"/><circle cx="6" cy="10" r="0.6"/><circle cx="9" cy="10" r="0.6"/><circle cx="12" cy="10" r="0.6"/><circle cx="15" cy="10" r="0.6"/>
                        <circle cx="4.5" cy="13.5" r="0.6"/><circle cx="7.5" cy="13.5" r="0.6"/><circle cx="10.5" cy="13.5" r="0.6"/><circle cx="13.5" cy="13.5" r="0.6"/>
                        <circle cx="3" cy="17" r="0.6"/><circle cx="6" cy="17" r="0.6"/><circle cx="9" cy="17" r="0.6"/><circle cx="12" cy="17" r="0.6"/><circle cx="15" cy="17" r="0.6"/>
                    </g>
                </g>
            </svg>';
        }

        if ($code === 'vi' || $code === 'vn') {
            return '<svg class="flag-svg flag-vi" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="16" height="16" style="vertical-align: middle; display: inline-block; border-radius: 50%; width: 1.1em; height: 1.1em; object-fit: cover;">
                <clipPath id="vi-clip-' . $uniq . '"><circle cx="18" cy="18" r="18"/></clipPath>
                <g clip-path="url(#vi-clip-' . $uniq . ')">
                    <rect width="36" height="36" fill="#DA251D"/>
                    <polygon fill="#FFFF00" points="18,8 21.1,17.4 31,17.4 23,23.3 26.1,32.7 18,26.8 9.9,32.7 13,23.3 5,17.4 14.9,17.4"/>
                </g>
            </svg>';
        }

        if ($code === 'zh-cn' || $code === 'zh' || $code === 'cn') {
            return '<svg class="flag-svg flag-zh" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="16" height="16" style="vertical-align: middle; display: inline-block; border-radius: 50%; width: 1.1em; height: 1.1em; object-fit: cover;">
                <clipPath id="zh-clip-' . $uniq . '"><circle cx="18" cy="18" r="18"/></clipPath>
                <g clip-path="url(#zh-clip-' . $uniq . ')">
                    <rect width="36" height="36" fill="#EE1C25"/>
                    <polygon fill="#FFFF00" points="8,4.5 9.2,7.8 12.7,7.8 9.9,9.8 11,13.1 8,11.1 5,13.1 6.1,9.8 3.3,7.8 6.8,7.8" transform="translate(1, 2)"/>
                    <polygon fill="#FFFF00" points="14,3.5 14.5,4.7 15.7,4.7 14.7,5.5 15.1,6.7 14,5.9 12.9,6.7 13.3,5.5 12.3,4.7 13.5,4.7"/>
                    <polygon fill="#FFFF00" points="16,5.5 16.5,6.7 17.7,6.7 16.7,7.5 17.1,8.7 16,7.9 14.9,8.7 15.3,7.5 14.3,6.7 15.5,6.7"/>
                    <polygon fill="#FFFF00" points="16,8.5 16.5,9.7 17.7,9.7 16.7,10.5 17.1,11.7 16,10.9 14.9,11.7 15.3,10.5 14.3,9.7 15.5,9.7"/>
                    <polygon fill="#FFFF00" points="14,10.5 14.5,11.7 15.7,11.7 14.7,12.5 15.1,13.7 14,12.9 12.9,13.7 13.3,12.5 12.3,11.7 13.5,11.7"/>
                </g>
            </svg>';
        }

        if ($code === 'ru') {
            return '<svg class="flag-svg flag-ru" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="16" height="16" style="vertical-align: middle; display: inline-block; border-radius: 50%; width: 1.1em; height: 1.1em; object-fit: cover;">
                <clipPath id="ru-clip-' . $uniq . '"><circle cx="18" cy="18" r="18"/></clipPath>
                <g clip-path="url(#ru-clip-' . $uniq . ')">
                    <rect width="36" height="12" fill="#FFF"/>
                    <rect y="12" width="36" height="12" fill="#0039A6"/>
                    <rect y="24" width="36" height="12" fill="#D52B1E"/>
                </g>
            </svg>';
        }

        // Fallback globe
        return '<svg class="flag-svg flag-fallback" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; display: inline-block; border-radius: 50%; width: 1.1em; height: 1.1em; object-fit: cover;">
            <circle cx="18" cy="18" r="17" fill="#ECEFF1"/>
            <circle cx="18" cy="18" r="17"/>
            <path d="M1 18h34M18 1v34M4 10h28M4 26h28"/>
            <path d="M18 1c6 0 11 8 11 17s-5 17-11 17S7 27 7 18s5-17 11-17z"/>
        </svg>';
    }
}

if (!function_exists('_l')) {
    function _l(string $text, array|string|null $replace = null, ?string $locale = null): string
    {
        return \App\Helpers\LanguageHelper::translate($text, $replace, $locale);
    }
}

if (!function_exists('_e')) {
    function _e(string $text, array|string|null $replace = null, ?string $locale = null): void
    {
        \App\Helpers\LanguageHelper::echo($text, $replace, $locale);
    }
}
