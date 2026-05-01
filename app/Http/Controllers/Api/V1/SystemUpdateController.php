<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\EnsuresAdmin;
use App\Models\SystemUpdateLog;
use App\Services\CoreUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemUpdateController extends Controller
{
    use EnsuresAdmin;

    public function __construct(
        private readonly CoreUpdateService $updateService
    ) {}

    /**
     * GET /api/v1/system/info
     * Returns system information.
     */
    public function info(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        return response()->json([
            'success' => true,
            'data' => $this->updateService->getSystemInfo(),
        ]);
    }

    /**
     * POST /api/v1/system/update/upload
     * Upload and validate a .zip update package.
     */
    public function upload(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $request->validate([
            'package' => 'required|file|mimes:zip|max:512000', // Max 500MB
        ]);

        $file = $request->file('package');

        $filename = 'polycms-update-' . time() . '.zip';
        $path = $file->storeAs('temp', $filename, 'local');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

        $validation = $this->updateService->validatePackage($fullPath);

        if (!$validation['valid']) {
            // Clean up invalid package
            @unlink($fullPath);

            return response()->json([
                'success' => false,
                'message' => $validation['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($validation, [
                'stored_path' => $fullPath,
                'filename' => $filename,
            ]),
        ]);
    }

    /**
     * POST /api/v1/system/update/execute
     * Execute the update from a previously uploaded package.
     */
    public function execute(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        // Prevent timeout and memory exhaustion during large file I/O operations
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $request->validate([
            'package_path' => 'required|string',
        ]);

        if (!\Illuminate\Support\Facades\Schema::hasTable('system_update_logs')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        }

        $packagePath = $request->input('package_path');

        if (!file_exists($packagePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Update package not found. Please upload again.',
            ], 404);
        }

        try {
            $log = $this->updateService->performUpdate($packagePath, $request->user()->id);

            // Clean up the uploaded package after successful update
            @unlink($packagePath);

            return response()->json([
                'success' => true,
                'message' => "PolyCMS updated successfully from v{$log->from_version} to v{$log->to_version}.",
                'data' => $log,
            ]);
        } catch (\Throwable $e) {
            Log::error('System update execution failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
                'data' => SystemUpdateLog::latest()->first(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/system/update/migrate
     * Run pending database migrations manually.
     */
    public function migrate(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Database migrations ran successfully.',
                'data' => [
                    'output' => $output
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Manual migration failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/system/update/log
     * Get the latest update log.
     */
    public function latestLog(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('system_update_logs')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        }

        try {
            $logs = SystemUpdateLog::with('performer:id,name,email')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            $logs = [];
        }

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * GET /api/v1/system/backups
     * List available core backups.
     */
    public function backups(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        return response()->json([
            'success' => true,
            'data' => $this->updateService->getBackups(),
        ]);
    }

    /**
     * POST /api/v1/system/rollback
     * Rollback to a specific backup.
     */
    public function rollback(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        $request->validate([
            'backup_path' => 'required|string',
        ]);

        $backupPath = $request->input('backup_path');

        try {
            $log = $this->updateService->rollback($backupPath, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Rollback completed successfully.',
                'data' => $log,
            ]);
        } catch (\Throwable $e) {
            Log::error('System rollback failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Rollback failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/system/check-update
     * Check for available updates from polycms.org (stub).
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        if ($denied = $this->ensureAdmin($request)) {
            return $denied;
        }

        return response()->json([
            'success' => true,
            'data' => $this->updateService->checkForUpdates(),
        ]);
    }
}
