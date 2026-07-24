<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\ResilientCache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Module Manager - Handles module discovery, registration, and state management
 */
class ModuleManager
{
    /**
     * Base path for modules
     */
    protected string $modulesPath;

    /**
     * Cache key for enabled modules
     */
    protected string $cacheKey = 'polycms.enabled_modules';

    /**
     * Cache key for all discovered modules
     */
    protected string $discoveredCacheKey = 'polycms.discovered_modules';

    public function __construct()
    {
        $this->modulesPath = base_path('modules');
    }

    /**
     * Discover all modules in the modules directory
     *
     * @return array<string, array{name: string, vendor: string, version: string, description: string, provider: string, path: string, enabled: bool}>
     */
    public function discoverModules(): array
    {
        return ResilientCache::remember($this->discoveredCacheKey, 3600, function () {
            $modules = [];

            if (!File::exists($this->modulesPath)) {
                File::makeDirectory($this->modulesPath, 0755, true);
                return [];
            }

            $vendorDirs = File::directories($this->modulesPath);

            foreach ($vendorDirs as $vendorDir) {
                $vendor = basename($vendorDir);
                $moduleDirs = File::directories($vendorDir);

                foreach ($moduleDirs as $moduleDir) {
                    $module = basename($moduleDir);
                    $manifestPath = $moduleDir . '/module.json';

                    if (!File::exists($manifestPath)) {
                        continue;
                    }

                    try {
                        $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

                        $modules["{$vendor}.{$module}"] = [
                            'name' => $manifest['name'] ?? $module,
                            'vendor' => $manifest['vendor'] ?? $vendor,
                            'module' => $module,
                            'version' => $manifest['version'] ?? '1.0.0',
                            'description' => $manifest['description'] ?? '',
                            'provider' => $manifest['provider'] ?? null,
                            'autoload' => $manifest['autoload'] ?? [],
                            'path' => $moduleDir,
                            'enabled' => $this->isModuleEnabled("{$vendor}.{$module}"),
                            'sandbox' => $manifest['sandbox'] ?? false,
                            'capabilities' => $manifest['capabilities'] ?? [],
                            'webhooks' => $manifest['webhooks'] ?? [],
                        ];
                    } catch (\JsonException $e) {
                        // Skip invalid manifest files
                        continue;
                    }
                }
            }

            return $modules;
        });
    }

    /**
     * Get a specific module information
     *
     * @param string $moduleKey Format: "Vendor.Module"
     * @return array<string, mixed>|null
     */
    public function getModule(string $moduleKey): ?array
    {
        $modules = $this->discoverModules();
        return $modules[$moduleKey] ?? null;
    }

    /**
     * Check if a module is enabled
     *
     * @param string $moduleKey Format: "Vendor.Module"
     * @return bool
     */
    public function isModuleEnabled(string $moduleKey): bool
    {
        $enabled = $this->getEnabledModules();
        return in_array($moduleKey, $enabled, true);
    }

    /**
     * Enable a module
     *
     * @param string $moduleKey Format: "Vendor.Module"
     * @return bool
     */
    public function enableModule(string $moduleKey): bool
    {
        $module = $this->getModule($moduleKey);

        if (!$module) {
            return false;
        }

        $this->runModuleMigrations($moduleKey, $module);

        $enabled = $this->getEnabledModules();

        if (!in_array($moduleKey, $enabled, true)) {
            $enabled[] = $moduleKey;
            $this->saveEnabledModules($enabled);
        }

        $this->publishModuleAssets($moduleKey, $module);

        $this->clearCache();
        return true;
    }

    /**
     * Auto-run module migrations when enabling.
     *
     * This keeps non-technical users from needing CLI access after activating modules.
     *
     * @param array<string, mixed> $module
     */
    protected function runModuleMigrations(string $moduleKey, array $module): void
    {
        $modulePath = (string) ($module['path'] ?? '');
        if ($modulePath === '') {
            return;
        }

        $candidates = [
            $modulePath . '/src/database/migrations',
            $modulePath . '/database/migrations',
        ];

        foreach ($candidates as $migrationPath) {
            if (!File::isDirectory($migrationPath)) {
                continue;
            }

            $relativePath = str_replace('\\', '/', str_replace(base_path() . DIRECTORY_SEPARATOR, '', $migrationPath));

            try {
                Artisan::call('migrate', [
                    '--path' => $relativePath,
                    '--force' => true,
                ]);
            } catch (\Throwable $e) {
                Log::warning("Module migration warning for {$moduleKey}: " . $e->getMessage());
            }

            return;
        }
    }

    /**
     * Disable a module
     *
     * @param string $moduleKey Format: "Vendor.Module"
     * @return bool
     */
    public function disableModule(string $moduleKey): bool
    {
        $module = $this->getModule($moduleKey);
        if (!$module) {
            return false;
        }

        // Keep database schema up-to-date on both activation and deactivation flows.
        // This does not rollback module tables/data; it only runs pending migrations.
        $this->runModuleMigrations($moduleKey, $module);

        $enabled = $this->getEnabledModules();

        $enabled = array_filter($enabled, fn($key) => $key !== $moduleKey);

        $this->saveEnabledModules(array_values($enabled));
        
        $this->unpublishModuleAssets($moduleKey);
        
        $this->clearCache();

        return true;
    }

