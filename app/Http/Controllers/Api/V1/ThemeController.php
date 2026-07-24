<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\Concerns\AuthorizesPermissions;
use App\Services\ThemeManager;
use App\Services\TemplateResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ThemeController extends Controller
{
    use AuthorizesPermissions;

    public function __construct(
        protected ThemeManager $themeManager,
        protected TemplateResolver $templateResolver,
    ) {}

    /**
     * Get all themes
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'view themes');

        $query = \App\Models\Theme::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or slug
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 25);
        $themes = $query->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate($perPage);

        $items = \App\Facades\Hook::applyFilters('themes.list', $themes->items());

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $themes->currentPage(),
                'last_page' => $themes->lastPage(),
                'per_page' => $themes->perPage(),
                'total' => $themes->total(),
            ],
        ]);
    }

    /**
     * Sync themes from filesystem
     */
    public function sync(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'manage theme options');

        try {
            $synced = $this->themeManager->sync();

            return response()->json([
                'success' => true,
                'message' => 'Themes synced successfully',
                'data' => array_values($synced),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync themes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate a theme
     */
    public function activate(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'activate themes');

        $type = $request->get('type', 'frontend');

        $success = $this->themeManager->activate($slug, $type);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found or cannot be activated',
            ], 404);
        }

        $theme = $this->themeManager->getTheme($slug);

        return response()->json([
            'success' => true,
            'message' => "Theme '{$theme->name}' activated successfully",
            'data' => $theme,
        ]);
    }

    /**
     * Upload a theme ZIP file
     */
    public function upload(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'install themes');

        $validator = Validator::make($request->all(), [
            'theme' => ['required', 'file', 'mimes:zip', 'max:10240'], // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('theme');
        \App\Facades\Hook::doAction('theme.installing', $file);

        $tempPath = $file->storeAs('temp', uniqid('theme_') . '.zip', 'local');

        try {
            $zip = new ZipArchive();
            $zipPath = Storage::disk('local')->path($tempPath);

            if ($zip->open($zipPath) !== true) {
                throw new \Exception('Failed to open ZIP file');
            }

            // Extract to temp directory first
            $tempExtractPath = storage_path('app/temp/' . uniqid('theme_extract_'));
            File::makeDirectory($tempExtractPath, 0755, true);

            // Extract entries individually, normalizing backslash paths
            // Windows-created ZIPs may use backslashes which PHP on Linux
            // treats as literal filename characters instead of directory separators
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if ($entryName === false) {
                    continue;
                }

                // Normalize backslashes to forward slashes
                $normalizedName = str_replace('\\', '/', $entryName);
                
                // Security Fix: Prevent Zip Slip (Path Traversal)
                if (str_contains($normalizedName, '../') || str_contains($normalizedName, '..\\')) {
                    continue; // Skip dangerous paths
                }
                
                $targetFile = $tempExtractPath . '/' . ltrim($normalizedName, '/');

                if (str_ends_with($normalizedName, '/')) {
                    File::ensureDirectoryExists($targetFile);
                    continue;
                }

                File::ensureDirectoryExists(dirname($targetFile));

                // Extract file content
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($targetFile, $content);
                }
            }

            $zip->close();

            // Find theme.json in extracted files
            $manifestPath = $this->findManifest($tempExtractPath);

            if (!$manifestPath) {
                File::deleteDirectory($tempExtractPath);
                Storage::disk('local')->delete($tempPath);
                throw new \Exception('Theme manifest (theme.json) not found in ZIP');
            }

            // Read manifest
            $manifest = json_decode(File::get($manifestPath), true);

            if (!$manifest || empty($manifest['slug'])) {
                File::deleteDirectory($tempExtractPath);
                Storage::disk('local')->delete($tempPath);
                throw new \Exception('Invalid theme manifest');
            }

            $slug = $manifest['slug'];
            $themePath = base_path("themes/{$slug}");

            // Check if theme already exists - if so, delete it to allow update
            if (File::exists($themePath)) {
                // Check if it's the active theme?
                // Actually, overwriting active theme files is standard for updates
                File::deleteDirectory($themePath);
            }

            // Move extracted theme to themes directory
            $extractedRoot = dirname($manifestPath);
            File::moveDirectory($extractedRoot, $themePath);

            // Cleanup
            File::deleteDirectory($tempExtractPath);
            Storage::disk('local')->delete($tempPath);

            // Sync themes to update database
            $this->themeManager->sync();

            // Auto-clear relevant caches so new theme options/views take effect immediately
            try {
                app(\App\Services\CacheService::class)->clear(['view', 'config', 'opcache', 'theme', 'settings', 'template']);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Auto-clear cache after theme upload failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Theme uploaded successfully',
                'data' => [
                    'slug' => $slug,
                    'name' => $manifest['name'] ?? $slug,
                ],
            ]);
        } catch (\Exception $e) {
            // Cleanup on error
            if (isset($tempExtractPath) && File::exists($tempExtractPath)) {
                File::deleteDirectory($tempExtractPath);
            }
            if (isset($tempPath)) {
                Storage::disk('local')->delete($tempPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload theme: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find theme.json manifest in extracted directory
     */
    protected function findManifest(string $directory): ?string
    {
        // Check root directory first
        $rootManifest = $directory . '/theme.json';
        if (File::exists($rootManifest)) {
            return $rootManifest;
        }

        // Search in subdirectories (common ZIP structure)
        $files = File::allFiles($directory);
        foreach ($files as $file) {
            if ($file->getFilename() === 'theme.json') {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * Recursively add theme directory contents to a ZIP archive.
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $path, string $basePath): void
    {
        $excludedDirectories = \App\Facades\Hook::applyFilters('theme.download.excluded_directories', [
            '.git',
            'node_modules',
            'vendor',
            'heraspec',
            '.agents',
            '.ai',
        ]);

        $excludedFiles = \App\Facades\Hook::applyFilters('theme.download.excluded_files', [
            '.DS_Store',
            '*.zip',
            '*.bak',
            '*.tmp',
            'debug_*.php',
            'test_*.php',
            'scratch-*.php',
        ]);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );

        foreach ($iterator as $fileInfo) {
            $relativePath = ltrim(str_replace($path, '', $fileInfo->getPathname()), DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);

            if ($this->shouldExcludeThemeDownloadPath($relativePath, $excludedDirectories, $excludedFiles)) {
                continue;
            }

            $zipPath = $basePath . '/' . $relativePath;

            if ($fileInfo->isDir()) {
                $zip->addEmptyDir($zipPath);
                continue;
            }

            $zip->addFile($fileInfo->getPathname(), $zipPath);
        }
    }

    protected function shouldExcludeThemeDownloadPath(string $relativePath, array $directories, array $files): bool
    {
        $segments = explode('/', $relativePath);
        foreach ($segments as $segment) {
            if (in_array($segment, $directories, true)) {
                return true;
            }
        }

        $basename = basename($relativePath);
        foreach ($files as $pattern) {
            if (fnmatch($pattern, $basename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a specific theme
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'view themes');

        $theme = $this->themeManager->getTheme($slug);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $theme,
        ]);
    }
    /**
     * Download a theme as ZIP.
     */
    public function download(Request $request, string $slug): JsonResponse|BinaryFileResponse
    {
        $this->authorizePermission($request, 'view themes');

        $theme = $this->themeManager->getTheme($slug);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
            ], 404);
        }

        $themePath = base_path($theme->path);

        if (!$theme->path || !File::exists($themePath) || !File::isDirectory($themePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Theme files not found',
            ], 404);
        }

        \App\Facades\Hook::doAction('theme.downloading', $theme, $request->user());

        $tempFile = tempnam(sys_get_temp_dir(), 'theme_');
        if ($tempFile === false) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create temporary file for download',
            ], 500);
        }

        $zipPath = $tempFile . '.zip';
        if (!@rename($tempFile, $zipPath)) {
            @unlink($tempFile);

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

        $basePath = basename($themePath);
        $zip->addEmptyDir($basePath);
        $this->addDirectoryToZip($zip, $themePath, $basePath);
        $zip->close();

        $fileName = $theme->slug . '-' . ($theme->version ?: 'theme') . '.zip';
        $fileName = \App\Facades\Hook::applyFilters('theme.download.filename', $fileName, $theme);

        \App\Facades\Hook::doAction('theme.downloaded', $theme, $request->user(), $zipPath);

        return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Delete a theme - removes the database record and optionally files.
     * Broken themes (no files on disk) are simply purged from the DB.
     */
    public function destroy(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'delete themes');

        $theme = $this->themeManager->getTheme($slug);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
            ], 404);
        }

        \App\Facades\Hook::doAction('theme.deleting', $theme);

        // Prevent deleting active theme
        if ($theme->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete active theme. Please activate another theme first.',
            ], 400);
        }

        // Delete theme files from disk if they exist
        $fullPath = base_path($theme->path);
        if (File::exists($fullPath)) {
            File::deleteDirectory($fullPath);
        }

        // Delete the database record so sync() won't re-create it
        $theme->delete();

        // Clear theme cache
        $this->themeManager->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Theme deleted successfully',
        ]);
    }

    // =====================================================================
    // Multi-Theme Endpoints
    // =====================================================================

    /**
     * Get snapshot info for a theme (determines dialog type in frontend)
     */
    public function snapshot(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'view themes');

        $snapshot = $this->themeManager->getThemeSnapshot($slug);

        return response()->json([
            'success' => true,
            'data' => $snapshot ?? ['has_snapshot' => false],
        ]);
    }

    /**
     * Set a theme as Main Theme
     */
    public function setMain(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'activate themes');

        $type = $request->get('type', 'frontend');
        $restoreMode = $request->get('restore_mode', 'restore');

        // Validate restore_mode
        if (!in_array($restoreMode, ['restore', 'reset', 'skip'], true)) {
            $restoreMode = 'restore';
        }

        \App\Facades\Hook::doAction('theme.activating', $slug, $type, 'main');

        $success = $this->themeManager->setAsMain($slug, $type, $restoreMode);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found or cannot be set as main',
            ], 404);
        }

        $theme = $this->themeManager->getTheme($slug);

        $message = "Theme '{$theme->name}' set as Main Theme";
        if ($restoreMode === 'restore' && ($theme->meta['config_snapshot'] ?? null)) {
            $message .= ' (homepage restored)';
        } elseif ($restoreMode === 'reset') {
            $message .= ' (homepage reset to default)';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $theme,
        ]);
    }

    /**
     * Activate a theme as Sub Theme
     */
    public function activateSub(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'activate themes');

        $type = $request->get('type', 'frontend');

        \App\Facades\Hook::doAction('theme.activating', $slug, $type, 'sub');

        $success = $this->themeManager->activateSubTheme($slug, $type);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found or cannot be activated as sub theme',
            ], 404);
        }

        $theme = $this->themeManager->getTheme($slug);

        return response()->json([
            'success' => true,
            'message' => "Theme '{$theme->name}' activated as Sub Theme",
            'data' => $theme,
        ]);
    }

    /**
     * Deactivate a Sub Theme
     */
    public function deactivateSub(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'activate themes');

        \App\Facades\Hook::doAction('theme.deactivating', $slug);

        $success = $this->themeManager->deactivateSubTheme($slug);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found or cannot be deactivated (Main Theme cannot be deactivated)',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sub Theme deactivated successfully',
        ]);
    }

    /**
     * Get available templates from all active themes for a view type
     */
    public function templates(Request $request): JsonResponse
    {
        $this->authorizePermission($request, 'view themes');

        $viewType = $request->get('view_type');

        if (!$viewType) {
            return response()->json([
                'success' => false,
                'message' => 'view_type parameter is required',
            ], 422);
        }

        $templates = $this->templateResolver->getAvailableTemplates($viewType);

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Switch template theme for a given entity
     */
    public function switchTemplate(Request $request): JsonResponse
    {
        $user = \Illuminate\Support\Facades\Auth::guard('sanctum')->user() 
            ?: \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'entity_type' => 'required|string|in:product,post,page,category,product_category',
            'entity_id' => 'required|integer',
            'template_theme' => 'nullable|string',
        ]);

        $entityType = $validated['entity_type'];
        $entityId = (int)$validated['entity_id'];
        $templateTheme = $validated['template_theme'] ?: null; // convert empty string to null

        $model = null;
        switch ($entityType) {
            case 'product':
                $model = \App\Models\Product::findOrFail($entityId);
                break;
            case 'post':
                $model = \App\Models\Post::where('id', $entityId)->where('type', 'post')->firstOrFail();
                break;
            case 'page':
                $model = \App\Models\Post::where('id', $entityId)->where('type', 'page')->firstOrFail();
                break;
            case 'category':
                $model = \App\Models\Category::findOrFail($entityId);
                break;
            case 'product_category':
                $model = \App\Models\ProductCategory::findOrFail($entityId);
                break;
        }

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Entity not found',
            ], 404);
        }

        // Perform authorization check
        if (!$user->can('apply layout templates') && !$user->can('update', $model)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this entity',
            ], 403);
        }

        // Update template_theme
        $model->template_theme = $templateTheme;
        $model->save();

        // Clear relevant cache
        try {
            app(\App\Services\CacheService::class)->clear(['view', 'template']);
        } catch (\Throwable $e) {
            // ignore
        }

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully',
        ]);
    }

    /**
     * Get templates provided by a specific theme
     */
    public function themeTemplates(Request $request, string $slug): JsonResponse
    {
        $this->authorizePermission($request, 'view themes');

        $theme = $this->themeManager->getTheme($slug);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
            ], 404);
        }

        $registry = $theme->template_registry ?? $this->themeManager->discoverThemeTemplates($slug);

        return response()->json([
            'success' => true,
            'data' => [
                'theme' => [
                    'slug' => $theme->slug,
                    'name' => $theme->name,
                    'role' => $theme->role,
                ],
                'templates' => $registry,
            ],
        ]);
    }
}
