<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Services;

use Modules\Polyx\Backup\Contracts\BackupDriverInterface;
use Modules\Polyx\Backup\Contracts\DatabaseDumperInterface;
use Modules\Polyx\Backup\Contracts\StorageDriverInterface;
use Modules\Polyx\Backup\Drivers\Backup\FullBackupDriver;
use Modules\Polyx\Backup\Drivers\Backup\DatabaseOnlyDriver;
use Modules\Polyx\Backup\Drivers\Backup\FilesOnlyDriver;
use Modules\Polyx\Backup\Drivers\Storage\LocalStorageDriver;
use Modules\Polyx\Backup\Drivers\Storage\GoogleDriveStorageDriver;
use Modules\Polyx\Backup\Drivers\Storage\OneDriveStorageDriver;
use Modules\Polyx\Backup\Models\BackupRecord;
use Modules\Polyx\Backup\Models\BackupCloudAccount;
use Modules\Polyx\Backup\Support\BackupManifest;
use Modules\Polyx\Backup\Support\ChunkedArchiver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BackupManager
{
    public function __construct(
        private DatabaseDumperInterface $dumper,
        private IntegrityService $integrity,
        private MaintenanceModeService $maintenanceMode,
    ) {}

    // ─────────────────────────────────────
    // BACKUP
    // ─────────────────────────────────────

    /**
     * Create a backup.
     *
     * @param  array{
     *   type?: string,
     *   name?: string,
     *   directories?: array,
     *   excludedTables?: string[],
     *   enableMaintenance?: bool,
     *   isScheduled?: bool,
     *   userId?: int,
     * }  $options
     */
    public function createBackup(array $options = []): BackupRecord
    {
        $type = $options['type'] ?? BackupRecord::TYPE_FULL;
        $name = $options['name'] ?? $this->generateBackupName($type);
        $enableMaintenance = $options['enableMaintenance'] ?? false;

        // Create a record
        $record = BackupRecord::create([
            'name' => $name,
            'type' => $type,
            'status' => BackupRecord::STATUS_PROCESSING,
            'disk' => BackupRecord::DISK_LOCAL,
            'is_scheduled' => $options['isScheduled'] ?? false,
            'started_at' => now(),
            'created_by' => $options['userId'] ?? auth()->id(),
        ]);

        // Update progress
        $this->setProgress($record->id, 'starting', 0);

        try {
            // Enable maintenance mode if requested
            if ($enableMaintenance) {
                $this->maintenanceMode->enable();
            }

            // Resolve backup driver
            $driver = $this->resolveBackupDriver($type);

            // Execute backup
            $this->setProgress($record->id, 'backing_up', 10);
            $tempDir = storage_path('app/backups/temp/' . Str::uuid());
            $result = $driver->execute($tempDir, [
                'directories' => $options['directories'] ?? null,
                'excludedTables' => $options['excludedTables'] ?? [],
            ]);

            if (!$result['success']) {
                throw new \RuntimeException($result['error'] ?? 'Backup failed');
            }

            $this->setProgress($record->id, 'storing', 70);

            // Move archive to storage
            $localStorage = new LocalStorageDriver();
            $filename = $name . '.zip';
            $localStorage->upload($result['archivePath'], $filename);

            // Clean up temp archive if it differs from final location
            $finalPath = $localStorage->getFullPath($filename);
            if ($result['archivePath'] !== $finalPath && file_exists($result['archivePath'])) {
                @unlink($result['archivePath']);
            }

            // Calculate checksum of final file
            $checksum = $this->integrity->hashFile($finalPath);
            $fileSize = filesize($finalPath);

            // Update record
            $manifest = $result['manifest'] ?? [];
            $record->update([
                'status' => BackupRecord::STATUS_COMPLETED,
                'filename' => $filename,
                'size' => $fileSize,
                'database_size' => $manifest['database']['dump_size'] ?? 0,
                'checksum' => $checksum,
                'manifest' => $manifest,
                'completed_at' => now(),
            ]);

            $this->setProgress($record->id, 'completed', 100);

            Log::info('Backup: Completed successfully', [
                'id' => $record->id,
                'name' => $name,
                'size' => BackupRecord::formatBytes($fileSize),
            ]);

            return $record->fresh();
        } catch (\Exception $e) {
            Log::error('Backup: Failed', ['error' => $e->getMessage()]);

            $record->update([
                'status' => BackupRecord::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->setProgress($record->id, 'failed', 0);

            return $record->fresh();
        } finally {
            if ($enableMaintenance) {
                $this->maintenanceMode->disable();
            }

            // Clean up temp dir
            $tempBase = storage_path('app/backups/temp');
            if (is_dir($tempBase)) {
                foreach (glob($tempBase . '/*') as $dir) {
                    if (is_dir($dir) && (time() - filemtime($dir)) > 3600) {
                        ChunkedArchiver::removeDirectory($dir);
                    }
                }
            }
        }
    }

    // ─────────────────────────────────────
    // RESTORE
    // ─────────────────────────────────────

    /**
     * Restore from a backup.
     */
    public function restore(int $backupId, array $options = []): BackupRecord
    {
        $record = BackupRecord::findOrFail($backupId);
        $enableMaintenance = $options['enableMaintenance'] ?? true;

        if (!$record->isCompleted()) {
            throw new \RuntimeException('Cannot restore from a backup with status: ' . $record->status);
        }

        // --- 1. Save protected tables before restore ---
        $savedRecords = BackupRecord::all()->toArray();
        $savedAccounts = BackupCloudAccount::all()->toArray();

        try {
            // Enable maintenance mode
            if ($enableMaintenance) {
                $this->maintenanceMode->enable();
            }

            $record->update(['status' => BackupRecord::STATUS_RESTORING]);
            $this->setProgress($record->id, 'preparing', 5);

            // --- 2. Create pre-restore snapshot ---
            if ($options['createSnapshot'] ?? true) {
                $this->setProgress($record->id, 'creating_snapshot', 10);
                $this->createBackup([
                    'name' => 'pre_restore_snapshot_' . date('Y-m-d_H-i-s'),
                    'type' => BackupRecord::TYPE_FULL,
                    'enableMaintenance' => false, // Already in maintenance
                ]);
            }

            // --- 3. Resolve the backup file ---
            $this->setProgress($record->id, 'downloading', 20);
            $localPath = $this->resolveBackupFilePath($record);

            // --- 4. Verify checksum ---
            if ($record->checksum) {
                $this->setProgress($record->id, 'verifying', 30);
                if (!$this->integrity->verify($localPath, $record->checksum)) {
                    throw new \RuntimeException('Backup file integrity check failed — checksum mismatch');
                }
            }

            // --- 5. Extract archive ---
            $this->setProgress($record->id, 'extracting', 35);
            $restoreDir = storage_path('app/backups/restore_' . Str::uuid());
            $extractResult = ChunkedArchiver::extract($localPath, $restoreDir);

            if (!$extractResult['success']) {
                throw new \RuntimeException('Archive extraction failed: ' . ($extractResult['error'] ?? 'unknown'));
            }

            // --- 6. Restore database ---
            $dbDir = $restoreDir . '/database';
            if (is_dir($dbDir)) {
                $sqlFiles = glob($dbDir . '/*.sql');
                if (!empty($sqlFiles)) {
                    $this->setProgress($record->id, 'restoring_database', 45);
                    $this->dumper->restore($sqlFiles[0], ['transaction' => true]);
                }
            }

            // --- 7. Restore files (atomic swap) ---
            $manifest = $record->manifest ?: [];
            $files = $manifest['files'] ?? [];

            if (!empty($files)) {
                $this->setProgress($record->id, 'restoring_files', 70);
                foreach ($files as $fileInfo) {
                    $name = $fileInfo['name'] ?? basename($fileInfo['path'] ?? '');
                    $sourcePath = $restoreDir . '/' . $name;
                    $targetPath = base_path($fileInfo['path'] ?? $name);

                    if (is_dir($sourcePath)) {
                        // Atomic swap: rename current → .bak, move new → target
                        $bakPath = $targetPath . '.bak_' . time();
                        if (is_dir($targetPath)) {
                            rename($targetPath, $bakPath);
                        }

                        ChunkedArchiver::copyDirectory($sourcePath, $targetPath);

                        // Remove backup dir on success
                        if (is_dir($bakPath)) {
                            ChunkedArchiver::removeDirectory($bakPath);
                        }
                    }
                }
            }

            // --- 8. Reconcile protected tables ---
            $this->setProgress($record->id, 'reconciling', 90);
            $this->reconcileProtectedTables($savedRecords, $savedAccounts);

            // --- 9. Update record ---
            $record->update([
                'status' => BackupRecord::STATUS_COMPLETED,
            ]);

            $this->setProgress($record->id, 'completed', 100);

            // Clean up
            ChunkedArchiver::removeDirectory($restoreDir);

            Log::info('Backup: Restore completed successfully', ['backup_id' => $backupId]);

            return $record->fresh();
        } catch (\Exception $e) {
            Log::error('Backup: Restore failed', ['error' => $e->getMessage()]);

            // Reconcile protected tables even on failure (they might have been wiped)
            $this->reconcileProtectedTables($savedRecords, $savedAccounts);

            $record = BackupRecord::find($backupId);
            if ($record) {
                $record->update([
                    'status' => BackupRecord::STATUS_FAILED,
                    'error_message' => 'Restore failed: ' . $e->getMessage(),
                ]);
            }

            $this->setProgress($backupId, 'failed', 0);

            // Clean up restore dir
            if (isset($restoreDir) && is_dir($restoreDir)) {
                ChunkedArchiver::removeDirectory($restoreDir);
            }

            throw $e;
        } finally {
            if ($enableMaintenance) {
                $this->maintenanceMode->disable();
            }
        }
    }

    // ─────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────

    public function deleteBackup(int $backupId): bool
    {
        $record = BackupRecord::findOrFail($backupId);

        // Delete local file
        if ($record->filename) {
            $localStorage = new LocalStorageDriver();
            $localStorage->delete($record->filename);
        }

        // Delete cloud file if exists
        if ($record->remote_path && $record->disk !== BackupRecord::DISK_LOCAL) {
            try {
                $cloudDriver = $this->resolveCloudDriver($record->disk);
                if ($cloudDriver) {
                    $cloudDriver->delete($record->remote_path);
                }
            } catch (\Exception $e) {
                Log::warning('Backup: Failed to delete cloud file', ['error' => $e->getMessage()]);
            }
        }

        $record->delete();

        return true;
    }

    // ─────────────────────────────────────
    // PROGRESS TRACKING
    // ─────────────────────────────────────

    public function setProgress(int $recordId, string $step, int $percent): void
    {
        Cache::put("backup_progress_{$recordId}", [
            'step' => $step,
            'percent' => $percent,
            'updated_at' => now()->toIso8601String(),
        ], 3600);
    }

    public function getProgress(int $recordId): ?array
    {
        return Cache::get("backup_progress_{$recordId}");
    }

    // ─────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────

    private function resolveBackupDriver(string $type): BackupDriverInterface
    {
        return match ($type) {
            BackupRecord::TYPE_FULL => new FullBackupDriver($this->dumper, $this->integrity),
            BackupRecord::TYPE_DATABASE => new DatabaseOnlyDriver($this->dumper, $this->integrity),
            BackupRecord::TYPE_FILES => new FilesOnlyDriver($this->integrity),
            default => throw new \InvalidArgumentException("Unknown backup type: {$type}"),
        };
    }

    private function resolveBackupFilePath(BackupRecord $record): string
    {
        $localStorage = new LocalStorageDriver();

        // If file exists locally, use it
        if ($record->filename && $localStorage->exists($record->filename)) {
            return $localStorage->getFullPath($record->filename);
        }

        // If on cloud, download first
        if ($record->remote_path && $record->disk !== BackupRecord::DISK_LOCAL) {
            $tempPath = storage_path('app/backups/temp/' . $record->filename);
            $cloudDriver = $this->resolveCloudDriver($record->disk);

            if (!$cloudDriver) {
                throw new \RuntimeException('Cloud storage driver not available for disk: ' . $record->disk);
            }

            $result = $cloudDriver->download($record->remote_path, $tempPath);
            if (!$result['success']) {
                throw new \RuntimeException('Failed to download from cloud: ' . ($result['error'] ?? 'unknown'));
            }

            return $tempPath;
        }

        throw new \RuntimeException('Backup file not found: ' . $record->filename);
    }

    private function resolveCloudDriver(string $disk): ?StorageDriverInterface
    {
        $provider = match ($disk) {
            BackupRecord::DISK_GOOGLE_DRIVE => BackupCloudAccount::PROVIDER_GOOGLE_DRIVE,
            BackupRecord::DISK_ONEDRIVE => BackupCloudAccount::PROVIDER_ONEDRIVE,
            default => null,
        };

        if (!$provider) {
            return null;
        }

        $account = BackupCloudAccount::where('provider', $provider)->where('is_active', true)->first();
        if (!$account) {
            return null;
        }

        return match ($provider) {
            BackupCloudAccount::PROVIDER_GOOGLE_DRIVE => new GoogleDriveStorageDriver($account),
            BackupCloudAccount::PROVIDER_ONEDRIVE => new OneDriveStorageDriver($account),
            default => null,
        };
    }

    /**
     * Re-import protected tables after DB restore.
     */
    private function reconcileProtectedTables(array $savedRecords, array $savedAccounts): void
    {
        try {
            foreach ($savedRecords as $row) {
                BackupRecord::updateOrCreate(
                    ['id' => $row['id']],
                    collect($row)->except(['id'])->toArray()
                );
            }

            foreach ($savedAccounts as $row) {
                // For cloud accounts, directly insert raw attributes (tokens are already encrypted)
                BackupCloudAccount::updateOrCreate(
                    ['id' => $row['id']],
                    collect($row)->except(['id'])->toArray()
                );
            }
        } catch (\Exception $e) {
            Log::warning('Backup: Protected tables reconciliation partial failure', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function generateBackupName(string $type): string
    {
        $prefix = config('app.name', 'PolyCMS');
        $prefix = Str::slug($prefix);
        $timestamp = date('Y-m-d_H-i-s');

        return "{$prefix}_{$type}_{$timestamp}";
    }

    /**
     * Clean up old backups based on retention rules.
     */
    public function cleanOldBackups(int $maxBackups = 10, int $maxDaysAge = 30): int
    {
        $deleted = 0;

        // Delete beyond max count
        $backups = BackupRecord::completed()->latest()->get();
        if ($backups->count() > $maxBackups) {
            $toDelete = $backups->slice($maxBackups);
            foreach ($toDelete as $backup) {
                $this->deleteBackup($backup->id);
                $deleted++;
            }
        }

        // Delete older than max days
        $cutoff = now()->subDays($maxDaysAge);
        $old = BackupRecord::completed()
            ->where('created_at', '<', $cutoff)
            ->get();

        foreach ($old as $backup) {
            // Always keep at least 1 backup
            if (BackupRecord::completed()->count() <= 1) {
                break;
            }
            $this->deleteBackup($backup->id);
            $deleted++;
        }

        return $deleted;
    }

    /**
     * Get the database dumper instance.
     */
    public function getDumper(): DatabaseDumperInterface
    {
        return $this->dumper;
    }
}
