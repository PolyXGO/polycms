<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ThemeManager;
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
        protected ThemeManager $themeManager
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

        return response()->json([
            'success' => true,
            'data' => $themes->items(),
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

            $zip->extractTo($tempExtractPath);
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

            // Check if theme already exists
            if (File::exists($themePath)) {
                File::deleteDirectory($tempExtractPath);
                Storage::disk('local')->delete($tempPath);
                throw new \Exception("Theme with slug '{$slug}' already exists");
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
     * Delete a theme (optional - can be disabled for safety)
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

        // Prevent deleting active theme
        if ($theme->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete active theme. Please activate another theme first.',
            ], 400);
        }

        // Only delete from database, keep files (safety measure)
        // Uncomment to also delete files:
        // if (File::exists($theme->full_path)) {
        //     File::deleteDirectory($theme->full_path);
        // }

        $theme->update(['status' => 'disabled']);

        return response()->json([
            'success' => true,
            'message' => 'Theme disabled successfully',
        ]);
    }
}