<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

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
        return Cache::remember($this->discoveredCacheKey, 3600, function () {
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

        $enabled = $this->getEnabledModules();

        if (!in_array($moduleKey, $enabled, true)) {
            $enabled[] = $moduleKey;
            $this->saveEnabledModules($enabled);
        }

        $this->clearCache();
        return true;
    }

    /**
     * Disable a module
     * 
     * @param string $moduleKey Format: "Vendor.Module"
     * @return bool
     */
    public function disableModule(string $moduleKey): bool
    {
        $enabled = $this->getEnabledModules();
        
        $enabled = array_filter($enabled, fn($key) => $key !== $moduleKey);
        
        $this->saveEnabledModules(array_values($enabled));
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

            $providerClass = $module['provider'];
            
            if (class_exists($providerClass)) {
                app()->register($providerClass);
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

            $loader = app()->getLoader();

            foreach ($module['autoload']['psr-4'] as $namespace => $path) {
                $fullPath = $module['path'] . '/' . ltrim($path, '/');
                
                if (File::exists($fullPath)) {
                    $loader->addPsr4($namespace, $fullPath);
                }
            }
        }
    }

    /**
     * Clear module cache
     * 
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget($this->discoveredCacheKey);
        Cache::forget($this->cacheKey);
    }
}
