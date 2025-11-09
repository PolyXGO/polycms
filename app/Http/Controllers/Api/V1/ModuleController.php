<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ModuleManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleManager $moduleManager
    ) {}

    /**
     * Get all discovered modules
     */
    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $modules = $this->moduleManager->discoverModules();

        // Convert to array for JSON response
        $modulesArray = [];
        foreach ($modules as $key => $module) {
            $modulesArray[] = [
                'key' => $key,
                'name' => $module['name'],
                'vendor' => $module['vendor'],
                'module' => $module['module'],
                'version' => $module['version'],
                'description' => $module['description'],
                'enabled' => $module['enabled'],
                'has_provider' => !empty($module['provider']),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => array_values($modulesArray),
        ]);
    }

    /**
     * Enable a module
     */
    public function enable(Request $request, string $moduleKey): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $success = $this->moduleManager->enableModule($moduleKey);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Module enabled successfully',
        ]);
    }

    /**
     * Disable a module
     */
    public function disable(Request $request, string $moduleKey): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $success = $this->moduleManager->disableModule($moduleKey);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Module disabled successfully',
        ]);
    }

    /**
     * Delete a module (removes module files - use with caution)
     */
    public function destroy(Request $request, string $moduleKey): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $module = $this->moduleManager->getModule($moduleKey);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found',
            ], 404);
        }

        // Disable module first if enabled
        if ($module['enabled']) {
            $this->moduleManager->disableModule($moduleKey);
        }

        // Delete module directory
        try {
            $modulePath = $module['path'];
            if (file_exists($modulePath) && is_dir($modulePath)) {
                // Use Laravel's File facade for safer deletion
                \Illuminate\Support\Facades\File::deleteDirectory($modulePath);
            }

            // Clear cache
            $this->moduleManager->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a module as ZIP
     */
    public function download(Request $request, string $moduleKey): JsonResponse|BinaryFileResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $module = $this->moduleManager->getModule($moduleKey);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found',
            ], 404);
        }

        $modulePath = $module['path'] ?? null;

        if (!$modulePath || !File::exists($modulePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Module files not found',
            ], 404);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'module_');
        if ($tempFile === false) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create temporary file for download',
            ], 500);
        }

        $zipPath = $tempFile . '.zip';
        if (!@rename($tempFile, $zipPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to prepare temporary ZIP file',
            ], 500);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize ZIP archive',
            ], 500);
        }

        $vendorDir = basename(dirname($modulePath));
        $moduleDir = basename($modulePath);
        $basePath = $vendorDir . '/' . $moduleDir;

        $zip->addEmptyDir($basePath);
        $this->addDirectoryToZip($zip, $modulePath, $basePath);
        $zip->close();

        $fileName = $moduleDir . '-' . ($module['version'] ?? 'module') . '.zip';

        return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Upload and install a module from ZIP
     */
    public function upload(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'module' => ['required', 'file', 'mimes:zip', 'max:51200'], // 50 MB
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $uploadedFile = $request->file('module');

        if (!$uploadedFile) {
            return response()->json([
                'success' => false,
                'message' => 'No module file uploaded',
            ], 422);
        }

        $disk = Storage::disk('local');
        $tempDirectory = 'module_uploads';
        $tempStoredPath = $uploadedFile->store($tempDirectory, 'local');
        $tempPath = $disk->path($tempStoredPath);
        $extractionPath = $disk->path($tempDirectory . '/' . uniqid('extract_', true));

        try {
            File::ensureDirectoryExists($extractionPath);

            $zip = new ZipArchive();
            if ($zip->open($tempPath) !== true) {
                throw new \RuntimeException('Unable to open uploaded ZIP file');
            }

            // Guard against directory traversal in ZIP entries
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if ($entryName === false) {
                    throw new \RuntimeException('Failed to read ZIP entry');
                }
                if (str_starts_with($entryName, '/') || str_contains($entryName, '..')) {
                    throw new \RuntimeException('ZIP archive contains invalid paths');
                }
            }

            if (!$zip->extractTo($extractionPath)) {
                throw new \RuntimeException('Failed to extract module ZIP');
            }

            $zip->close();

            $manifestPath = $this->findManifest($extractionPath);

            if (!$manifestPath) {
                throw new \RuntimeException('Module manifest (module.json) not found in archive');
            }

            $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($manifest)) {
                throw new \RuntimeException('Invalid module manifest contents');
            }

            $vendor = $manifest['vendor'] ?? null;
            $moduleName = $manifest['module'] ?? null;

            if (!$vendor || !$moduleName) {
                throw new \RuntimeException('Module manifest must define vendor and module');
            }

            if (!$this->isValidIdentifier($vendor) || !$this->isValidIdentifier($moduleName)) {
                throw new \RuntimeException('Vendor or module name contains invalid characters');
            }

            $moduleKey = "{$vendor}.{$moduleName}";
            $modulesPath = $this->moduleManager->getModulesPath();
            $targetPath = $modulesPath . '/' . $vendor . '/' . $moduleName;

            if (File::exists($targetPath)) {
                throw new \RuntimeException("Module {$moduleKey} already exists");
            }

            File::ensureDirectoryExists(dirname($targetPath));

            $extractedRoot = dirname($manifestPath);

            if (!File::isDirectory($extractedRoot)) {
                throw new \RuntimeException('Invalid extracted module structure');
            }

            if (!File::moveDirectory($extractedRoot, $targetPath)) {
                throw new \RuntimeException('Failed to move module into modules directory');
            }

            // Cleanup extraction directory (may still contain artifacts like __MACOSX)
            if (File::exists($extractionPath)) {
                File::deleteDirectory($extractionPath);
            }
            $disk->delete($tempStoredPath);

            // Refresh module cache and enable the module
            $this->moduleManager->clearCache();
            $this->moduleManager->discoverModules();

            $this->moduleManager->enableModule($moduleKey);

            $module = $this->moduleManager->getModule($moduleKey);

            return response()->json([
                'success' => true,
                'message' => 'Module uploaded and activated successfully',
                'data' => [
                    'key' => $moduleKey,
                    'module' => $module['module'] ?? $moduleName,
                    'vendor' => $module['vendor'] ?? $vendor,
                    'version' => $module['version'] ?? ($manifest['version'] ?? '1.0.0'),
                ],
            ], 201);
        } catch (\Throwable $e) {
            if (isset($zip) && $zip instanceof ZipArchive) {
                $zip->close();
            }

            if (isset($extractionPath) && File::exists($extractionPath)) {
                File::deleteDirectory($extractionPath);
            }

            if (isset($tempStoredPath)) {
                $disk->delete($tempStoredPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload module: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ensure the current user is an admin
     */
    protected function ensureAdmin(Request $request): ?JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: admin access required',
            ], 403);
        }

        return null;
    }

    /**
     * Recursively add directory contents to a ZipArchive
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $path, string $basePath): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            $relativePath = ltrim(str_replace($path, '', $fileInfo->getPathname()), DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);
            $zipPath = $basePath . '/' . $relativePath;

            if ($fileInfo->isDir()) {
                $zip->addEmptyDir($zipPath);
            } else {
                $zip->addFile($fileInfo->getPathname(), $zipPath);
            }
        }
    }

    /**
     * Find module.json manifest inside extracted ZIP directory
     */
    protected function findManifest(string $directory): ?string
    {
        $rootManifest = $directory . '/module.json';
        if (File::exists($rootManifest)) {
            return $rootManifest;
        }

        $files = File::allFiles($directory);
        foreach ($files as $file) {
            if ($file->getFilename() === 'module.json') {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * Validate vendor/module identifiers
     */
    protected function isValidIdentifier(string $value): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_\-]+$/', $value);
    }
}
