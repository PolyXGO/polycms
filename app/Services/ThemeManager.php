<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Theme Manager - Handles theme discovery, registration, and activation
 * Similar pattern to ModuleManager
 */
class ThemeManager
{
    /**
     * Base path for themes
     */
    protected string $themesPath;

    /**
     * Cache key for discovered themes
     */
    protected string $discoveredCacheKey = 'polycms.discovered_themes';

    /**
     * Cache key for active theme
     */
    protected string $activeThemeCacheKey = 'polycms.active_theme';

    public function __construct()
    {
        $this->themesPath = base_path('themes');
    }

    /**
     * Discover all themes in the themes directory
     *
     * @return array<string, array{name: string, slug: string, version: string, author: string|null, description: string|null, type: string, path: string, screenshot: string|null}>
     */
    public function discoverThemes(): array
    {
        return Cache::remember($this->discoveredCacheKey, 3600, function () {
            $themes = [];

            if (!File::exists($this->themesPath)) {
                File::makeDirectory($this->themesPath, 0755, true);
                return [];
            }

            $themeDirs = File::directories($this->themesPath);

            foreach ($themeDirs as $themeDir) {
                $slug = basename($themeDir);
                $manifestPath = $themeDir . '/theme.json';

                if (!File::exists($manifestPath)) {
                    Log::warning("Theme manifest not found: {$manifestPath}");
                    continue;
                }

                try {
                    $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

                    // Validate required fields
                    if (empty($manifest['name']) || empty($manifest['slug'])) {
                        Log::warning("Theme manifest missing required fields: {$manifestPath}");
                        continue;
                    }

                    // Use slug from manifest or fallback to directory name
                    $themeSlug = $manifest['slug'] ?? $slug;

                    $themes[$themeSlug] = [
                        'name' => $manifest['name'],
                        'slug' => $themeSlug,
                        'version' => $manifest['version'] ?? '1.0.0',
                        'author' => $manifest['author'] ?? null,
                        'description' => $manifest['description'] ?? null,
                        'type' => $manifest['type'] ?? 'frontend',
                        'path' => 'themes/' . $slug,
                        'screenshot' => $this->findScreenshot($themeDir),
                    ];
                } catch (\JsonException $e) {
                    Log::error("Invalid theme manifest JSON: {$manifestPath}", ['error' => $e->getMessage()]);
                    continue;
                }
            }

            return $themes;
        });
    }

    /**
     * Find screenshot file in theme directory
     */
    protected function findScreenshot(string $themeDir): ?string
    {
        $screenshotPatterns = ['screenshot.png', 'screenshot.jpg', 'screenshot.webp', 'screenshot.jpeg'];
        $screenshotDir = $themeDir;

        foreach ($screenshotPatterns as $pattern) {
            $path = $screenshotDir . '/' . $pattern;
            if (File::exists($path)) {
                return $pattern;
            }
        }

        return null;
    }

    /**
     * Sync discovered themes with database
     *
     * @return array<string, Theme> Array of synced themes
     */
    public function sync(): array
    {
        $discovered = $this->discoverThemes();
        $synced = [];

        foreach ($discovered as $slug => $themeData) {
            $theme = Theme::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $themeData['name'],
                    'version' => $themeData['version'],
                    'author' => $themeData['author'],
                    'description' => $themeData['description'],
                    'type' => $themeData['type'],
                    'path' => $themeData['path'],
                    'screenshot' => $themeData['screenshot'],
                    'status' => $this->validateTheme($themeData['path']) ? 'installed' : 'broken',
                ]
            );

            $synced[$slug] = $theme;
        }

        // Mark themes not found in filesystem as broken
        Theme::whereNotIn('slug', array_keys($discovered))
            ->where('status', '!=', 'broken')
            ->update(['status' => 'broken']);

        $this->clearCache();
        return $synced;
    }

    /**
     * Validate theme structure
     */
    protected function validateTheme(string $themePath): bool
    {
        $fullPath = base_path($themePath);

        if (!File::exists($fullPath)) {
            return false;
        }

        // Check for required files/directories
        $manifestPath = $fullPath . '/theme.json';
        if (!File::exists($manifestPath)) {
            return false;
        }

        // Views directory is optional but recommended
        // $viewsPath = $fullPath . '/resources/views';
        // if (!File::exists($viewsPath)) {
        //     return false;
        // }

        return true;
    }

    /**
     * Get active theme for a given type
     */
    public function getActiveTheme(string $type = 'frontend'): ?Theme
    {
        return Cache::remember("{$this->activeThemeCacheKey}.{$type}", 3600, function () use ($type) {
            return Theme::active($type)->first();
        });
    }

    /**
     * Activate a theme by slug
     */
    public function activate(string $slug, string $type = 'frontend'): bool
    {
        $theme = Theme::where('slug', $slug)
            ->where('type', $type)
            ->where('status', 'installed')
            ->first();

        if (!$theme) {
            return false;
        }

        // Deactivate all other themes of the same type
        Theme::where('type', $type)
            ->where('id', '!=', $theme->id)
            ->update(['is_active' => false]);

        // Activate the selected theme
        $theme->update(['is_active' => true]);

        $this->clearCache();
        return true;
    }

    /**
     * Deactivate a theme
     */
    public function deactivate(string $slug, string $type = 'frontend'): bool
    {
        $theme = Theme::where('slug', $slug)
            ->where('type', $type)
            ->first();

        if (!$theme) {
            return false;
        }

        $theme->update(['is_active' => false]);
        $this->clearCache();
        return true;
    }

    /**
     * Get view paths for active theme
     *
     * @return array<string> Array of view paths in priority order
     */
    public function getViewPaths(string $type = 'frontend'): array
    {
        $activeTheme = $this->getActiveTheme($type);
        $paths = [];

        // Add theme views path first (highest priority)
        if ($activeTheme && File::exists($activeTheme->views_path)) {
            $paths[] = $activeTheme->views_path;
        }

        // Add default views path as fallback
        $paths[] = resource_path('views');

        return $paths;
    }

    /**
     * Get asset URL for active theme
     */
    public function assetUrl(string $path, string $type = 'frontend'): string
    {
        $activeTheme = $this->getActiveTheme($type);

        if (!$activeTheme) {
            // Fallback to default assets
            return asset($path);
        }

        // Remove leading slash if present
        $path = ltrim($path, '/');

        // Return theme asset URL
        return asset("themes/{$activeTheme->slug}/{$path}");
    }

    /**
     * Clear theme cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->discoveredCacheKey);
        Cache::forget("{$this->activeThemeCacheKey}.frontend");
        Cache::forget("{$this->activeThemeCacheKey}.admin");
    }

    /**
     * Get theme by slug
     */
    public function getTheme(string $slug): ?Theme
    {
        return Theme::where('slug', $slug)->first();
    }
}
