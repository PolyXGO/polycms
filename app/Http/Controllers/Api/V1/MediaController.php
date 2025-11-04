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

        $response = $this->successResponse(MediaResource::collection($media->items()));
        $response->getData()->meta = [
            'pagination' => [
                'total' => $media->total(),
                'per_page' => $media->perPage(),
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
            ],
        ];
        return $response;
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
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:' . (10 * 1024)], // 10MB max
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $disk = config('filesystems.default', 'public');
        $path = $file->store('media/' . date('Y/m'), $disk);
        $mimeType = $file->getMimeType();
        $type = $this->determineMediaType($mimeType);

        // Extract metadata for images
        $metadata = [];
        $width = null;
        $height = null;

        if ($type === 'image') {
            try {
                // Try to get image dimensions using getimagesize
                $imagePath = Storage::disk($disk)->path($path);
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

        $media = Media::create([
            'user_id' => $request->user()->id,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'disk' => $disk,
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $type,
            'alt_text' => $validated['alt_text'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'description' => $validated['description'] ?? null,
            'metadata' => $metadata,
            'width' => $width,
            'height' => $height,
        ]);

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
    public function destroy(Media $media): JsonResponse
    {
        // Delete file from storage
        Storage::disk($media->disk)->delete($media->path);

        // Delete record
        $media->delete();

        return $this->successResponse(null, 'Media deleted successfully', 204);
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
