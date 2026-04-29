<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class LanguageService
{
    protected ModuleManager $moduleManager;
    protected ThemeManager $themeManager;

    public function __construct(ModuleManager $moduleManager, ThemeManager $themeManager)
    {
        $this->moduleManager = $moduleManager;
        $this->themeManager = $themeManager;
    }

    /**
     * Get all available scopes for translations
     *
     * @return array<int, array{id: string, name: string, path: string, type: string}>
     */
    public function getAvailableScopes(): array
    {
        $scopes = [
            [
                'id' => 'core',
                'name' => 'Core',
                'path' => base_path(),
                'type' => 'core',
            ]
        ];

        // Add modules
        $modules = $this->moduleManager->discoverModules();
        foreach ($modules as $key => $module) {
            $scopes[] = [
                'id' => "module:{$key}",
                'name' => "Module: {$module['name']}",
                'path' => $module['path'],
                'type' => 'module',
            ];
        }

        // Add themes
        $themes = $this->themeManager->discoverThemes();
        foreach ($themes as $key => $theme) {
            $scopes[] = [
                'id' => "theme:{$key}",
                'name' => "Theme: {$theme['name']}",
                'path' => base_path($theme['path']),
                'type' => 'theme',
            ];
        }

        return $scopes;
    }

    /**
     * Get scope by ID
     */
    public function getScope(string $id): ?array
    {
        $scopes = $this->getAvailableScopes();
        foreach ($scopes as $scope) {
            if ($scope['id'] === $id) {
                return $scope;
            }
        }
        return null;
    }

    /**
     * Compile JSON source files to PHP cache files for a locale
     */
    public function compileToPhp(string $locale, ?string $scopeId = null): bool
    {
        try {
            $scopes = $this->getAvailableScopes();

            foreach ($scopes as $availableScope) {
                if ($scopeId !== null && $scopeId !== $availableScope['id']) {
                    continue;
                }

                $jsonFile = $availableScope['path'] . "/lang/{$locale}.json";
                $phpFile = $availableScope['path'] . "/lang/{$locale}.php";
                
                $this->compileFile($jsonFile, $phpFile);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to compile translations for {$locale}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper to read JSON and write PHP cache
     */
    protected function compileFile(string $jsonPath, string $phpPath): void
    {
        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);
            if (is_array($data)) {
                $content = "<?php\n\nreturn " . var_export($data, true) . ";\n";
                $this->ensureDirectory(dirname($phpPath));
                File::put($phpPath, $content);
            }
        }
    }

    /**
     * Copy translation files from one locale to another (working on JSON source)
     */
    public function copyTranslations(string $from, string $to): bool
    {
        try {
            $this->ensureDirectory(base_path("lang"));

            $scopes = $this->getAvailableScopes();

            foreach ($scopes as $scope) {
                $fromFile = $scope['path'] . "/lang/{$from}.json";
                $toFile = $scope['path'] . "/lang/{$to}.json";
                $fromPhpFile = $scope['path'] . "/lang/{$from}.php";

                // If JSON doesn't exist, try to convert from PHP first (migration support)
                if (!File::exists($fromFile) && File::exists($fromPhpFile)) {
                    $this->phpToJson($fromPhpFile, $fromFile);
                }

                if (File::exists($fromFile)) {
                    $this->ensureDirectory(dirname($toFile));
                    File::copy($fromFile, $toFile);
                } else if ($scope['id'] === 'core') {
                    // Always ensure core root has a file
                    File::put($toFile, "{}");
                }
            }
            
            // Compile the new language
            $this->compileToPhp($to);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to copy translations from {$from} to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert PHP translation file to JSON
     */
    protected function phpToJson(string $phpPath, string $jsonPath): void
    {
        if (File::exists($phpPath)) {
            $data = require $phpPath;
            if (is_array($data)) {
                 $this->ensureDirectory(dirname($jsonPath));
                 File::put($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * Get path to a scope's JSON translation file (ensuring it exists if core)
     */
    public function getScopeJsonPath(string $locale, string $scopeId = 'core'): ?string
    {
        $scope = $this->getScope($scopeId);
        if (!$scope) return null;

        $jsonPath = $scope['path'] . "/lang/{$locale}.json";
        $phpPath = $scope['path'] . "/lang/{$locale}.php";

        // If JSON missing but PHP exists, convert it
        if (!File::exists($jsonPath) && File::exists($phpPath)) {
            $this->phpToJson($phpPath, $jsonPath);
        }

        // If core and neither exists, create empty JSON
        if ($scopeId === 'core' && !File::exists($jsonPath)) {
            $this->ensureDirectory(dirname($jsonPath));
            File::put($jsonPath, "{}");
        }

        return $jsonPath;
    }

    /**
     * For backward compatibility in Controller
     */
    public function getCoreJsonPath(string $locale): string
    {
        return $this->getScopeJsonPath($locale, 'core');
    }

    /**
     * Zip all "JSON" translation files for a locale
     */
    public function packTranslations(string $locale, ?string $scopeId = null): ?string
    {
        $zipPath = storage_path("app/translations/{$locale}.zip");
        if ($scopeId) {
            $cleanScopeId = str_replace([':', '.'], '_', $scopeId);
            $zipPath = storage_path("app/translations/{$locale}_{$cleanScopeId}.zip");
        }
        $this->ensureDirectory(dirname($zipPath));
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $this->ensureJsonFiles($locale);

        $scopes = $this->getAvailableScopes();
        foreach ($scopes as $availableScope) {
            if ($scopeId !== null && $scopeId !== $availableScope['id']) continue;

            $langFile = $availableScope['path'] . "/lang/{$locale}.json";
            
            if (File::exists($langFile)) {
                $relativePath = str_replace(base_path() . '/', '', $availableScope['path']);
                // If it's core, we just want lang/en.json. Otherwise modules/.../lang/en.json
                $zipInternalPath = $relativePath === '' ? "lang/{$locale}.json" : "{$relativePath}/lang/{$locale}.json";
                $zip->addFile($langFile, $zipInternalPath);
            }
        }

        $zip->close();
        
        return $zipPath;
    }

    /**
     * Helper to ensure JSON files exist for a locale (converting PHP if needed)
     */
    protected function ensureJsonFiles(string $locale): void
    {
        $scopes = $this->getAvailableScopes();
        foreach ($scopes as $scope) {
            $json = $scope['path'] . "/lang/{$locale}.json";
            $php = $scope['path'] . "/lang/{$locale}.php";
            
            if (!File::exists($json) && File::exists($php)) {
                $this->phpToJson($php, $json);
            }
        }
    }

    /**
     * Extract zip file containing JSON files and compile to PHP
     */
    public function unpackTranslations(string $locale, string $zipPath): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        try {
            $tempDir = storage_path("app/temp/translations/{$locale}_" . time());
            $this->ensureDirectory($tempDir);
            $zip->extractTo($tempDir);
            $zip->close();

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $relativePath = str_replace($tempDir . '/', '', $item->getPathname());
                    
                    if (str_ends_with($relativePath, "/{$locale}.json") || $relativePath === "lang/{$locale}.json") {
                        $destPath = base_path($relativePath);
                        $this->ensureDirectory(dirname($destPath));
                        File::copy($item->getPathname(), $destPath);
                    }
                }
            }

            File::deleteDirectory($tempDir);
            
            $this->compileToPhp($locale);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to unpack translations for {$locale}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync keys from default language to target language (using JSON source)
     */
    public function syncKeys(string $defaultLocale, string $targetLocale, ?string $scopeId = null): int
    {
        $this->ensureJsonFiles($defaultLocale);
        $this->ensureJsonFiles($targetLocale);

        $syncedCount = 0;
        $scopes = $this->getAvailableScopes();

        foreach ($scopes as $availableScope) {
            if ($scopeId !== null && $scopeId !== $availableScope['id']) continue;

            $source = $availableScope['path'] . "/lang/{$defaultLocale}.json";
            $target = $availableScope['path'] . "/lang/{$targetLocale}.json";

            if (File::exists($source)) {
                $syncedCount += $this->syncJsonFile($source, $target);
            }
        }
        
        $this->compileToPhp($targetLocale, $scopeId);
        
        return $syncedCount;
    }

    protected function syncJsonFile(string $sourcePath, string $targetPath): int
    {
        if (!File::exists($sourcePath)) {
            return 0;
        }

        $sourceData = json_decode(File::get($sourcePath), true);
        if (!is_array($sourceData)) {
            return 0;
        }

        $targetData = [];
        if (File::exists($targetPath)) {
            $targetData = json_decode(File::get($targetPath), true);
            if (!is_array($targetData)) {
                $targetData = [];
            }
        }

        $added = 0;
        foreach ($sourceData as $key => $value) {
            if (!array_key_exists($key, $targetData)) {
                $targetData[$key] = $value; // Copy original value as default
                $added++;
            }
        }
        
        if ($added > 0) {
            $this->ensureDirectory(dirname($targetPath));
            File::put($targetPath, json_encode($targetData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $added;
    }

    protected function ensureDirectory(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }
}
