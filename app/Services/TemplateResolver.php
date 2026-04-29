<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

/**
 * TemplateResolver — resolves view names based on multi-theme template assignments.
 *
 * Resolution priority:
 * 1. Entity's template_theme (if set and theme is active)
 * 2. Global default for entity type (from Settings)
 * 3. Main Theme default view (via prepended path)
 * 4. Core default views (resources/views/)
 */
class TemplateResolver
{
    /**
     * Track which theme functions.php have been loaded this request
     */
    protected static array $loadedThemeFunctions = [];

    /**
     * Cached template registry from all active themes
     */
    protected ?array $cachedRegistry = null;

    /**
     * Cached active themes (keyed by slug) — single query per request
     */
    protected ?\Illuminate\Support\Collection $cachedActiveThemes = null;

    public function __construct(
        protected ThemeManager $themeManager,
    ) {}

    /**
     * Get all active themes as a collection keyed by slug.
     * Single DB query per request — all other methods use this.
     */
    protected function getActiveThemes(): \Illuminate\Support\Collection
    {
        if ($this->cachedActiveThemes === null) {
            $this->cachedActiveThemes = Theme::where('is_active', true)
                ->orderByRaw("CASE WHEN role = 'main' THEN 0 ELSE 1 END")
                ->orderBy('priority')
                ->orderBy('name')
                ->get()
                ->keyBy('slug');
        }
        return $this->cachedActiveThemes;
    }