    /**
     * Get list of enabled module keys
     *
     * @return array<int, string>
     */
    public function getEnabledModules(): array
    {
        // In a real implementation, you might store this in database
        // For now, using config file
        $configPath = config_path('modules.php');

        if (File::exists($configPath)) {
            $config = require $configPath;
            return $config['enabled'] ?? [];
        }

        return [];
    }

    /**
     * Save enabled modules list
     *
     * @param array<int, string> $modules
     * @return void
     */
    protected function saveEnabledModules(array $modules): void
    {
        $configPath = config_path('modules.php');
        $config = "<?php\n\nreturn [\n    'enabled' => " . var_export($modules, true) . ",\n];\n";

        File::put($configPath, $config);
        
        // Invalidate opcache for this specific file to ensure changes are picked up
        if (function_exists('opcache_invalidate') && ini_get('opcache.enable')) {
            opcache_invalidate($configPath, true);
        }
        
        // Clear Laravel's config cache if it exists
        if (app()->configurationIsCached()) {
            try {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Ignore if config cache clearing fails
            }
        }
    }

    /**
     * Register all enabled modules' service providers
     *
     * @return void
     */
    public function registerModules(): void
    {
        $modules = $this->discoverModules();

        foreach ($modules as $moduleKey => $module) {
            if (!$module['enabled'] || !$module['provider']) {
                continue;
            }

            // Self-healing: publish assets if missing physically in public/modules/...
            $publicDistPath = public_path('modules/' . str_replace('.', '/', $moduleKey) . '/dist/admin.js');
            if (File::exists($module['path'] . '/dist/admin.js') && !File::exists($publicDistPath)) {
                $this->publishModuleAssets($moduleKey, $module);
            }

            $providerClass = $module['provider'];

            try {
                if (class_exists($providerClass)) {
                    app()->register($providerClass);
                }
            } catch (\Throwable $e) {
                // Module failed to boot — auto-disable to prevent site-wide crash
                $this->disableModule($moduleKey);
                \Illuminate\Support\Facades\Log::error(
                    "Module {$moduleKey} auto-disabled due to boot failure: " . $e->getMessage()
                );
            }
        }
    }

    /**
     * Publish module public assets to public/modules directory
     */
    public function publishModuleAssets(string $moduleKey, array $module): void
    {
        $distPath = $module['path'] . '/dist';
        if (File::isDirectory($distPath)) {
            $publicPath = public_path('modules/' . str_replace('.', '/', $moduleKey) . '/dist');
            try {
                if (!File::exists(dirname($publicPath))) {
                    File::makeDirectory(dirname($publicPath), 0755, true);
                }
                File::copyDirectory($distPath, $publicPath);
            } catch (\Throwable $e) {
                Log::error("Failed to publish assets for module {$moduleKey}: " . $e->getMessage());
            }
        }
    }

    /**
     * Remove published module assets from public directory
     */
    public function unpublishModuleAssets(string $moduleKey): void
    {
        $publicPath = public_path('modules/' . str_replace('.', '/', $moduleKey));
        if (File::exists($publicPath)) {
            try {
                File::deleteDirectory($publicPath);
            } catch (\Throwable $e) {
                Log::error("Failed to clean up assets for module {$moduleKey}: " . $e->getMessage());
            }
        }
    }

    /**
     * Register module autoloaders
     *
     * @return void
     */
    public function registerAutoloaders(): void
    {
        $modules = $this->discoverModules();

        foreach ($modules as $module) {
            if (!$module['enabled'] || empty($module['autoload']['psr-4'])) {
                continue;
            }

            // Get Composer's autoloader instance
            // In Laravel, the autoloader is already loaded, so we get it from registered loaders
            $loaders = \Composer\Autoload\ClassLoader::getRegisteredLoaders();
            $loader = $loaders['default'] ?? reset($loaders);

            if (!$loader instanceof \Composer\Autoload\ClassLoader) {
                // Fallback: if no loader found, create a new one and register it
                $loader = new \Composer\Autoload\ClassLoader();
            }

            foreach ($module['autoload']['psr-4'] as $namespace => $path) {
                $fullPath = $module['path'] . '/' . ltrim($path, '/');

                if (File::exists($fullPath)) {
                    $loader->addPsr4($namespace, $fullPath);
                }
            }
        }
    }

    /**
     * Get absolute path to the modules directory
     */
    public function getModulesPath(): string
    {
        return $this->modulesPath;
    }

    /**
     * Clear module cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        ResilientCache::forget($this->discoveredCacheKey);
        ResilientCache::forget($this->cacheKey);
        
        // Invalidate admin menu cache by bumping its global version
        ResilientCache::put('polycms.admin_menu.version', time());
    }
}
