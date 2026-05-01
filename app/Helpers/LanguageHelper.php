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
            
            // Note: Ideally we only load translations for the ACTIVE theme,
            // but for simplicity, we can load all themes or just active.
            // Let's load active front and admin themes if possible.
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
