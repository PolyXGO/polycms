<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * CacheService — Central cache management for PolyCMS.
 *
 * Provides a unified, hook-driven API for viewing cache status and clearing
 * caches.  Modules/themes can:
 *
 *  1. Register their own cache types via the `cache.types` filter.
 *  2. Handle clearing of their caches via `cache.clear.{type}` actions.
 *  3. Claim ownership of a cache type via the `cache.type.handler` filter
 *     so that the core does NOT clear it (avoids conflict when a dedicated
 *     caching module is installed).
 *  4. React after a clear via the `cache.cleared` action.
 *
 * Hook reference
 * ──────────────
 *  Filter  cache.types               → array $types
 *  Filter  cache.type.handler        → ?string $handler, string $type
 *  Filter  cache.status              → array $status
 *  Action  cache.clearing            → string $type
 *  Action  cache.clear.{type}        → void
 *  Action  cache.cleared             → string $type, bool $success
 *  Action  cache.clear_all.before    → array $types
 *  Action  cache.clear_all.after     → array $results
 */
class CacheService
{
    /**
     * Built-in cache type definitions.
     * Modules can extend this list via the `cache.types` filter.
     *
     * Each entry:
     *   key          — unique slug
     *   label        — human-readable name
     *   description  — short explanation
     *   group        — 'laravel' | 'polycms' | 'server' | 'module'
     *   clearable    — whether the UI should show a clear button
     */
    private function coreTypes(): array
    {
        return [
            // Laravel framework caches
            [
                'key'         => 'application',
                'label'       => 'Application Cache',
                'description' => 'General key-value cache store (file, database, redis, etc.)',
                'group'       => 'laravel',
                'clearable'   => true,
            ],
            [
                'key'         => 'view',
                'label'       => 'View Cache',
                'description' => 'Compiled Blade templates stored in storage/framework/views',
                'group'       => 'laravel',
                'clearable'   => true,
            ],
            [
                'key'         => 'config',
                'label'       => 'Config Cache',
                'description' => 'Cached configuration (bootstrap/cache/config.php)',
                'group'       => 'laravel',
                'clearable'   => true,
            ],
            [
                'key'         => 'route',
                'label'       => 'Route Cache',
                'description' => 'Cached route registrations (bootstrap/cache/routes-v7.php)',
                'group'       => 'laravel',
                'clearable'   => true,
            ],
            [
                'key'         => 'event',
                'label'       => 'Event Cache',
                'description' => 'Cached event-to-listener mappings',
                'group'       => 'laravel',
                'clearable'   => true,
            ],
            // PolyCMS internal caches
            [
                'key'         => 'theme',
                'label'       => 'Theme Cache',
                'description' => 'Theme registry, resolved templates, and theme options cache',
                'group'       => 'polycms',
                'clearable'   => true,
            ],
            [
                'key'         => 'module',
                'label'       => 'Module Cache',
                'description' => 'Module discovery and state cache',
                'group'       => 'polycms',
                'clearable'   => true,
            ],
            [
                'key'         => 'settings',
                'label'       => 'Settings Cache',
                'description' => 'Autoloaded settings key-value cache',
                'group'       => 'polycms',
                'clearable'   => true,
            ],
            [
                'key'         => 'template',
                'label'       => 'Template Resolver Cache',
                'description' => 'View path resolution cache for themes',
                'group'       => 'polycms',
                'clearable'   => true,
            ],
            // Server caches
            [
                'key'         => 'opcache',
                'label'       => 'PHP OPcache',
                'description' => 'PHP bytecode cache (requires opcache extension)',
                'group'       => 'server',
                'clearable'   => function_exists('opcache_reset'),
            ],
        ];
    }

    /**
     * Return all registered cache types (core + module-registered).
     */
    public function getTypes(): array
    {
        $types = $this->coreTypes();

        // Allow modules/themes to register additional cache types
        return Hook::applyFilters('cache.types', $types);
    }

