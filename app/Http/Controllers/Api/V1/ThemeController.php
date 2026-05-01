<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ThemeManager;
use App\Services\TemplateResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemeManager $themeManager,
        protected TemplateResolver $templateResolver,
    ) {}

    /**
     * Get all themes
     */
    public function index(Request $request): JsonResponse
    {
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
    public function sync(): JsonResponse
    {
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
     * Get a specific theme
     */
    public function show(string $slug): JsonResponse
    {
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
     * Delete a theme — removes the database record and optionally files.
     * Broken themes (no files on disk) are simply purged from the DB.
     */
    public function destroy(Request $request, string $slug): JsonResponse
    {
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
    public function snapshot(string $slug): JsonResponse
    {
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
    public function deactivateSub(string $slug): JsonResponse
    {
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
     * Get templates provided by a specific theme
     */
    public function themeTemplates(string $slug): JsonResponse
    {
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