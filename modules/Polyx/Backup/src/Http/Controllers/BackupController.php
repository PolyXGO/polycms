<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Polyx\Backup\Services\BackupManager;
use Modules\Polyx\Backup\Models\BackupRecord;
use Modules\Polyx\Backup\Drivers\Storage\LocalStorageDriver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(
        private BackupManager $manager,
    ) {}

    /**
     * List all backups.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BackupRecord::with('creator');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type = $request->get('type')) {
            $query->byType($type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($sortBy = $request->get('sort_by')) {
            $sortDir = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->get('per_page', 20);
        $backups = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $backups->items(),
            'meta' => [
                'current_page' => $backups->currentPage(),
                'last_page' => $backups->lastPage(),
                'total' => $backups->total(),
                'per_page' => $backups->perPage(),
            ],
        ]);
    }

    /**
     * Create a new backup.
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'in:full,database,files',
            'name' => 'nullable|string|max:255',
            'enable_maintenance' => 'nullable|boolean',
            'directories' => 'nullable|array',
            'excluded_tables' => 'nullable|array',
        ]);

        // Check if any backup is already processing
        $processing = BackupRecord::whereIn('status', [
            BackupRecord::STATUS_PROCESSING,
            BackupRecord::STATUS_RESTORING,
        ])->exists();

        if ($processing) {
            return response()->json([
                'success' => false,
                'message' => 'Another backup or restore operation is already in progress.',
            ], 409);
        }

        try {
            $record = $this->manager->createBackup([
                'type' => $request->get('type', 'full'),
                'name' => $request->get('name'),
                'enableMaintenance' => $request->boolean('enable_maintenance', false),
                'directories' => $request->get('directories'),
                'excludedTables' => $request->get('excluded_tables', []),
                'userId' => auth()->id(),
            ]);

            return response()->json([
                'success' => $record->isCompleted(),
                'message' => $record->isCompleted()
                    ? 'Backup created successfully'
                    : 'Backup failed: ' . $record->error_message,
                'data' => $record->load('creator'),
            ], $record->isCompleted() ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore from a backup.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'enable_maintenance' => 'nullable|boolean',
            'create_snapshot' => 'nullable|boolean',
        ]);

        // Check if any operation is already running
        $processing = BackupRecord::whereIn('status', [
            BackupRecord::STATUS_PROCESSING,
            BackupRecord::STATUS_RESTORING,
        ])->exists();

        if ($processing) {
            return response()->json([
                'success' => false,
                'message' => 'Another backup or restore operation is already in progress.',
            ], 409);
        }

        try {
            $record = $this->manager->restore($id, [
                'enableMaintenance' => $request->boolean('enable_maintenance', true),
                'createSnapshot' => $request->boolean('create_snapshot', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Restore completed successfully',
                'data' => $record,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a backup.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->manager->deleteBackup($id);

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a signed URL for downloading.
     */
    public function downloadUrl(int $id): JsonResponse
    {
        $record = BackupRecord::findOrFail($id);

        if (!$record->filename) {
            return response()->json(['success' => false, 'message' => 'Backup file not found'], 404);
        }

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'backup.api.download',
            now()->addHours(1),
            ['id' => $id]
        );

        return response()->json([
            'success' => true,
            'url' => $url,
        ]);
    }

    /**
     * Download a backup file.
     */
    public function download(int $id)
    {
        $record = BackupRecord::findOrFail($id);

        if (!$record->filename) {
            abort(404, 'Backup file not found');
        }

        $localStorage = new LocalStorageDriver();
        $path = $localStorage->getFullPath($record->filename);

        if (!file_exists($path)) {
            abort(404, 'Backup file not found on disk');
        }

        return response()->download($path, $record->filename);
    }

    /**
     * Get backup/restore progress.
     */
    public function status(int $id): JsonResponse
    {
        $record = BackupRecord::findOrFail($id);
        $progress = $this->manager->getProgress($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $record->id,
                'status' => $record->status,
                'progress' => $progress,
            ],
        ]);
    }

    /**
     * Get system info for backup (tables, directories, db size).
     */
    public function info(): JsonResponse
    {
        $dumper = $this->manager->getDumper();
        $tables = $dumper->getTables();
        $protectedTables = $dumper->getProtectedTables();
        $dbSize = $dumper->getDatabaseSize();

        // Default directories
        $directories = [];
        $candidates = [
            ['path' => 'storage/app/public', 'name' => 'storage_public', 'type' => 'dir'],
            ['path' => 'themes', 'name' => 'themes', 'type' => 'dir'],
            ['path' => 'modules', 'name' => 'modules', 'type' => 'dir'],
            ['path' => 'app', 'name' => 'app_core', 'type' => 'dir'],
            ['path' => 'config', 'name' => 'config', 'type' => 'dir'],
            ['path' => 'public', 'name' => 'public', 'type' => 'dir'],
            ['path' => 'resources', 'name' => 'resources', 'type' => 'dir'],
            ['path' => 'routes', 'name' => 'routes', 'type' => 'dir'],
            ['path' => 'database', 'name' => 'database', 'type' => 'dir'],
            ['path' => '.env', 'name' => 'environment_file', 'type' => 'file'],
            ['path' => 'composer.json', 'name' => 'composer_json', 'type' => 'file'],
            ['path' => 'composer.lock', 'name' => 'composer_lock', 'type' => 'file'],
        ];

        foreach ($candidates as $dir) {
            $basePath = base_path($dir['path']);
            if ($dir['type'] === 'file' && is_file($basePath)) {
                $dir['size'] = filesize($basePath);
                $directories[] = array_merge($dir, ['exists' => true]);
            } elseif ($dir['type'] === 'dir' && is_dir($basePath)) {
                $dir['size'] = \Modules\Polyx\Backup\Support\ChunkedArchiver::directorySize($basePath);
                $directories[] = array_merge($dir, ['exists' => true]);
            }
        }

        // Stats
        $totalBackups = BackupRecord::completed()->count();
        $totalSize = BackupRecord::completed()->sum('size');
        $lastBackup = BackupRecord::completed()->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'database' => [
                    'driver' => $dumper->getDriverName(),
                    'tables' => $tables,
                    'protected_tables' => $protectedTables,
                    'size' => $dbSize,
                    'size_formatted' => BackupRecord::formatBytes($dbSize),
                ],
                'directories' => $directories,
                'stats' => [
                    'total_backups' => $totalBackups,
                    'total_size' => $totalSize,
                    'total_size_formatted' => BackupRecord::formatBytes((int) $totalSize),
                    'last_backup' => $lastBackup?->created_at?->toIso8601String(),
                ],
            ],
        ]);
    }
}
