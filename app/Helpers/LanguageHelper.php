<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Services\SettingsService;
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
        self::$currentLanguage = self::$settingsService->get('site_language', 'en');
        
        // Load translations
        self::loadTranslations();
    }

    /**
     * Translate a string (similar to WordPress __())
     * 
     * @param string $text The text to translate
     * @param string|null $locale Optional locale override
     * @return string Translated text or original if translation not found
     */
    public static function translate(string $text, ?string $locale = null): string
    {
        if (self::$settingsService === null) {
            self::init();
        }

        // Get current language from settings if not already set or if locale is not provided
        $currentLang = self::$settingsService->get('site_language', 'en');
        
        // If language changed, reload translations
        if (self::$currentLanguage !== $currentLang) {
            self::$currentLanguage = $currentLang;
            self::loadTranslations();
        }

        $locale = $locale ?? self::$currentLanguage ?? 'en';

        // If English, return as-is
        if ($locale === 'en') {
            return $text;
        }

        // Ensure translations are loaded for this locale
        if (!isset(self::$translations[$locale])) {
            $oldLocale = self::$currentLanguage;
            self::$currentLanguage = $locale;
            self::loadTranslations();
            self::$currentLanguage = $oldLocale;
        }

        // Check if translation exists - use text directly as key
        if (isset(self::$translations[$locale][$text])) {
            return self::$translations[$locale][$text];
        }

        // Return original text if no translation found
        return $text;
    }

    /**
     * Echo translated string (similar to WordPress _e())
     * 
     * @param string $text The text to translate and echo
     * @param string|null $locale Optional locale override
     */
    public static function echo(string $text, ?string $locale = null): void
    {
        echo self::translate($text, $locale);
    }

    /**
     * Load translations from files
     */
    protected static function loadTranslations(): void
    {
        $locale = self::$currentLanguage ?? 'en';
        
        if ($locale === 'en') {
            // Clear translations for English (default, no translation needed)
            if (isset(self::$translations[$locale])) {
                unset(self::$translations[$locale]);
            }
            return;
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

        // Load module translations
        $modulesPath = base_path("modules");
        if (is_dir($modulesPath)) {
            $modules = glob($modulesPath . '/*/lang/' . $locale . '.php');
            foreach ($modules as $file) {
                $moduleTranslations = require $file;
                if (is_array($moduleTranslations)) {
                    self::$translations[$locale] = array_merge(
                        self::$translations[$locale],
                        $moduleTranslations
                    );
                }
            }
        }

        // Load theme translations
        $themesPath = base_path("themes");
        if (is_dir($themesPath)) {
            $themes = glob($themesPath . '/*/lang/' . $locale . '.php');
            foreach ($themes as $file) {
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
     * Get language direction (ltr or rtl)
     */
    public static function getLanguageDirection(): string
    {
        if (self::$settingsService === null) {
            self::init();
        }
        
        return self::$settingsService->get('site_language_direction', 'ltr');
    }
}

/**
 * Global helper function _l() - Similar to WordPress __()
 * 
 * @param string $text The text to translate
 * @param string|null $locale Optional locale override
 * @return string Translated text
 */
if (!function_exists('_l')) {
    function _l(string $text, ?string $locale = null): string
    {
        return \App\Helpers\LanguageHelper::translate($text, $locale);
    }
}

/**
 * Global helper function _e() - Similar to WordPress _e()
 * 
 * @param string $text The text to translate and echo
 * @param string|null $locale Optional locale override
 */
if (!function_exists('_e')) {
    function _e(string $text, ?string $locale = null): void
    {
        \App\Helpers\LanguageHelper::echo($text, $locale);
    }
}

