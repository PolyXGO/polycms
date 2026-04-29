<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\Hook;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Media Service - Handles media operations with hooks support
 * 
 * This service provides a centralized way to handle media operations
 * and allows modules/themes to extend functionality via hooks.
 */
class MediaService
{
    /**
     * Upload a media file
     * 
     * @param UploadedFile $file
     * @param array $data Additional data (alt_text, caption, description)
     * @return Media
     */
    public function upload(UploadedFile $file, array $data = []): Media
    {
        // Apply filter before upload
        $file = Hook::applyFilters('media.upload.file', $file, $data);
        $data = Hook::applyFilters('media.upload.data', $data, $file);

        $disk = config('filesystems.default', 'public');
        $path = $file->store('media/' . date('Y/m'), $disk);
        $mimeType = $file->getMimeType();
        $type = $this->determineMediaType($mimeType);

        // Extract metadata
        $metadata = [];
        $width = null;
        $height = null;

        if ($type === 'image') {
            try {
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

        // Apply filter before creating media record
        $mediaData = Hook::applyFilters('media.create.data', [
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'disk' => $disk,
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $type,
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => $metadata,
            'width' => $width,
            'height' => $height,
        ], $file, $data);

        $media = Media::create($mediaData);

        // Fire action hook after upload
        Hook::doAction('media.uploaded', $media, $file, $data);

        return $media;
    }

    /**
     * Delete media
     * 
     * @param Media $media
     * @return bool
     */
    public function delete(Media $media): bool
    {
        // Apply filter before delete
        $shouldDelete = Hook::applyFilters('media.delete.should', true, $media);
        
        if (!$shouldDelete) {
            return false;
        }

        // Fire action hook before delete
        Hook::doAction('media.deleting', $media);

        // Delete file from storage
        Storage::disk($media->disk)->delete($media->path);

        // Delete record
        $deleted = $media->delete();

        // Fire action hook after delete
        Hook::doAction('media.deleted', $media);

        return $deleted;
    }

    /**
     * Get media URL
     * 
     * @param Media $media
     * @return string
     */
    public function getUrl(Media $media): string
    {
        $url = Storage::disk($media->disk)->url($media->path);
        
        // Apply filter to allow custom URL generation
        return Hook::applyFilters('media.url', $url, $media);
    }

    /**
     * Determine media type from mime type
     * 
     * @param string $mimeType
     * @return string
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
        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])) {
            return 'document';
        }

        return 'other';
    }
}