    /**
     * Return comprehensive status info for all cache types.
     */
    public function getStatus(): array
    {
        $status = [
            'driver'  => config('cache.default'),
            'store'   => config('cache.stores.' . config('cache.default') . '.driver', config('cache.default')),
            'types'   => [],
        ];

        foreach ($this->getTypes() as $type) {
            $typeStatus = [
                'key'         => $type['key'],
                'label'       => $type['label'],
                'description' => $type['description'],
                'group'       => $type['group'],
                'clearable'   => (bool) $type['clearable'],
                'handler'     => $this->getHandler($type['key']),
                'info'        => $this->getTypeInfo($type['key']),
            ];
            $status['types'][] = $typeStatus;
        }

        // Allow modules to enrich status data
        return Hook::applyFilters('cache.status', $status);
    }

    /**
     * Clear one or more cache types.
     *
     * @param  string[]  $types  Cache type keys to clear.  Pass ['all'] to clear everything.
     * @return array  Per-type results: ['type' => 'success'|'failed'|'skipped', ...]
     */
    public function clear(array $types): array
    {
        $allTypes = collect($this->getTypes())->pluck('key')->all();

        if (in_array('all', $types, true)) {
            $types = $allTypes;
        }

        Hook::doAction('cache.clear_all.before', $types);

        $results = [];

        foreach ($types as $type) {
            if (!in_array($type, $allTypes, true)) {
                $results[$type] = 'unknown';
                continue;
            }

            // Check if a module has claimed this type
            $handler = $this->getHandler($type);

            Hook::doAction('cache.clearing', $type);

            try {
                if ($handler) {
                    // Module-claimed: let the module's hook handle it
                    Hook::doAction('cache.clear.' . $type);
                } else {
                    // Core handles it
                    $this->clearType($type);
                    // Also fire the hook in case someone wants to piggyback
                    Hook::doAction('cache.clear.' . $type);
                }

                $results[$type] = 'success';
                Hook::doAction('cache.cleared', $type, true);
            } catch (\Throwable $e) {
                Log::warning("Cache clear failed for '{$type}': " . $e->getMessage());
                $results[$type] = 'failed';
                Hook::doAction('cache.cleared', $type, false);
            }
        }

        Hook::doAction('cache.clear_all.after', $results);

        return $results;
    }

    /**
     * Attempt to recursively fix storage and bootstrap/cache permissions.
     */
    public function fixPermissions(): array
    {
        $directories = [
            storage_path(),
            base_path('bootstrap/cache')
        ];

        $fixedCount = 0;
        $failedFiles = [];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                continue;
            }

            try {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                $pathsToFix = [$dir];
                foreach ($iterator as $item) {
                    $pathsToFix[] = $item->getPathname();
                }
            } catch (\Throwable $e) {
                $failedFiles[] = [
                    'path' => str_replace(base_path(), '', $dir),
                    'error' => 'Could not read directory contents: ' . $e->getMessage(),
                    'owner' => function_exists('posix_getpwuid') ? (posix_getpwuid(@fileowner($dir))['name'] ?? @fileowner($dir)) : @fileowner($dir),
                ];
                continue;
            }

