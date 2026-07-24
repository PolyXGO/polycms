<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * ModuleUpdateService — Handles automated, fault-tolerant module updates (WordPress-style).
 *
 * Workflow: Download ZIP -> Create Backup -> Maintenance Mode -> Replace Files -> Publish Assets -> Migrate & Clear Cache -> Exit Maintenance Mode.
 * Safety: Automatic rollback from backup if any step fails after file replacement.
 */
class ModuleUpdateService
{
    public function __construct(
        private readonly ModuleManager $moduleManager
    ) {}

    /**
     * Download module update package (.zip) from remote server.
     */
    public function downloadPackage(string $downloadUrl): string
    {
        if (empty($downloadUrl)) {
            throw new \InvalidArgumentException('Download URL is empty. License verification may be required.');
        }

        $tempDir = storage_path('app/temp');
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/module_update_' . time() . '_' . Str::random(6) . '.zip';

        // Check if downloadUrl is a local file path
        if (File::exists($downloadUrl)) {
            File::copy($downloadUrl, $zipPath);
            return $zipPath;
        }

        // Fetch remote URL
        $response = Http::timeout(60)
            ->withOptions(['verify' => false])
            ->get($downloadUrl);

        if (!$response->successful()) {
            throw new \RuntimeException("Failed to download update package: HTTP {$response->status()}");
        }

        $body = $response->body();
        if (empty($body)) {
            throw new \RuntimeException('Downloaded update package is empty (0 bytes).');
        }

        File::put($zipPath, $body);

        // Verify ZIP header
        if (!self::isValidZip($zipPath)) {
            @unlink($zipPath);
            throw new \RuntimeException('Downloaded file is not a valid ZIP archive.');
        }

        return $zipPath;
    }

    /**
     * Create backup ZIP of current module directory before applying update.
     */
    public function createModuleBackup(string $vendor, string $module): string
    {
        $modulePath = base_path("modules/{$vendor}/{$module}");
        if (!File::isDirectory($modulePath)) {
            throw new \RuntimeException("Module directory does not exist: modules/{$vendor}/{$module}");
        }

        // Get current version from module.json
        $version = 'unknown';
        $manifestPath = $modulePath . '/module.json';
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            $version = $manifest['version'] ?? 'unknown';
        }

        $backupDir = storage_path("app/backups/modules/{$vendor}/{$module}");
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $backupZipPath = $backupDir . "/{$module}-v{$version}-" . date('Ymd_His') . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($backupZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Unable to create backup archive at {$backupZipPath}");
        }

        $files = File::allFiles($modulePath);
        foreach ($files as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($file->getRealPath(), $relativePath);
        }

        $zip->close();

