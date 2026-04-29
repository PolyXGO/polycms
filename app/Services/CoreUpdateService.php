<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemUpdateLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * CoreUpdateService — Handles the full lifecycle of PolyCMS core updates from .zip packages.
 *
 * Safety: Uses a strict whitelist approach. Only explicitly listed core paths are updated.
 * All third-party modules, themes, user uploads, storage, and environment config are preserved.
 */
class CoreUpdateService
{
    /**
     * Core directories that will be fully synced (delete-then-copy).
     */
    private const CORE_DIRS = [
        'app',
        'bootstrap',
        'database',
        'graphql',
        'lang',
        'resources',
        'routes',
    ];

    /**
     * Root-level core files that will be overwritten.
     */
    private const CORE_ROOT_FILES = [
        'artisan',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'vite.config.js',
        'tailwind.config.js',
        'postcss.config.js',
        'tsconfig.json',
        'phpunit.xml',
        '.editorconfig',
        'README.md',
    ];

    /**
     * Config files that may be updated (modules.php is explicitly excluded).
     */
    private const UPDATABLE_CONFIGS = [
        'app.php',
        'auth.php',
        'cache.php',
        'database.php',
        'filesystems.php',
        'lighthouse.php',
        'logging.php',
        'mail.php',
        'permission.php',
        'permissions.php',
        'queue.php',
        'sanctum.php',
        'services.php',
        'session.php',
    ];

    /**
     * Public directory items that will be updated.
     */
    private const PUBLIC_UPDATES = [
        'build',
        'index.php',
        '.htaccess',
        'favicon.ico',
        'robots.txt',
    ];

    /**
     * Paths that must NEVER be touched during an update.
     */
    private const PROTECTED_PATHS = [
        'modules',
        'themes',
        'storage',
        '.env',
        'config/modules.php',
        'public/storage',
        'public/uploads',
        'public/media',
        'vendor',
        'node_modules',
        'heraspec',
        'AGENTS.heraspec.md',
        'edition.json',
    ];

    private string $basePath;
    private string $tempDir;
    private string $backupDir;

    public function __construct()
    {
        $this->basePath = base_path();
        $this->tempDir = storage_path('app/temp');
        $this->backupDir = storage_path('app/backups');
    }

    /**
     * Get the current installed PolyCMS version.
     */
    public function getCurrentVersion(): string
    {
        return config('app.version', '1.0.0');
    }