            foreach ($pathsToFix as $path) {
                $isDir = is_dir($path);
                $targetPerms = $isDir ? 0775 : 0664;

                try {
                    $currentPerms = @fileperms($path) & 0777;
                    if ($currentPerms !== $targetPerms) {
                        if (!@chmod($path, $targetPerms)) {
                            $failedFiles[] = [
                                'path' => str_replace(base_path(), '', $path),
                                'error' => 'Could not set permissions to ' . decoct($targetPerms),
                                'owner' => function_exists('posix_getpwuid') ? (posix_getpwuid(@fileowner($path))['name'] ?? @fileowner($path)) : @fileowner($path),
                            ];
                            continue;
                        }
                    }
                    $fixedCount++;
                } catch (\Throwable $e) {
                    $failedFiles[] = [
                        'path' => str_replace(base_path(), '', $path),
                        'error' => $e->getMessage(),
                        'owner' => function_exists('posix_getpwuid') ? (posix_getpwuid(@fileowner($path))['name'] ?? @fileowner($path)) : @fileowner($path),
                    ];
                }
            }
        }

        // 3. Fix root .htaccess rules for zip/pdf file routing
        $htaccessPath = base_path('.htaccess');
        if (file_exists($htaccessPath)) {
            try {
                $content = file_get_contents($htaccessPath);
                
                // Regex to find the RewriteCond for static assets
                $pattern = '/RewriteCond\s+%{REQUEST_URI}\s+\\\.\(([^)]+)\)\$?\s+\[NC\]/i';
                if (preg_match($pattern, $content, $matches)) {
                    $extensions = explode('|', $matches[1]);
                    $needsUpdate = false;
                    foreach (['zip', 'pdf'] as $ext) {
                        if (!in_array($ext, $extensions)) {
                            $extensions[] = $ext;
                            $needsUpdate = true;
                        }
                    }
                    
                    if ($needsUpdate) {
                        $newExtensions = implode('|', $extensions);
                        $replaced = preg_replace(
                            $pattern,
                            "RewriteCond %{REQUEST_URI} \\.(" . $newExtensions . ")$ [NC]",
                            $content
                        );
                        
                        if ($replaced !== null && $replaced !== $content) {
                            @file_put_contents($htaccessPath, $replaced);
                            Log::info("CacheService: Auto-updated root .htaccess to support extensions: " . $newExtensions);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("CacheService: Failed to validate/update root .htaccess: " . $e->getMessage());
            }
        }

        return [
            'success' => empty($failedFiles),
            'fixed_count' => $fixedCount,
            'failed_count' => count($failedFiles),
            'failed_files' => array_slice($failedFiles, 0, 50),
        ];
    }

    // ─── Private helpers ─────────────────────────────────────────────

    /**
     * Determine the external handler module for a type (null = core handles it).
     */
    private function getHandler(string $type): ?string
    {
        return Hook::applyFilters('cache.type.handler', null, $type);
    }

    /**
     * Gather runtime info for a specific cache type.
     */
    private function getTypeInfo(string $type): array
    {
        return match ($type) {
            'application' => [
                'driver' => config('cache.default'),
            ],
            'view' => [
                'compiled_count' => $this->countCompiledViews(),
            ],
            'config' => [
                'cached' => file_exists(app()->getCachedConfigPath()),
            ],
            'route' => [
                'cached' => file_exists(app()->getCachedRoutesPath()),
            ],
            'event' => [
                'cached' => file_exists(app()->getCachedEventsPath()),
            ],
            'opcache' => $this->getOpcacheInfo(),
            default => [],
        };
    }

    /**
     * Core clearing logic per type.
     */
    private function clearType(string $type): void
    {
        match ($type) {
            'application' => Artisan::call('cache:clear'),
            'view'        => Artisan::call('view:clear'),
            'config'      => $this->clearConfigCache(),
            'route'       => $this->clearRouteCache(),
            'event'       => $this->clearEventCache(),
            'theme'       => $this->clearThemeCache(),
            'module'      => $this->clearModuleCache(),
            'settings'    => $this->clearSettingsCache(),
            'template'    => $this->clearTemplateCache(),
            'opcache'     => $this->clearOpcache(),
            default       => null,
        };
    }

    private function clearConfigCache(): void
    {
        $path = app()->getCachedConfigPath();
        
        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        if (function_exists('opcache_invalidate') && ini_get('opcache.enable')) {
            @opcache_invalidate($path, true);
        }
    }

    private function clearRouteCache(): void
    {
        $path = app()->getCachedRoutesPath();
        
        try {
            Artisan::call('route:clear');
        } catch (\Throwable $e) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        if (function_exists('opcache_invalidate') && ini_get('opcache.enable')) {
            @opcache_invalidate($path, true);
        }
    }

    private function clearEventCache(): void
    {
        $path = app()->getCachedEventsPath();
        
        try {
            Artisan::call('event:clear');
        } catch (\Throwable $e) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        if (function_exists('opcache_invalidate') && ini_get('opcache.enable')) {
            @opcache_invalidate($path, true);
        }
    }

    private function clearThemeCache(): void
    {
        app(ThemeManager::class)->clearCache();
    }

    private function clearModuleCache(): void
    {
        app(ModuleManager::class)->clearCache();
    }

    private function clearSettingsCache(): void
    {
        app(SettingsService::class)->clearCache();
    }

    private function clearTemplateCache(): void
    {
        app(TemplateResolver::class)->clearCache();
    }

    private function clearOpcache(): void
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Return detailed info for a specific cache type (for the detail modal).
     */
    public function getTypeDetail(string $type): array
    {
        $base = [
            'key'  => $type,
            'info' => $this->getTypeInfo($type),
        ];

        return match ($type) {
            'application' => array_merge($base, $this->getApplicationDetail()),
            'view'        => array_merge($base, $this->getViewDetail()),
            'config'      => array_merge($base, $this->getConfigDetail()),
            'route'       => array_merge($base, $this->getRouteDetail()),
            'event'       => array_merge($base, $this->getEventDetail()),
            'opcache'     => array_merge($base, $this->getOpcacheDetail()),
            'theme'       => array_merge($base, $this->getPolycmsCacheDetail('theme')),
            'module'      => array_merge($base, $this->getPolycmsCacheDetail('module')),
            'settings'    => array_merge($base, $this->getPolycmsCacheDetail('settings')),
            'template'    => array_merge($base, $this->getPolycmsCacheDetail('template')),
            default       => $base,
        };
    }

    private function getApplicationDetail(): array
    {
        $driver = config('cache.default');
        $storeCfg = config('cache.stores.' . $driver, []);
        $detail = [
            'driver'       => $driver,
            'store_config' => $this->sanitizeConfig($storeCfg),
        ];

        // For file driver, show cache dir info
        if ($driver === 'file') {
            $path = $storeCfg['path'] ?? storage_path('framework/cache/data');
            $detail['path'] = $path;
            $detail['files'] = $this->getDirStats($path);
        }

        return ['detail' => $detail];
    }

    private function getViewDetail(): array
    {
        $viewPath = config('view.compiled', storage_path('framework/views'));
        $files = [];
        $totalSize = 0;

        if (File::isDirectory($viewPath)) {
            foreach (File::files($viewPath) as $file) {
                $size = $file->getSize();
                $totalSize += $size;
                $files[] = [
                    'name'      => $file->getFilename(),
                    'size'      => $size,
                    'modified'  => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
            // Sort by modified descending
            usort($files, fn($a, $b) => strcmp($b['modified'], $a['modified']));
        }

        return [
            'detail' => [
                'path'        => $viewPath,
                'total_files' => count($files),
                'total_size'  => $totalSize,
                'files'       => array_slice($files, 0, 100), // Limit to 100
            ],
        ];
    }

    private function getConfigDetail(): array
    {
        $path = app()->getCachedConfigPath();
        $cached = file_exists($path);

        return [
            'detail' => [
                'path'   => $path,
                'cached' => $cached,
                'size'   => $cached ? filesize($path) : 0,
                'modified' => $cached ? date('Y-m-d H:i:s', filemtime($path)) : null,
            ],
        ];
    }

    private function getRouteDetail(): array
    {
        $path = app()->getCachedRoutesPath();
        $cached = file_exists($path);

        return [
            'detail' => [
                'path'   => $path,
                'cached' => $cached,
                'size'   => $cached ? filesize($path) : 0,
                'modified' => $cached ? date('Y-m-d H:i:s', filemtime($path)) : null,
            ],
        ];
    }

    private function getEventDetail(): array
    {
        $path = app()->getCachedEventsPath();
        $cached = file_exists($path);

        return [
            'detail' => [
                'path'   => $path,
                'cached' => $cached,
                'size'   => $cached ? filesize($path) : 0,
                'modified' => $cached ? date('Y-m-d H:i:s', filemtime($path)) : null,
            ],
        ];
    }

    private function getOpcacheDetail(): array
    {
        if (!function_exists('opcache_get_status')) {
            return ['detail' => ['enabled' => false, 'reason' => 'OPcache extension not loaded']];
        }

        try {
            $status = @opcache_get_status(true);
            if ($status === false) {
                return ['detail' => ['enabled' => false, 'reason' => 'OPcache is disabled in php.ini']];
            }

            $mem = $status['memory_usage'] ?? [];
            $stats = $status['opcache_statistics'] ?? [];
            $scripts = $status['scripts'] ?? [];

            // Top 30 scripts by hits
            $topScripts = collect($scripts)
                ->sortByDesc('hits')
                ->take(30)
                ->map(fn($s) => [
                    'path'     => basename($s['full_path'] ?? ''),
                    'full_path' => $s['full_path'] ?? '',
                    'hits'     => $s['hits'] ?? 0,
                    'memory'   => $s['memory_consumption'] ?? 0,
                    'modified' => isset($s['timestamp']) ? date('Y-m-d H:i:s', $s['timestamp']) : null,
                ])
                ->values()
                ->all();

            return [
                'detail' => [
                    'enabled'            => true,
                    'memory' => [
                        'used_mb'        => round(($mem['used_memory'] ?? 0) / 1024 / 1024, 2),
                        'free_mb'        => round(($mem['free_memory'] ?? 0) / 1024 / 1024, 2),
                        'wasted_mb'      => round(($mem['wasted_memory'] ?? 0) / 1024 / 1024, 2),
                        'wasted_pct'     => round($mem['current_wasted_percentage'] ?? 0, 2),
                    ],
                    'statistics' => [
                        'cached_scripts' => $stats['num_cached_scripts'] ?? 0,
                        'cached_keys'    => $stats['num_cached_keys'] ?? 0,
                        'max_keys'       => $stats['max_cached_keys'] ?? 0,
                        'hits'           => $stats['hits'] ?? 0,
                        'misses'         => $stats['misses'] ?? 0,
                        'hit_rate'       => round($stats['opcache_hit_rate'] ?? 0, 2),
                        'restarts'       => ($stats['oom_restarts'] ?? 0) + ($stats['manual_restarts'] ?? 0),
                    ],
                    'top_scripts' => $topScripts,
                ],
            ];
        } catch (\Throwable $e) {
            return ['detail' => ['enabled' => false, 'error' => $e->getMessage()]];
        }
    }

    private function getPolycmsCacheDetail(string $type): array
    {
        $cacheKeys = match ($type) {
            'theme'    => ['polycms.discovered_themes', 'polycms.active_theme.frontend', 'polycms.active_theme.admin', 'polycms.active_theme.main'],
            'module'   => ['polycms.discovered_modules', 'polycms.enabled_modules'],
            'settings' => ['polycms.settings.db', 'polycms.settings.map'],
            'template' => [], // TemplateResolver uses per-request caching and ThemeManager caches
            default    => [],
        };

        $keys = [];
        foreach ($cacheKeys as $k) {
            $keys[] = [
                'key'    => $k,
                'exists' => Cache::has($k),
            ];
        }

        return [
            'detail' => [
                'cache_driver' => config('cache.default'),
                'keys'         => $keys,
            ],
        ];
    }

    /**
     * Get directory file count & total size.
     */
    private function getDirStats(string $path): array
    {
        if (!File::isDirectory($path)) {
            return ['count' => 0, 'total_size' => 0];
        }

        $count = 0;
        $total = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
                $total += $file->getSize();
            }
        }

        return ['count' => $count, 'total_size' => $total];
    }

    /**
     * Sanitize config for display (remove passwords/secrets).
     */
    private function sanitizeConfig(array $config): array
    {
        $hidden = ['password', 'secret', 'key', 'token'];
        foreach ($config as $k => &$v) {
            if (is_array($v)) {
                $v = $this->sanitizeConfig($v);
            } elseif (is_string($k) && collect($hidden)->contains(fn($h) => str_contains(strtolower($k), $h))) {
                $v = '••••••';
            }
        }
        return $config;
    }

    private function countCompiledViews(): int
    {
        $viewPath = config('view.compiled', storage_path('framework/views'));

        if (!File::isDirectory($viewPath)) {
            return 0;
        }

        return count(File::files($viewPath));
    }

    private function getOpcacheInfo(): array
    {
        if (!function_exists('opcache_get_status')) {
            return ['enabled' => false];
        }

        try {
            $status = @opcache_get_status(false);
            if ($status === false) {
                return ['enabled' => false];
            }

            return [
                'enabled'            => true,
                'memory_used_mb'     => round(($status['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 2),
                'memory_free_mb'     => round(($status['memory_usage']['free_memory'] ?? 0) / 1024 / 1024, 2),
                'cached_scripts'     => $status['opcache_statistics']['num_cached_scripts'] ?? 0,
                'hit_rate'           => round($status['opcache_statistics']['opcache_hit_rate'] ?? 0, 2),
            ];
        } catch (\Throwable $e) {
            return ['enabled' => false, 'error' => $e->getMessage()];
        }
    }
}