    /**
     * Resolve the view name for rendering an entity.
     *
     * @param string      $viewName       Standard view name (e.g., 'posts.show')
     * @param string|null $templateTheme  Theme slug from entity's template_theme field
     * @param string|null $entityType     Entity type for global default lookup (e.g., 'posts')
     * @return string Resolved view name (possibly namespaced)
     */
    public function resolve(string $viewName, ?string $templateTheme = null, ?string $entityType = null): string
    {
        // 1. Entity-level template_theme
        if ($templateTheme) {
            $themeSlug = $templateTheme;
            $templateKey = $viewName;
            
            // Handle custom templates (e.g., flexidocs::posts.iframe)
            if (str_contains($templateTheme, '::')) {
                [$themeSlug, $templateKey] = explode('::', $templateTheme, 2);
            }

            $resolved = $this->resolveFromTheme($templateKey, $themeSlug);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        // 2. Global default for entity type
        if ($entityType) {
            $settingsKey = $this->getSettingsKeyForViewType($viewName, $entityType);
            if ($settingsKey) {
                $globalDefault = $this->getSettingValue($settingsKey);
                if ($globalDefault) {
                    $themeSlug = $globalDefault;
                    $templateKey = $viewName;
                    
                    if (str_contains($globalDefault, '::')) {
                        [$themeSlug, $templateKey] = explode('::', $globalDefault, 2);
                    }
                    
                    $resolved = $this->resolveFromTheme($templateKey, $themeSlug);
                    if ($resolved !== null) {
                        return $resolved;
                    }
                }
            }
        }

        // 3. Main Theme default (already prepended via ThemeServiceProvider)
        // 4. Core default — Laravel handles this via standard view resolution
        // Apply filter hook
        $resolved = \App\Facades\Hook::applyFilters('theme.template.resolve', $viewName, $templateTheme, $entityType, null);

        return $resolved;
    }

    /**
     * Attempt to resolve a view from a specific theme.
     *
     * @return string|null Namespaced view name if theme is active and has the view, null otherwise
     */
    protected function resolveFromTheme(string $viewName, string $themeSlug): ?string
    {
        // Check if the theme is active (Main or Sub) — uses cached collection
        if (!$this->isThemeActive($themeSlug)) {
            return null;
        }

        // Check if the Main Theme — use default resolution (already prepended)
        $mainTheme = $this->themeManager->getMainTheme();
        if ($mainTheme && $mainTheme->slug === $themeSlug) {
            return $viewName;
        }

        // Sub Theme — use namespaced view
        $namespacedView = "theme.{$themeSlug}::{$viewName}";

        if (View::exists($namespacedView)) {
            // Lazy load sub-theme functions.php
            $this->loadThemeFunctionsIfNeeded($themeSlug);
            return $namespacedView;
        }

        return null;
    }

    /**
     * Check if a theme is currently active (Main or Sub).
     * Uses per-request cached collection — no DB query.
     */
    protected function isThemeActive(string $themeSlug): bool
    {
        return $this->getActiveThemes()->has($themeSlug);
    }

    /**
     * Get available templates for a specific view type from all active themes.
     * Uses cached collection — single query for all themes.
     *
     * @param string $viewType View type (e.g., 'posts.show', 'products.index')
     * @return array Array of template entries
     */
    public function getAvailableTemplates(string $viewType): array
    {
        $templates = [];
        $activeThemes = $this->getActiveThemes();

        $parts = explode('.', $viewType);
        $prefix = $parts[0] . '.';

        foreach ($activeThemes as $theme) {
            $hasThemeGroup = false;
            $registry = $theme->template_registry ?? [];

            // Always merge manifest-declared templates from theme.json
            // so newly-added templates appear without manual sync
            $manifestPath = $theme->full_path . '/theme.json';
            if (file_exists($manifestPath)) {
                try {
                    $manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
                    $declaredTemplates = $manifest['templates'] ?? [];
                    foreach ($declaredTemplates as $tKey => $tMeta) {
                        if (is_string($tMeta)) {
                            $tKey = $tMeta;
                            $tMeta = ['name' => ($theme->name ?? $theme->slug) . ' — ' . ucwords(str_replace(['.', '-'], ' ', $tMeta))];
                        }
                        if (is_array($tMeta) && !isset($registry[$tKey])) {
                            $registry[$tKey] = $tMeta;
                        }
                    }
                } catch (\JsonException $e) {
                    // skip
                }
            }

            // 1. Add Default template for this theme
            if ($this->themeHasTemplate($theme->slug, $viewType)) {
                $templateMeta = $registry[$viewType] ?? [];

                $templates[] = [
                    'theme_slug' => $theme->slug,
                    'theme_name' => $theme->name,
                    'theme_role' => $theme->role,
                    'template_name' => $templateMeta['name'] ?? ($theme->name . ' — ' . $this->humanizeViewType($viewType)),
                    'template_id' => $theme->slug,
                    'is_group' => true,
                    'description' => $templateMeta['description'] ?? null,
                    'screenshot_url' => $this->resolveScreenshotUrl($theme, $templateMeta),
                ];
                $hasThemeGroup = true;
            }

            // 2. Add Custom templates for this entity type (e.g. posts.*)
            // Exclude standard templates that have their own viewType
            $standardKeys = [
                'home', 'posts.index', 'posts.show', 'pages.show',
                'products.index', 'products.show',
                'categories.show', 'product-categories.show',
            ];
            foreach ($registry as $key => $meta) {
                if ($key !== $viewType && str_starts_with($key, $prefix) && !in_array($key, $standardKeys, true)) {
                    if (View::exists("theme.{$theme->slug}::{$key}")) {
                        // Ensure we have a group header
                        if (!$hasThemeGroup) {
                            $templates[] = [
                                'theme_slug' => $theme->slug,
                                'theme_name' => $theme->name,
                                'theme_role' => $theme->role,
                                'template_name' => $theme->name,
                                'template_id' => null,
                                'is_group' => true,
                                'description' => null,
                                'screenshot_url' => null,
                            ];
                            $hasThemeGroup = true;
                        }

                        $templates[] = [
                            'theme_slug' => $theme->slug,
                            'theme_name' => $theme->name,
                            'theme_role' => $theme->role,
                            'template_name' => $meta['name'] ?? static::humanizeViewType($key),
                            'template_id' => $theme->slug . '::' . $key,
                            'is_group' => false,
                            'description' => $meta['description'] ?? null,
                            'screenshot_url' => $this->resolveScreenshotUrl($theme, $meta),
                        ];
                    }
                }
            }
        }

        // Apply filter hook
        $templates = \App\Facades\Hook::applyFilters('theme.template.registry', $templates, $viewType);

        return $templates;
    }

    /**
     * Check if a specific theme provides a template for a view type.
     * Uses cached collection for registry check — no individual query.
     */
    public function themeHasTemplate(string $themeSlug, string $viewType): bool
    {
        // Check if the namespaced view exists
        $namespacedView = "theme.{$themeSlug}::{$viewType}";
        if (View::exists($namespacedView)) {
            return true;
        }

        // Also check from template_registry cache (using cached collection)
        $theme = $this->getActiveThemes()->get($themeSlug);
        if ($theme && $theme->template_registry) {
            return array_key_exists($viewType, $theme->template_registry);
        }

        return false;
    }

    /**
     * Get CSS/JS asset paths for a theme's scoped loading.
     * Uses cached collection — no individual query.
     *
     * @return array{css: string[], js: string[]}
     */
    public function resolveThemeAssets(string $themeSlug): array
    {
        $theme = $this->getActiveThemes()->get($themeSlug);
        if (!$theme) {
            return ['css' => [], 'js' => []];
        }

        $publicPath = $theme->full_path . '/public';
        $css = [];
        $js = [];

        // Check for standard asset files
        if (file_exists($publicPath . '/css/style.css')) {
            $css[] = "/themes/{$themeSlug}/css/style.css";
        }
        if (file_exists($publicPath . '/js/main.js')) {
            $js[] = "/themes/{$themeSlug}/js/main.js";
        }

        // Scan for additional CSS/JS files
        if (is_dir($publicPath . '/css')) {
            foreach (glob($publicPath . '/css/*.css') as $file) {
                $filename = basename($file);
                if ($filename !== 'style.css') {
                    $css[] = "/themes/{$themeSlug}/css/{$filename}";
                }
            }
        }
        if (is_dir($publicPath . '/js')) {
            foreach (glob($publicPath . '/js/*.js') as $file) {
                $filename = basename($file);
                if ($filename !== 'main.js') {
                    $js[] = "/themes/{$themeSlug}/js/{$filename}";
                }
            }
        }

        $assets = ['css' => $css, 'js' => $js];

        // Apply filter hook
        return \App\Facades\Hook::applyFilters('theme.template.assets', $assets, $themeSlug);
    }

    /**
     * Lazy-load a theme's functions.php (once per request).
     * Uses cached collection — no individual query.
     *
     * Core-level protection: catches function redeclaration errors
     * (e.g., when main and sub themes define the same helper functions)
     * instead of crashing with FatalError. Theme developers SHOULD use
     * function_exists() guards, but the core handles it gracefully if they don't.
     */
    public function loadThemeFunctionsIfNeeded(string $themeSlug): void
    {
        if (isset(static::$loadedThemeFunctions[$themeSlug])) {
            return;
        }

        $theme = $this->getActiveThemes()->get($themeSlug);
        if (!$theme) {
            return;
        }

        $functionsPath = $theme->full_path . '/functions.php';
        if (file_exists($functionsPath)) {
            try {
                require_once $functionsPath;
            } catch (\Throwable $e) {
                // Catch function redeclaration and other fatal-level errors.
                // Log for theme developer awareness, but don't crash the request.
                \Illuminate\Support\Facades\Log::warning(
                    "Theme '{$themeSlug}' functions.php error: {$e->getMessage()}. " .
                    "Theme helpers should use function_exists() guards to avoid conflicts."
                );
            }
        }

        static::$loadedThemeFunctions[$themeSlug] = true;
    }

    /**
     * Get the settings key for a view type's global default.
     */
    protected function getSettingsKeyForViewType(string $viewName, string $entityType): ?string
    {
        $sanitized = str_replace('.', '_', $viewName);
        return "template_default_{$sanitized}";
    }

    /**
     * Get a setting value.
     */
    protected function getSettingValue(string $key): ?string
    {
        try {
            $settingsService = app(\App\Services\SettingsService::class);
            $value = $settingsService->get($key);
            return $value && $value !== '' ? (string) $value : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert view type to human-readable name.
     */
    protected function humanizeViewType(string $viewType): string
    {
        $map = [
            'home' => 'Homepage',
            'posts.index' => 'Post Archive',
            'posts.show' => 'Single Post',
            'pages.show' => 'Single Page',
            'products.index' => 'Product Listing',
            'products.show' => 'Single Product',
            'categories.show' => 'Category Archive',
            'product-categories.show' => 'Product Category',
        ];

        return $map[$viewType] ?? ucwords(str_replace(['.', '-'], ' ', $viewType));
    }

    /**
     * Resolve screenshot URL for a template.
     */
    protected function resolveScreenshotUrl(Theme $theme, array $templateMeta): ?string
    {
        if (!empty($templateMeta['screenshot'])) {
            return "/themes/{$theme->slug}/{$templateMeta['screenshot']}";
        }

        if ($theme->screenshot) {
            return "/themes/{$theme->slug}/{$theme->screenshot}";
        }

        return null;
    }

    /**
     * Reset cached data (useful after theme changes).
     */
    public function clearCache(): void
    {
        $this->cachedRegistry = null;
        $this->cachedActiveThemes = null;
    }
}