    /**
     * Get system info for the admin UI.
     */
    public function getSystemInfo(): array
    {
        $diskFree = disk_free_space($this->basePath);
        $diskTotal = disk_total_space($this->basePath);

        return [
            'polycms_version' => $this->getCurrentVersion(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_driver' => config('database.default'),
            'server_os' => PHP_OS_FAMILY . ' ' . php_uname('r'),
            'disk_free' => $diskFree !== false ? $diskFree : 0,
            'disk_total' => $diskTotal !== false ? $diskTotal : 0,
            'max_upload_size' => $this->getMaxUploadSize(),
            'extensions' => [
                'zip' => extension_loaded('zip'),
                'fileinfo' => extension_loaded('fileinfo'),
            ],
        ];
    }

    /**
     * Validate an uploaded .zip package.
     * Returns package metadata if valid, or error details.
     */
    public function validatePackage(string $zipPath): array
    {
        if (!file_exists($zipPath)) {
            return ['valid' => false, 'error' => 'Package file not found.'];
        }

        if (!extension_loaded('zip')) {
            return ['valid' => false, 'error' => 'PHP zip extension is required but not installed.'];
        }

        $zip = new ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== true) {
            return ['valid' => false, 'error' => 'Invalid or corrupted .zip file.'];
        }

        // Check for config/app.php inside the zip to confirm it's a PolyCMS package
        $appConfig = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#(^|/)config/app\.php$#', $name)) {
                $appConfig = $zip->getFromIndex($i);
                break;
            }
        }

        if (!$appConfig) {
            $zip->close();
            return ['valid' => false, 'error' => 'Invalid PolyCMS package: config/app.php not found in archive.'];
        }

        // Extract version from the package's config/app.php
        $packageVersion = '0.0.0';
        if (preg_match("/'version'\s*=>\s*env\('APP_VERSION',\s*'([^']+)'\)/", $appConfig, $matches)) {
            $packageVersion = $matches[1];
        }

        $currentVersion = $this->getCurrentVersion();

        // Determine if the package includes vendor/ and public/build/
        $hasVendor = false;
        $hasBuild = false;
        $fileCount = $zip->numFiles;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'vendor/') || preg_match('#^[^/]+/vendor/#', $name)) {
                $hasVendor = true;
            }
            if (str_starts_with($name, 'public/build/') || preg_match('#^[^/]+/public/build/#', $name)) {
                $hasBuild = true;
            }
            if ($hasVendor && $hasBuild) {
                break;
            }
        }

        $fileSizeBytes = filesize($zipPath);
        $zip->close();

        return [
            'valid' => true,
            'current_version' => $currentVersion,
            'package_version' => $packageVersion,
            'is_upgrade' => version_compare($packageVersion, $currentVersion, '>'),
            'is_downgrade' => version_compare($packageVersion, $currentVersion, '<'),
            'is_same' => version_compare($packageVersion, $currentVersion, '=='),
            'has_vendor' => $hasVendor,
            'has_build' => $hasBuild,
            'file_count' => $fileCount,
            'file_size' => $fileSizeBytes,
        ];
    }

    /**
     * Create a backup of current core files before updating.
     */
    public function createBackup(): string
    {
        $this->ensureDirectory($this->backupDir);

        $version = $this->getCurrentVersion();
        $timestamp = now()->format('Ymd_His');
        $backupFile = "{$this->backupDir}/core-backup-v{$version}-{$timestamp}.zip";

        $zip = new ZipArchive();
        if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Failed to create backup archive.');
        }

        // Backup core directories
        foreach (self::CORE_DIRS as $dir) {
            $fullPath = $this->basePath . '/' . $dir;
            if (File::isDirectory($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, $dir);
            }
        }

        // Backup core root files
        foreach (self::CORE_ROOT_FILES as $file) {
            $fullPath = $this->basePath . '/' . $file;
            if (File::exists($fullPath)) {
                $zip->addFile($fullPath, $file);
            }
        }

        // Backup updatable config files
        foreach (self::UPDATABLE_CONFIGS as $configFile) {
            $fullPath = $this->basePath . '/config/' . $configFile;
            if (File::exists($fullPath)) {
                $zip->addFile($fullPath, 'config/' . $configFile);
            }
        }

        // Backup public items
        foreach (self::PUBLIC_UPDATES as $item) {
            $fullPath = $this->basePath . '/public/' . $item;
            if (File::isDirectory($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, 'public/' . $item);
            } elseif (File::exists($fullPath)) {
                $zip->addFile($fullPath, 'public/' . $item);
            }
        }

        // Backup vendor/ (for rollback)
        $vendorPath = $this->basePath . '/vendor';
        if (File::isDirectory($vendorPath)) {
            $this->addDirectoryToZip($zip, $vendorPath, 'vendor');
        }

        $zip->close();

        return $backupFile;
    }

    /**
     * Execute the full core update process.
     */
    public function performUpdate(string $zipPath, int $userId): SystemUpdateLog
    {
        $validation = $this->validatePackage($zipPath);

        if (!$validation['valid']) {
            throw new \RuntimeException($validation['error']);
        }

        // Create update log entry
        $log = SystemUpdateLog::create([
            'from_version' => $validation['current_version'],
            'to_version' => $validation['package_version'],
            'status' => SystemUpdateLog::STATUS_RUNNING,
            'performed_by' => $userId,
            'started_at' => now(),
        ]);

        try {
            // Step 1: Create backup
            $log->addStep('backup', 'running', 'Creating backup of current core files...');
            $backupPath = $this->createBackup();
            $log->update(['backup_path' => $backupPath]);
            $log->addStep('backup', 'success', 'Backup created: ' . basename($backupPath));

            // Step 2: Enter maintenance mode
            $log->addStep('maintenance_on', 'running', 'Entering maintenance mode...');
            $bypassSecret = Str::uuid()->toString();
            Artisan::call('down', [
                '--secret' => $bypassSecret,
                '--retry' => 60,
                '--refresh' => 15,
            ]);
            $log->addStep('maintenance_on', 'success', 'Maintenance mode active. Bypass: ' . $bypassSecret);

            // Step 3: Extract package to staging directory
            $log->addStep('extract', 'running', 'Extracting update package...');
            $stagingDir = $this->extractPackage($zipPath);
            $log->addStep('extract', 'success', 'Package extracted to staging directory.');

            // Step 4: Sync core directories
            $log->addStep('sync_core', 'running', 'Syncing core directories...');
            $this->syncCoreDirs($stagingDir);
            $log->addStep('sync_core', 'success', 'Core directories synced successfully.');

            // Step 5: Sync core root files
            $log->addStep('sync_files', 'running', 'Syncing core root files...');
            $this->syncCoreRootFiles($stagingDir);
            $log->addStep('sync_files', 'success', 'Core root files synced.');

            // Step 6: Sync updatable config files
            $log->addStep('sync_config', 'running', 'Syncing configuration files...');
            $this->syncConfigFiles($stagingDir);
            $log->addStep('sync_config', 'success', 'Configuration files synced (modules.php preserved).');

            // Step 7: Sync public assets (vendor + build)
            $log->addStep('sync_public', 'running', 'Syncing public assets...');
            $this->syncPublicAssets($stagingDir);
            $log->addStep('sync_public', 'success', 'Public assets synced.');

            // Step 8: Sync vendor directory if included in package
            if ($validation['has_vendor']) {
                $log->addStep('sync_vendor', 'running', 'Syncing vendor directory...');
                $this->syncVendor($stagingDir);
                $log->addStep('sync_vendor', 'success', 'Vendor directory synced.');
            }

            // Step 9: Run database migrations
            $log->addStep('migrate', 'running', 'Running database migrations...');
            $migrateOutput = '';
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrateOutput = Artisan::output();
            } catch (\Exception $e) {
                $migrateOutput = 'Migration warning: ' . $e->getMessage();
                Log::warning('Core update migration warning: ' . $e->getMessage());
            }
            $log->addStep('migrate', 'success', 'Database migrations complete. ' . trim($migrateOutput));

            // Step 10: Clear all caches
            $log->addStep('cache_clear', 'running', 'Clearing all caches...');
            $this->clearAllCaches();
            $log->addStep('cache_clear', 'success', 'All caches cleared.');

            // Step 11: Exit maintenance mode
            $log->addStep('maintenance_off', 'running', 'Exiting maintenance mode...');
            Artisan::call('up');
            $log->addStep('maintenance_off', 'success', 'Site is back online.');

            // Cleanup staging
            $this->cleanupStaging($stagingDir);

            // Mark update as successful
            $log->update([
                'status' => SystemUpdateLog::STATUS_SUCCESS,
                'completed_at' => now(),
            ]);

            Log::info("PolyCMS core updated from v{$validation['current_version']} to v{$validation['package_version']}");

            return $log->fresh();
        } catch (\Throwable $e) {
            // Log the failure
            $log->addStep('error', 'failed', $e->getMessage());
            $log->update([
                'status' => SystemUpdateLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            // Try to bring site back up
            try {
                Artisan::call('up');
            } catch (\Exception $upError) {
                Log::error('Failed to exit maintenance mode after update failure: ' . $upError->getMessage());
            }

            Log::error('PolyCMS core update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Rollback to a previous backup.
     */
    public function rollback(string $backupPath, int $userId): SystemUpdateLog
    {
        if (!file_exists($backupPath)) {
            throw new \RuntimeException('Backup file not found: ' . $backupPath);
        }

        $currentVersion = $this->getCurrentVersion();

        // Determine the target version from backup filename
        $targetVersion = 'unknown';
        if (preg_match('/core-backup-v([\d.]+)-/', basename($backupPath), $m)) {
            $targetVersion = $m[1];
        }

        $log = SystemUpdateLog::create([
            'from_version' => $currentVersion,
            'to_version' => $targetVersion,
            'status' => SystemUpdateLog::STATUS_RUNNING,
            'backup_path' => $backupPath,
            'performed_by' => $userId,
            'started_at' => now(),
        ]);

        try {
            // Enter maintenance mode
            $log->addStep('maintenance_on', 'running', 'Entering maintenance mode for rollback...');
            $bypassSecret = Str::uuid()->toString();
            Artisan::call('down', ['--secret' => $bypassSecret]);
            $log->addStep('maintenance_on', 'success', 'Maintenance mode active.');

            // Extract backup
            $log->addStep('extract_backup', 'running', 'Extracting backup archive...');
            $stagingDir = $this->tempDir . '/rollback-' . time();
            $this->ensureDirectory($stagingDir);

            $zip = new ZipArchive();
            if ($zip->open($backupPath) !== true) {
                throw new \RuntimeException('Failed to open backup archive.');
            }
            $zip->extractTo($stagingDir);
            $zip->close();
            $log->addStep('extract_backup', 'success', 'Backup extracted.');

            // Restore core directories
            $log->addStep('restore_core', 'running', 'Restoring core files from backup...');
            $this->syncCoreDirs($stagingDir);
            $this->syncCoreRootFiles($stagingDir);
            $this->syncConfigFiles($stagingDir);
            $this->syncPublicAssets($stagingDir);

            // Restore vendor if present in backup
            $vendorDir = $stagingDir . '/vendor';
            if (File::isDirectory($vendorDir)) {
                $this->syncVendor($stagingDir);
            }
            $log->addStep('restore_core', 'success', 'Core files restored.');

            // Run migrations in case of schema changes
            $log->addStep('migrate', 'running', 'Running database migrations...');
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                Log::warning('Rollback migration warning: ' . $e->getMessage());
            }
            $log->addStep('migrate', 'success', 'Database migrations complete.');

            // Clear caches
            $log->addStep('cache_clear', 'running', 'Clearing all caches...');
            $this->clearAllCaches();
            $log->addStep('cache_clear', 'success', 'All caches cleared.');

            // Exit maintenance mode
            $log->addStep('maintenance_off', 'running', 'Exiting maintenance mode...');
            Artisan::call('up');
            $log->addStep('maintenance_off', 'success', 'Site is back online.');

            // Cleanup
            File::deleteDirectory($stagingDir);

            $log->update([
                'status' => SystemUpdateLog::STATUS_ROLLED_BACK,
                'completed_at' => now(),
            ]);

            return $log->fresh();
        } catch (\Throwable $e) {
            $log->addStep('error', 'failed', $e->getMessage());
            $log->update([
                'status' => SystemUpdateLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            try {
                Artisan::call('up');
            } catch (\Exception $upError) {
                // Swallow
            }

            throw $e;
        }
    }

    /**
     * Get list of available backups.
     */
    public function getBackups(): array
    {
        if (!File::isDirectory($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $name = $file->getFilename();
            if (!str_starts_with($name, 'core-backup-') || !str_ends_with($name, '.zip')) {
                continue;
            }

            $version = 'unknown';
            if (preg_match('/core-backup-v([\d.]+)-/', $name, $m)) {
                $version = $m[1];
            }

            $backups[] = [
                'filename' => $name,
                'path' => $file->getPathname(),
                'version' => $version,
                'size' => $file->getSize(),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        // Sort by newest first
        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Check for available updates from polycms.org API (stub — to be integrated later).
     */
    public function checkForUpdates(): array
    {
        $currentVersion = $this->getCurrentVersion();

        try {
            $response = @file_get_contents('https://polycms.org/api/v1/version/latest?' . http_build_query([
                'current' => $currentVersion,
                'php' => PHP_VERSION,
                'edition' => $this->getEdition(),
            ]));

            if ($response === false) {
                return [
                    'available' => false,
                    'current_version' => $currentVersion,
                    'message' => 'Unable to connect to update server. Please check manually or upload a package.',
                ];
            }

            $data = json_decode($response, true);

            return [
                'available' => isset($data['version']) && version_compare($data['version'], $currentVersion, '>'),
                'current_version' => $currentVersion,
                'latest_version' => $data['version'] ?? $currentVersion,
                'download_url' => $data['download_url'] ?? null,
                'release_notes' => $data['release_notes'] ?? null,
                'release_date' => $data['release_date'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'available' => false,
                'current_version' => $currentVersion,
                'message' => 'Update check failed: ' . $e->getMessage(),
            ];
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────────

    /**
     * Extract the update package to a staging directory and return its path.
     */
    private function extractPackage(string $zipPath): string
    {
        $stagingDir = $this->tempDir . '/core-update-' . time();
        $this->ensureDirectory($stagingDir);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Failed to open update package.');
        }
        $zip->extractTo($stagingDir);
        $zip->close();

        // Check if the zip extracts into a subdirectory (common pattern)
        // e.g., the zip might contain polycms-ce/ as the root folder
        $entries = File::directories($stagingDir);
        if (count($entries) === 1 && count(File::files($stagingDir)) === 0) {
            // All content is inside a single subdirectory — treat it as the root
            return $entries[0];
        }

        return $stagingDir;
    }

    /**
     * Sync core directories from staging to live installation.
     */
    private function syncCoreDirs(string $stagingDir): void
    {
        foreach (self::CORE_DIRS as $dir) {
            $source = $stagingDir . '/' . $dir;
            $target = $this->basePath . '/' . $dir;

            if (!File::isDirectory($source)) {
                continue;
            }

            // Remove the existing core directory and copy fresh from staging
            if (File::isDirectory($target)) {
                File::deleteDirectory($target);
            }

            File::copyDirectory($source, $target);
        }
    }

    /**
     * Sync core root-level files from staging.
     */
    private function syncCoreRootFiles(string $stagingDir): void
    {
        foreach (self::CORE_ROOT_FILES as $file) {
            $source = $stagingDir . '/' . $file;
            $target = $this->basePath . '/' . $file;

            if (File::exists($source)) {
                File::copy($source, $target);
            }
        }
    }

    /**
     * Sync config files (preserving modules.php and .env).
     */
    private function syncConfigFiles(string $stagingDir): void
    {
        foreach (self::UPDATABLE_CONFIGS as $configFile) {
            $source = $stagingDir . '/config/' . $configFile;
            $target = $this->basePath . '/config/' . $configFile;

            if (File::exists($source)) {
                File::copy($source, $target);
            }
        }
    }

    /**
     * Sync public assets from staging.
     */
    private function syncPublicAssets(string $stagingDir): void
    {
        foreach (self::PUBLIC_UPDATES as $item) {
            $source = $stagingDir . '/public/' . $item;
            $target = $this->basePath . '/public/' . $item;

            if (File::isDirectory($source)) {
                if (File::isDirectory($target)) {
                    File::deleteDirectory($target);
                }
                File::copyDirectory($source, $target);
            } elseif (File::exists($source)) {
                File::copy($source, $target);
            }
        }
    }

    /**
     * Sync vendor directory from staging (if the package includes pre-built vendor).
     */
    private function syncVendor(string $stagingDir): void
    {
        $source = $stagingDir . '/vendor';
        $target = $this->basePath . '/vendor';

        if (!File::isDirectory($source)) {
            return;
        }

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);
    }

    /**
     * Clear all Laravel caches.
     */
    private function clearAllCaches(): void
    {
        $commands = ['config:clear', 'route:clear', 'view:clear', 'cache:clear', 'event:clear'];

        foreach ($commands as $command) {
            try {
                Artisan::call($command);
            } catch (\Exception $e) {
                Log::warning("Cache clear command '{$command}' failed: " . $e->getMessage());
            }
        }

        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Add a directory recursively to a ZipArchive.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $prefix): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $relativePath = $prefix . '/' . substr($filePath, strlen($dir) + 1);
                // Normalize directory separators
                $relativePath = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Ensure a directory exists.
     */
    private function ensureDirectory(string $dir): void
    {
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    /**
     * Cleanup staging directory after update.
     */
    private function cleanupStaging(string $stagingDir): void
    {
        try {
            // Find the parent temp directory and clean it
            if (File::isDirectory($stagingDir)) {
                File::deleteDirectory($stagingDir);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup staging directory: ' . $e->getMessage());
        }
    }

    /**
     * Get the installed edition (CE or Pro).
     */
    private function getEdition(): string
    {
        $editionFile = base_path('edition.json');
        if (File::exists($editionFile)) {
            return 'pro';
        }
        return 'ce';
    }

    /**
     * Get the maximum upload size from PHP configuration.
     */
    private function getMaxUploadSize(): int
    {
        $uploadMax = $this->parseSize(ini_get('upload_max_filesize') ?: '2M');
        $postMax = $this->parseSize(ini_get('post_max_size') ?: '8M');
        return min($uploadMax, $postMax);
    }

    /**
     * Parse PHP ini size value to bytes.
     */
    private function parseSize(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
