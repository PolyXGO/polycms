<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Theme;
use App\Models\Post;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

            // Auto-populate template_registry
            if ($theme->status === 'installed') {
                $theme->update(['template_registry' => $this->discoverThemeTemplates($slug)]);
            }

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
     * Get active theme for a given type (legacy — returns Main Theme)
     */
    public function getActiveTheme(string $type = 'frontend'): ?Theme
    {
        return Cache::remember("{$this->activeThemeCacheKey}.{$type}", 3600, function () use ($type) {
            // Prefer Main Theme, fallback to any active theme
            return Theme::main()->where('type', $type)->first()
                ?? Theme::active($type)->first();
        });
    }

    /**
     * Get the Main Theme.
     */
    public function getMainTheme(): ?Theme
    {
        return Cache::remember("{$this->activeThemeCacheKey}.main", 3600, function () {
            return Theme::main()->first();
        });
    }

    /**
     * Get all active Sub Themes.
     */
    public function getActiveSubThemes(): Collection
    {
        return Theme::activeSubs()->orderBy('priority')->orderBy('name')->get();
    }

    /**
     * Get all active themes (Main + Subs), Main first.
     */
    public function getAllActiveThemes(): Collection
    {
        return Theme::where('is_active', true)
            ->orderByRaw("CASE WHEN role = 'main' THEN 0 ELSE 1 END")
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    /**
     * Set a theme as Main Theme.
     * Old Main is auto-converted to Sub if entities reference it.
     */
    public function setAsMain(string $slug, string $type = 'frontend'): bool
    {
        $theme = Theme::where('slug', $slug)
            ->where('type', $type)
            ->where('status', 'installed')
            ->first();

        if (!$theme) {
            return false;
        }

        $oldMainTheme = $this->getMainTheme();

        // If the selected theme is already Main, nothing to do
        if ($oldMainTheme && $oldMainTheme->id === $theme->id) {
            return true;
        }

        DB::transaction(function () use ($theme, $oldMainTheme) {
            // Handle old Main Theme
            if ($oldMainTheme) {
                $isReferencedByEntities = $this->isThemeReferencedByEntities($oldMainTheme->slug);

                if ($isReferencedByEntities) {
                    // Auto-convert old Main to Sub Theme to preserve template references
                    $oldMainTheme->update([
                        'role' => 'sub',
                        'is_active' => true,
                    ]);
                } else {
                    // Simply deactivate
                    $oldMainTheme->update([
                        'role' => null,
                        'is_active' => false,
                    ]);
                }
            }

            // Activate new Main Theme
            $theme->update([
                'role' => 'main',
                'is_active' => true,
            ]);
        });

        // Fire hook (outside transaction — side-effects should not block commit)
        \App\Facades\Hook::doAction('theme.main.changed', $theme, $oldMainTheme);

        $this->clearCache();
        return true;
    }

    /**
     * Activate a theme as Sub Theme.
     */
    public function activateSubTheme(string $slug, string $type = 'frontend'): bool
    {
        $theme = Theme::where('slug', $slug)
            ->where('type', $type)
            ->where('status', 'installed')
            ->first();

        if (!$theme) {
            return false;
        }

        // Cannot activate Main Theme as Sub
        if ($theme->isMain()) {
            return false;
        }

        // Discover and cache templates
        $templateRegistry = $this->discoverThemeTemplates($slug);

        $theme->update([
            'role' => 'sub',
            'is_active' => true,
            'template_registry' => $templateRegistry,
        ]);

        // Fire hook
        \App\Facades\Hook::doAction('theme.sub.activated', $theme);

        $this->clearCache();
        return true;
    }

    /**
     * Deactivate a Sub Theme.
     * Entities using this theme's templates fallback to Main Theme.
     */
    public function deactivateSubTheme(string $slug): bool
    {
        $theme = Theme::where('slug', $slug)->first();

        if (!$theme) {
            return false;
        }

        // Cannot deactivate Main Theme
        if ($theme->isMain()) {
            return false;
        }

        $theme->update([
            'role' => null,
            'is_active' => false,
        ]);

        // Fire hook
        \App\Facades\Hook::doAction('theme.sub.deactivated', $theme);

        $this->clearCache();
        return true;
    }

    /**
     * Activate a theme by slug (legacy — redirects to setAsMain).
     */
    public function activate(string $slug, string $type = 'frontend'): bool
    {
        return $this->setAsMain($slug, $type);
    }

    /**
     * Deactivate a theme (legacy).
     */
    public function deactivate(string $slug, string $type = 'frontend'): bool
    {
        return $this->deactivateSubTheme($slug);
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
            return asset($path);
        }

        $path = ltrim($path, '/');
        return asset("themes/{$activeTheme->slug}/{$path}");
    }

    /**
     * Check if a theme slug is referenced as template_theme by any entity.
     */
    public function isThemeReferencedByEntities(string $themeSlug): bool
    {
        if (Schema::hasColumn('posts', 'template_theme') &&
            Post::where('template_theme', $themeSlug)->exists()) {
            return true;
        }

        if (Schema::hasColumn('products', 'template_theme') &&
            Product::where('template_theme', $themeSlug)->exists()) {
            return true;
        }

        if (Schema::hasColumn('categories', 'template_theme') &&
            Category::where('template_theme', $themeSlug)->exists()) {
            return true;
        }

        if (Schema::hasTable('product_categories') &&
            Schema::hasColumn('product_categories', 'template_theme') &&
            ProductCategory::where('template_theme', $themeSlug)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Discover templates provided by a theme by scanning its views directory.
     *
     * @return array<string, array{name: string, description?: string}>
     */
    public function discoverThemeTemplates(string $themeSlug): array
    {
        $theme = Theme::where('slug', $themeSlug)->first();
        if (!$theme) {
            return [];
        }

        $viewsDir = $theme->full_path . '/resources/views';
        if (!File::exists($viewsDir)) {
            return [];
        }

        // Standard template view patterns to look for
        $standardTemplates = [
            'home' => 'Homepage',
            'posts.index' => 'Post Archive',
            'posts.show' => 'Single Post',
            'pages.show' => 'Single Page',
            'products.index' => 'Product Listing',
            'products.show' => 'Single Product',
            'categories.show' => 'Category Archive',
            'product-categories.show' => 'Product Category',
        ];

        $registry = [];

        foreach ($standardTemplates as $viewType => $defaultName) {
            // Convert dot notation to path
            $viewFile = str_replace('.', '/', $viewType) . '.blade.php';

            if (File::exists($viewsDir . '/' . $viewFile)) {
                $registry[$viewType] = [
                    'name' => ($theme->name ?? $themeSlug) . ' — ' . $defaultName,
                ];
            }
        }

        // Merge with manifest-declared templates (if any)
        $manifestPath = $theme->full_path . '/theme.json';
        if (File::exists($manifestPath)) {
            try {
                $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
                $declaredTemplates = $manifest['templates'] ?? [];

                foreach ($declaredTemplates as $viewType => $meta) {
                    // Handle indexed array format: ["categories.wiki-docs", "posts.wiki-article"]
                    if (is_string($meta)) {
                        $viewType = $meta;
                        $meta = ['name' => ($theme->name ?? $themeSlug) . ' — ' . ucwords(str_replace(['.', '-'], ' ', $meta))];
                    }

                    if (!is_array($meta)) {
                        continue;
                    }

                    $registry[$viewType] = array_merge(
                        $registry[$viewType] ?? [],
                        $meta
                    );
                }
            } catch (\JsonException $e) {
                // Skip — manifest already validated during discovery
            }
        }

        return $registry;
    }

    /**
     * Clear theme cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->discoveredCacheKey);
        Cache::forget("{$this->activeThemeCacheKey}.frontend");
        Cache::forget("{$this->activeThemeCacheKey}.admin");
        Cache::forget("{$this->activeThemeCacheKey}.main");
    }

    /**
     * Get theme by slug
     */
    public function getTheme(string $slug): ?Theme
    {
        return Theme::where('slug', $slug)->first();
    }
}