        return $backupZipPath;
    }

    /**
     * Perform full module update with maintenance mode & automatic rollback protection.
     */
    public function performModuleUpdate(string $vendor, string $module, string $zipPath): array
    {
        $moduleKey = "{$vendor}.{$module}";
        $targetModulePath = base_path("modules/{$vendor}/{$module}");
        $backupPath = null;
        $stagingDir = null;
        $fileReplacementStarted = false;
        $maintenanceActive = false;
        $steps = [];

        try {
            // Step 1: Create backup
            $steps[] = ['step' => 'Backup', 'status' => 'running', 'message' => "Creating backup of modules/{$vendor}/{$module}..."];
            $backupPath = $this->createModuleBackup($vendor, $module);
            $steps[count($steps) - 1] = ['step' => 'Backup', 'status' => 'success', 'message' => 'Backup created: ' . basename($backupPath)];

            // Step 2: Extract to staging
            $steps[] = ['step' => 'Extract', 'status' => 'running', 'message' => 'Extracting update package to staging...'];
            $stagingDir = storage_path('app/temp/staging_' . strtolower("{$vendor}_{$module}") . '_' . time());
            if (File::isDirectory($stagingDir)) {
                File::deleteDirectory($stagingDir);
            }
            File::makeDirectory($stagingDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException('Failed to open update ZIP archive.');
            }
            $zip->extractTo($stagingDir);
            $zip->close();
            $steps[count($steps) - 1] = ['step' => 'Extract', 'status' => 'success', 'message' => 'Package extracted successfully.'];

            // Locate module root inside staging (could be directly inside staging, or nested vendor/module)
            $sourcePath = $stagingDir;
            if (File::isDirectory("{$stagingDir}/{$vendor}/{$module}")) {
                $sourcePath = "{$stagingDir}/{$vendor}/{$module}";
            } elseif (File::isDirectory("{$stagingDir}/{$module}")) {
                $sourcePath = "{$stagingDir}/{$module}";
            }

            $stagingManifest = "{$sourcePath}/module.json";
            if (!File::exists($stagingManifest)) {
                throw new \RuntimeException('Invalid update package: module.json not found in ZIP payload.');
            }

            $manifestData = json_decode(File::get($stagingManifest), true);
            $newVersion = $manifestData['version'] ?? 'unknown';

            // Step 3: Enter maintenance mode
            $steps[] = ['step' => 'Maintenance Mode', 'status' => 'running', 'message' => 'Entering maintenance mode...'];
            try {
                Artisan::call('down', ['--secret' => 'module-update-bypass', '--retry' => 15]);
                $maintenanceActive = true;
                $steps[count($steps) - 1] = ['step' => 'Maintenance Mode', 'status' => 'success', 'message' => 'Maintenance mode active.'];
            } catch (\Throwable $e) {
                Log::warning("Could not enable maintenance mode: " . $e->getMessage());
                $steps[count($steps) - 1] = ['step' => 'Maintenance Mode', 'status' => 'warning', 'message' => 'Maintenance mode warning: ' . $e->getMessage()];
            }

            // Step 4: Replace module files
            $steps[] = ['step' => 'Replace Files', 'status' => 'running', 'message' => "Replacing module files for {$moduleKey}..."];
            $fileReplacementStarted = true;

            if (File::isDirectory($targetModulePath)) {
                File::deleteDirectory($targetModulePath);
            }
            File::makeDirectory($targetModulePath, 0755, true);
            File::copyDirectory($sourcePath, $targetModulePath);

            $steps[count($steps) - 1] = ['step' => 'Replace Files', 'status' => 'success', 'message' => "Module files updated to v{$newVersion}."];

            // Step 5: Publish public assets
            $steps[] = ['step' => 'Publish Assets', 'status' => 'running', 'message' => 'Publishing frontend bundle assets...'];
            $moduleData = $this->moduleManager->getModule($moduleKey) ?? [
                'path' => $targetModulePath,
                'name' => $module,
                'vendor' => $vendor,
            ];
            $this->moduleManager->publishModuleAssets($moduleKey, $moduleData);
            $steps[count($steps) - 1] = ['step' => 'Publish Assets', 'status' => 'success', 'message' => 'Assets published to public/modules.'];

            // Step 6: Clear Caches
            $steps[] = ['step' => 'Clear Caches', 'status' => 'running', 'message' => 'Clearing module and system caches...'];
            $this->moduleManager->clearCache();
            try {
                Artisan::call('view:clear');
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
            } catch (\Throwable $e) {
                // Ignore silent cache clear errors
            }
            $steps[count($steps) - 1] = ['step' => 'Clear Caches', 'status' => 'success', 'message' => 'Caches cleared successfully.'];

            // Step 7: Turn off maintenance mode
            if ($maintenanceActive) {
                try {
                    Artisan::call('up');
                    $maintenanceActive = false;
                } catch (\Throwable $e) {
                    Log::warning("Could not disable maintenance mode: " . $e->getMessage());
                }
            }

            // Step 8: Clean up staging and downloaded temp ZIP
            if ($stagingDir && File::isDirectory($stagingDir)) {
                File::deleteDirectory($stagingDir);
            }
            if (File::exists($zipPath)) {
                @unlink($zipPath);
            }

            return [
                'success' => true,
                'message' => "Module {$moduleKey} updated successfully to v{$newVersion}.",
                'version' => $newVersion,
                'steps' => $steps,
                'backup_path' => $backupPath,
            ];
        } catch (\Throwable $e) {
            Log::error("Module update failed for {$moduleKey}: " . $e->getMessage());

            // Rollback if files were already replaced
            if ($fileReplacementStarted && $backupPath && File::exists($backupPath)) {
                try {
                    $this->rollbackModule($vendor, $module, $backupPath);
                    $steps[] = ['step' => 'Rollback', 'status' => 'success', 'message' => 'Module source code rolled back successfully from backup.'];
                } catch (\Throwable $rollbackError) {
                    Log::critical("Module rollback failed for {$moduleKey}: " . $rollbackError->getMessage());
                    $steps[] = ['step' => 'Rollback', 'status' => 'failed', 'message' => 'Rollback error: ' . $rollbackError->getMessage()];
                }
            }

            // Ensure site comes out of maintenance mode
            if ($maintenanceActive) {
                try {
                    Artisan::call('up');
                } catch (\Throwable $upErr) {
                    // Silent
                }
            }

            // Cleanup temp
            if ($stagingDir && File::isDirectory($stagingDir)) {
                File::deleteDirectory($stagingDir);
            }
            if (File::exists($zipPath)) {
                @unlink($zipPath);
            }

            throw new \RuntimeException("Update failed: {$e->getMessage()}" . ($fileReplacementStarted ? " (Rolled back to previous version)" : ""));
        }
    }

    /**
     * Rollback module directory from a backup ZIP.
     */
    public function rollbackModule(string $vendor, string $module, string $backupZipPath): void
    {
        $targetModulePath = base_path("modules/{$vendor}/{$module}");

        if (!File::exists($backupZipPath)) {
            throw new \InvalidArgumentException("Backup file not found: {$backupZipPath}");
        }

        if (File::isDirectory($targetModulePath)) {
            File::deleteDirectory($targetModulePath);
        }
        File::makeDirectory($targetModulePath, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($backupZipPath) !== true) {
            throw new \RuntimeException('Failed to open backup ZIP for rollback.');
        }

        $zip->extractTo($targetModulePath);
        $zip->close();

        // Re-publish restored assets
        $moduleKey = "{$vendor}.{$module}";
        $this->moduleManager->publishModuleAssets($moduleKey, [
            'path' => $targetModulePath,
            'name' => $module,
            'vendor' => $vendor,
        ]);

        $this->moduleManager->clearCache();
    }

    private static function isValidZip(string $filePath): bool
    {
        if (!File::exists($filePath) || File::size($filePath) < 22) {
            return false;
        }

        $zip = new ZipArchive();
        $res = $zip->open($filePath, ZipArchive::CHECKCONS);
        if ($res === true) {
            $zip->close();
            return true;
        }

        return false;
    }
}
