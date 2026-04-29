<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Display a listing of media
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::with('user');

        // Path filter (folder navigation) — show files from current folder AND all subfolders
        $path = $request->get('path', '');
        if ($path) {
            $searchPath = 'media/' . trim($path, '/') . '/';
            // Get all files that start with this path (recursive — includes subfolders)
            $query->where('path', 'like', $searchPath . '%');
        } else {
            // Root level: show ALL media files across all folders
            $query->where('path', 'like', 'media/%');
        }

        // Type filter
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = min($request->get('per_page', 15), 100);
        $media = $query->paginate($perPage);

        // Get folders in current path
        $folders = $this->getFoldersInPath($path);

        $response = $this->successResponse(MediaResource::collection($media->items()));
        $responseData = $response->getData();

        // Add folders to response
        $responseData->folders = $folders;

        // Add meta information
        $responseData->meta = [
            'pagination' => [
                'total' => $media->total(),
                'per_page' => $media->perPage(),
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
            ],
        ];

        // Return response with folders in root level
        return response()->json([
            'data' => $responseData->data,
            'folders' => $folders,
            'error' => null,
            'meta' => $responseData->meta,
            'message' => 'Success',
        ], 200);
    }

    /**
     * Get folders in a specific path
     */
    private function getFoldersInPath(string $path): array
    {
        $disk = Storage::disk('public');
        $basePath = 'media/' . ($path ? trim($path, '/') . '/' : '');

        // Ensure basePath doesn't end with double slash
        $basePath = rtrim($basePath, '/') . '/';

        try {
            if (!$disk->exists($basePath)) {
                \Log::info('Path does not exist', ['path' => $basePath]);
                return [];
            }

            $folders = [];
            $directories = $disk->directories($basePath);

            \Log::info('Found directories', [
                'basePath' => $basePath,
                'directories' => $directories,
                'count' => count($directories)
            ]);

            foreach ($directories as $dir) {
                // Get relative path from basePath
                $relativePath = str_replace($basePath, '', $dir);
                $relativePath = rtrim($relativePath, '/');

                // Get immediate subfolders only (not nested)
                $parts = explode('/', $relativePath);
                if (count($parts) === 1) {
                    $folderName = $parts[0];
                    // Include all folders (year folders like 2025, month folders like 11, and custom folders)
                    $folders[] = $folderName;
                }
            }

            // Sort: year folders first, then month folders, then custom folders
            usort($folders, function($a, $b) {
                $aIsYear = preg_match('/^[0-9]{4}$/', $a);
                $bIsYear = preg_match('/^[0-9]{4}$/', $b);
                $aIsMonth = preg_match('/^[0-9]{1,2}$/', $a);
                $bIsMonth = preg_match('/^[0-9]{1,2}$/', $b);

                if ($aIsYear && !$bIsYear) return -1;
                if (!$aIsYear && $bIsYear) return 1;
                if ($aIsMonth && !$bIsMonth && !$aIsYear && !$bIsYear) return -1;
                if (!$aIsMonth && $bIsMonth && !$aIsYear && !$bIsYear) return 1;

                return strnatcasecmp($a, $b);
            });

            \Log::info('Returning folders', ['folders' => $folders]);
            return $folders;
        } catch (\Exception $e) {
            \Log::error('Error getting folders', [
                'path' => $path,
                'basePath' => $basePath,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Store a newly created media (alias for upload)
     */
    public function store(Request $request): JsonResponse
    {
        return $this->upload($request);
    }

    /**
     * Upload a new media file
     */
    public function upload(Request $request): JsonResponse
    {
        // Get PHP upload limits (parse ini_get values like "2M", "8M")
        $parseSize = function($size) {
            $size = trim($size);
            if (empty($size)) return 2 * 1024 * 1024; // Default 2MB
            $last = strtolower($size[strlen($size)-1]);
            $value = (int)$size;
            switch($last) {
                case 'g': $value *= 1024;
                case 'm': $value *= 1024;
                case 'k': $value *= 1024;
            }
            return $value;
        };

        $maxSize = min(
            $parseSize(ini_get('upload_max_filesize')),
            $parseSize(ini_get('post_max_size')),
            10 * 1024 * 1024 // Max 10MB
        );

        $postMaxSize = $parseSize(ini_get('post_max_size'));
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        if ($postMaxSize > 0 && $contentLength > $postMaxSize) {
            return $this->errorResponse(
                'Uploaded payload exceeds server limit (' . round($postMaxSize / 1024 / 1024, 2) . 'MB). Please increase post_max_size and upload_max_filesize in php.ini.',
                'PAYLOAD_TOO_LARGE',
                [],
                422
            );
        }

        // Check if file exists in request
        // Check both hasFile and file() methods
        if (!$request->hasFile('file') && empty($request->allFiles())) {
            \Log::warning('Upload request missing file', [
                'has_file' => $request->hasFile('file'),
                'all_files' => $request->allFiles(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method(),
            ]);
            return $this->errorResponse('No file provided in request.', 'NO_FILE', [], 422);
        }

        $file = $request->file('file');
        if (!$file) {
            // Fallback: some clients may send file key names like files[0], upload, image...
            $allFiles = $request->allFiles();
            $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($allFiles));
            foreach ($iterator as $value) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $file = $value;
                    break;
                }
            }
        }

        if (!$file) {
            \Log::warning('Upload file invalid or missing', [
                'file_exists' => $file !== null,
                'all_files' => $request->allFiles(),
            ]);
            return $this->errorResponse('Invalid file provided in request.', 'INVALID_FILE', [], 422);
        }

        if (!$file->isValid()) {
            $errorCode = $file->getError();
            $errorMessage = match($errorCode) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File size exceeds maximum allowed size (' . round($maxSize / 1024 / 1024, 2) . 'MB). Please increase upload_max_filesize and post_max_size in php.ini.',
                UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                default => 'The file failed to upload.',
            };
            return $this->errorResponse($errorMessage, 'UPLOAD_ERROR', [], 422);
        }

        // Laravel "max" for file uses KB, not bytes.
        $maxSizeKb = max(1, (int) floor($maxSize / 1024));
        $validated = $request->validate([
            'file' => ['nullable', 'file', 'max:' . $maxSizeKb],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
        ]);

        // Apply hooks before upload (ensure file is still valid after filter)
        $filteredFile = \App\Facades\Hook::applyFilters('media.upload.file', $file, $validated);
        if ($filteredFile && $filteredFile instanceof \Illuminate\Http\UploadedFile) {
            $file = $filteredFile;
        }
        $validated = \App\Facades\Hook::applyFilters('media.upload.data', $validated, $file);
        $diskName = 'public'; // Always use public disk for web-accessible files
        $disk = Storage::disk($diskName);

        // Get current path from request (for folder navigation)
        // Can come from query parameter or form data
        $currentPath = $request->get('path', '') ?: $request->input('path', '');

        // If path is provided, upload directly to that folder
        // If no path (root), use date-based organization (Y/m)
        if ($currentPath) {
            // Upload directly to the current folder: media/{path}/filename.ext
            $uploadPath = 'media/' . trim($currentPath, '/');
        } else {
            // Root level: organize by date: media/{Y/m}/filename.ext
            $uploadPath = 'media/' . date('Y/m');
        }

        // Ensure the directory exists and has proper permissions
        if (!$disk->exists($uploadPath)) {
            $disk->makeDirectory($uploadPath, true);
            // Set permissions after creation
            $realPath = storage_path('app/public/' . $uploadPath);
            if (file_exists($realPath)) {
                @chmod($realPath, 0755);
            }
        } else {
            // Ensure existing directory has proper permissions
            $realPath = storage_path('app/public/' . $uploadPath);
            if (file_exists($realPath)) {
                @chmod($realPath, 0755);
            }
        }

        $path = $file->store($uploadPath, $diskName);
        $mimeType = $file->getMimeType();
        $type = $this->determineMediaType($mimeType);

        // Extract metadata for images
        $metadata = [];
        $width = null;
        $height = null;

        if ($type === 'image') {
            try {
                // Try to get image dimensions using getimagesize
                $imagePath = $disk->path($path);
                if (file_exists($imagePath)) {
                    $imageInfo = getimagesize($imagePath);
                    if ($imageInfo !== false) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                        $metadata = [
                            'width' => $width,
                            'height' => $height,
                            'format' => $imageInfo['mime'] ?? $mimeType,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Ignore if image processing fails
            }
        }

        $mediaData = \App\Facades\Hook::applyFilters('media.create.data', [
            'user_id' => $request->user()->id,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'disk' => $diskName, // Use string disk name, not Storage instance
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $type,
            'alt_text' => $validated['alt_text'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'description' => $validated['description'] ?? null,
            'metadata' => $metadata,
            'width' => $width,
            'height' => $height,
        ], $file, $validated);

        $media = Media::create($mediaData);

        // Fire action hook after upload
        \App\Facades\Hook::doAction('media.uploaded', $media, $file, $validated);

        return $this->successResponse(
            new MediaResource($media),
            'Media uploaded successfully',
            201
        );
    }

    /**
     * Display the specified media
     */
    public function show(Media $media): JsonResponse
    {
        $media->load('user');

        return $this->successResponse(new MediaResource($media));
    }

    /**
     * Serve media file (for private disk files)
     */
    public function serve(Media $media)
    {
        // Check if file exists
        if (!$media->path || !$media->disk) {
            abort(404);
        }

        try {
            $disk = Storage::disk($media->disk);

            if (!$disk->exists($media->path)) {
                abort(404);
            }

            // Get file content
            $fileContent = $disk->get($media->path);

            // Determine MIME type
            $mimeType = $media->mime_type ?? mime_content_type($disk->path($media->path)) ?? 'application/octet-stream';

            // Return file response
            return response($fileContent, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $media->file_name . '"')
                ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
        } catch (\Exception $e) {
            \Log::error('Error serving media file', [
                'media_id' => $media->id,
                'path' => $media->path,
                'disk' => $media->disk,
                'error' => $e->getMessage(),
            ]);
            abort(404);
        }
    }

    /**
     * Update the specified media
     */
    public function update(Request $request, Media $media): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
        ]);

        $media->update($validated);

        return $this->successResponse(
            new MediaResource($media),
            'Media updated successfully'
        );
    }

    /**
     * Remove the specified media
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Find media (including soft deleted)
            $media = Media::withTrashed()->findOrFail($id);

            // Store path and disk before deleting record
            $path = $media->path;
            $diskName = $media->disk;
            $mediaId = $media->id;

            // Fire action hook before delete
            \App\Facades\Hook::doAction('media.deleting', $media);

            // Delete file from storage BEFORE deleting record
            $fileDeleted = false;
            if ($path && $diskName) {
                try {
                    $disk = Storage::disk($diskName);

                    // Check if file exists
                    if ($disk->exists($path)) {
                        // Attempt to delete
                        $fileDeleted = $disk->delete($path);

                        \Log::info('Media file deletion attempt', [
                            'media_id' => $mediaId,
                            'path' => $path,
                            'disk' => $diskName,
                            'exists' => true,
                            'deleted' => $fileDeleted,
                            'storage_root' => $disk->path(''),
                        ]);

                        // Verify deletion
                        if ($disk->exists($path)) {
                            \Log::warning('Media file still exists after delete attempt', [
                                'media_id' => $mediaId,
                                'path' => $path,
                                'disk' => $diskName,
                            ]);
                        }
                    } else {
                        \Log::warning('Media file not found in storage', [
                            'media_id' => $mediaId,
                            'path' => $path,
                            'disk' => $diskName,
                            'storage_root' => $disk->path(''),
                            'full_path' => $disk->path($path),
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Exception while deleting media file from storage', [
                        'media_id' => $mediaId,
                        'path' => $path,
                        'disk' => $diskName,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Continue with record deletion even if file deletion fails
                }
            } else {
                \Log::warning('Media has no path or disk', [
                    'media_id' => $mediaId,
                    'path' => $path,
                    'disk' => $diskName,
                ]);
            }

            // Force delete the record (bypass soft delete)
            $media->forceDelete();

            // Fire action hook after delete
            \App\Facades\Hook::doAction('media.deleted', $media);

            $message = $fileDeleted
                ? 'Media deleted successfully'
                : 'Media record deleted, but file may still exist in storage';

            return $this->successResponse(null, $message, 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting media', [
                'media_id' => $media->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                'Failed to delete media: ' . $e->getMessage(),
                'DELETE_ERROR',
                [],
                500
            );
        }
    }

    /**
     * Create a new folder
     */
    public function createFolder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:500'],
        ]);

        $path = $validated['path'];
        $disk = Storage::disk('public');
        $fullPath = 'media/' . ltrim($path, '/');

        // Sanitize path to prevent directory traversal
        $fullPath = str_replace('..', '', $fullPath);
        $fullPath = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $fullPath);

        // Check if folder already exists
        if ($disk->exists($fullPath)) {
            return $this->errorResponse(
                'Folder already exists',
                'FOLDER_EXISTS',
                [],
                409
            );
        }

        try {
            // Create directory with recursive option
            $disk->makeDirectory($fullPath, true);

            // Set full permissions (read, write, execute for owner, group, and others)
            // chmod 755: owner can read/write/execute, group and others can read/execute
            $realPath = storage_path('app/public/' . $fullPath);
            if (file_exists($realPath)) {
                @chmod($realPath, 0755);
            }

            return $this->successResponse(
                ['path' => $fullPath],
                'Folder created successfully',
                201
            );
        } catch (\Exception $e) {
            \Log::error('Error creating folder', [
                'path' => $fullPath,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to create folder: ' . $e->getMessage(),
                'CREATE_FOLDER_ERROR',
                [],
                500
            );
        }
    }

    /**
     * Rename a folder
     */
    public function renameFolder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'old_path' => ['required', 'string', 'max:500'],
            'new_path' => ['required', 'string', 'max:500'],
        ]);

        $oldPath = $validated['old_path'];
        $newPath = $validated['new_path'];
        $disk = Storage::disk('public');
        $fullOldPath = 'media/' . ltrim($oldPath, '/');
        $fullNewPath = 'media/' . ltrim($newPath, '/');

        // Sanitize paths to prevent directory traversal
        $fullOldPath = str_replace('..', '', $fullOldPath);
        $fullNewPath = str_replace('..', '', $fullNewPath);
        $fullOldPath = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $fullOldPath);
        $fullNewPath = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $fullNewPath);

        // Check if old folder exists
        if (!$disk->exists($fullOldPath)) {
            return $this->errorResponse(
                'Folder does not exist',
                'FOLDER_NOT_FOUND',
                [],
                404
            );
        }

        // Check if new folder already exists
        if ($disk->exists($fullNewPath)) {
            return $this->errorResponse(
                'A folder with this name already exists',
                'FOLDER_EXISTS',
                [],
                409
            );
        }

        try {
            // Move/rename the folder
            $disk->move($fullOldPath, $fullNewPath);

            // Update all media records that reference files in the old path
            Media::where('path', 'like', $fullOldPath . '%')
                ->get()
                ->each(function ($media) use ($fullOldPath, $fullNewPath) {
                    $media->path = str_replace($fullOldPath, $fullNewPath, $media->path);
                    $media->save();
                });

            return $this->successResponse(
                ['path' => $fullNewPath],
                'Folder renamed successfully',
                200
            );
        } catch (\Exception $e) {
            \Log::error('Error renaming folder', [
                'old_path' => $fullOldPath,
                'new_path' => $fullNewPath,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to rename folder: ' . $e->getMessage(),
                'RENAME_FOLDER_ERROR',
                [],
                500
            );
        }
    }

    /**
     * Delete a folder
     */
    public function deleteFolder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:500'],
        ]);

        $path = $validated['path'];
        $disk = Storage::disk('public');
        $fullPath = 'media/' . ltrim($path, '/');

        // Sanitize path to prevent directory traversal
        $fullPath = str_replace('..', '', $fullPath);
        $fullPath = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $fullPath);

        // Check if folder exists
        if (!$disk->exists($fullPath)) {
            return $this->errorResponse(
                'Folder does not exist',
                'FOLDER_NOT_FOUND',
                [],
                404
            );
        }

        try {
            // Delete all media records that reference files in this path
            $mediaToDelete = Media::where('path', 'like', $fullPath . '%')->get();
            $deletedCount = $mediaToDelete->count();

            $mediaToDelete->each(function ($media) {
                $media->forceDelete();
            });

            // Delete the folder and all its contents
            $disk->deleteDirectory($fullPath);

            return $this->successResponse(
                ['deleted_media_count' => $deletedCount],
                'Folder deleted successfully',
                200
            );
        } catch (\Exception $e) {
            \Log::error('Error deleting folder', [
                'path' => $fullPath,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to delete folder: ' . $e->getMessage(),
                'DELETE_FOLDER_ERROR',
                [],
                500
            );
        }
    }

    /**
     * Upload media from URL
     */
    public function uploadFromUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $url = $validated['url'];
            $imageContent = @file_get_contents($url);

            if ($imageContent === false) {
                return $this->errorResponse('Failed to download image from URL', 'DOWNLOAD_ERROR', [], 400);
            }

            // Get file info from URL
            $urlInfo = parse_url($url);
            $pathInfo = pathinfo($urlInfo['path'] ?? '');
            $extension = $pathInfo['extension'] ?? 'jpg';
            $filename = ($pathInfo['filename'] ?? 'image') . '.' . $extension;

            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'media_');
            file_put_contents($tempFile, $imageContent);

            // Determine mime type
            $mimeType = mime_content_type($tempFile) ?: 'image/jpeg';

            // Create UploadedFile instance
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile,
                $filename,
                $mimeType,
                null,
                true
            );

            // Create a new request with the uploaded file
            $uploadRequest = new Request([
                'file' => $uploadedFile,
                'alt_text' => $validated['alt_text'] ?? null,
                'caption' => $validated['caption'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            // Merge files for validation
            $uploadRequest->files->set('file', $uploadedFile);
            $uploadRequest->setUserResolver($request->getUserResolver());

            // Use existing upload logic
            return $this->upload($uploadRequest);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload from URL: ' . $e->getMessage(), 'UPLOAD_URL_ERROR', [], 400);
        }
    }

    /**
     * Determine media type from mime type
     */
    protected function determineMediaType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        if (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'document';
        }

        return 'other';
    }
}
