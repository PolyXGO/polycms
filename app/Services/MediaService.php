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
                    $imageInfo = @getimagesize($imagePath);
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

        // Generate thumbnails if image
        if ($media->type === 'image') {
            $this->generateThumbnails($media);
        }

        // Fire action hook after upload
        Hook::doAction('media.uploaded', $media, $file, $data);

        return $media;
    }

    /**
     * Calculate proportional dimensions keeping original aspect ratio
     */
    public function calculateProportionalDimensions(int $origW, int $origH, int $maxW, int $maxH): array
    {
        if ($origW <= 0 || $origH <= 0) {
            return [$maxW, $maxH];
        }
        if ($origW <= $maxW && $origH <= $maxH) {
            return [$origW, $origH];
        }

        $ratio = min($maxW / $origW, $maxH / $origH);
        return [
            (int) max(1, round($origW * $ratio)),
            (int) max(1, round($origH * $ratio))
        ];
    }

    /**
     * Generate WebP proportional thumbnails for a media item
     */
    public function generateThumbnails(Media $media): array
    {
        if ($media->type !== 'image') {
            return [];
        }

        $settings = app(\App\Services\SettingsService::class);
        $enableThumbnails = $settings->get('media_enable_thumbnails', true);
        if (!$enableThumbnails) {
            return [];
        }

        $sourcePath = Storage::disk($media->disk)->path($media->path);
        if (!file_exists($sourcePath)) {
            return [];
        }

        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false) {
            return [];
        }

        $origW = $imageInfo[0];
        $origH = $imageInfo[1];

        $quality = (int) $settings->get('media_image_quality', 75);
        $thumbW = (int) $settings->get('media_thumb_width', 150);
        $thumbH = (int) $settings->get('media_thumb_height', 150);
        $featW = (int) $settings->get('media_featured_width', 565);
        $featH = (int) $settings->get('media_featured_height', 375);

        $dir = dirname($media->path);
        if (str_ends_with(str_replace('\\', '/', $dir), '/thumbs')) {
            $dir = dirname($dir);
        }
        $filename = pathinfo($media->file_name, PATHINFO_FILENAME);

        $presets = [
            'thumb' => [$thumbW, $thumbH],
            'featured' => [$featW, $featH],
        ];

        $thumbnails = $media->metadata['thumbnails'] ?? [];

        foreach ($presets as $preset => $dims) {
            [$targetW, $targetH] = $this->calculateProportionalDimensions($origW, $origH, $dims[0], $dims[1]);
            $relativeThumbPath = $dir . '/thumbs/' . $filename . '_' . $preset . '_' . $targetW . 'x' . $targetH . '.webp';
            $fullThumbPath = Storage::disk($media->disk)->path($relativeThumbPath);

            $thumbDir = dirname($fullThumbPath);
            if (!file_exists($thumbDir)) {
                @mkdir($thumbDir, 0755, true);
            }

            if ($this->resizeAndSaveWebp($sourcePath, $fullThumbPath, $targetW, $targetH, $quality)) {
                $thumbnails[$preset] = $relativeThumbPath;
            }
        }

        $meta = $media->metadata ?? [];
        $meta['thumbnails'] = $thumbnails;
        $media->metadata = $meta;
        $media->save();

        return $thumbnails;
    }

    /**
     * Prepare clean, unique, and safe destination filename based on Media Settings configuration.
     *
     * Modes:
     * 1. media_convert_uuid = true -> Convert filename to UUID v4 (e.g. 550e8400-e29b-41d4-a716-446655440000.png)
     * 2. media_use_original_name = true -> Keep original filename (sanitized for security/filesystem safety)
     * 3. Default (media_use_original_name = false) -> Convert to URL-friendly Latin slug without accents/unicode & collapse hyphens.
     */
    public function prepareUploadFileName(string $originalName, string $uploadPath, string $diskName = 'public'): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $filename = pathinfo($originalName, PATHINFO_FILENAME);

        $settings = app(SettingsService::class);
        $convertUuid = (bool) $settings->get('media_convert_uuid', false);
        $useOriginalName = (bool) $settings->get('media_use_original_name', false);

        if ($convertUuid) {
            $slug = (string) \Illuminate\Support\Str::uuid();
        } elseif ($useOriginalName) {
            // Keep original filename, but replace dangerous filesystem characters
            $slug = preg_replace('/[\/\\\\:\*\?"<>\|]/', '_', $filename);
        } else {
            // Default: Latin slug without unicode accents, spaces to single hyphen, collapse --
            $asciiName = \Illuminate\Support\Str::ascii($filename);
            $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $asciiName);
            $slug = trim(preg_replace('/-+/', '-', $slug), '-');
            $slug = strtolower($slug);
        }

        if (empty($slug)) {
            $slug = 'file-' . \Illuminate\Support\Str::random(6);
        }

        $finalFileName = $slug . ($extension ? '.' . $extension : '');
        $targetPath = trim($uploadPath, '/') . '/' . $finalFileName;

        // Ensure uniqueness in the destination folder
        $counter = 1;
        while (Storage::disk($diskName)->exists($targetPath)) {
            $finalFileName = $slug . '-' . $counter . ($extension ? '.' . $extension : '');
            $targetPath = trim($uploadPath, '/') . '/' . $finalFileName;
            $counter++;
        }

        return $finalFileName;
    }

    /**
     * Alias for prepareUploadFileName
     */
    public function sanitizeFileName(string $originalName, string $uploadPath, string $diskName = 'public'): string
    {
        return $this->prepareUploadFileName($originalName, $uploadPath, $diskName);
    }

    /**
     * Get variant URL (thumb, featured, or full) for any given raw image URL or path.
     * Automatically handles default placeholders, legacy files, and database media items.
     */
    public function getVariantUrl(?string $rawUrl, string $variant = 'featured'): ?string
    {
        if (empty($rawUrl)) {
            return null;
        }

        if ($variant === 'full' || $variant === 'original') {
            return $this->formatUrlDomain($rawUrl);
        }

        // Avoid re-processing thumbnail URLs or generated webp variants
        if (str_contains($rawUrl, '/thumbs/') || preg_match('/_(thumb|featured)_\d+x\d+\.webp$/i', $rawUrl)) {
            return $this->formatUrlDomain($rawUrl);
        }

        // Clean path to locate Media record
        $parsedPath = parse_url($rawUrl, PHP_URL_PATH) ?? '';
        $extension = strtolower(pathinfo($parsedPath, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            return $this->formatUrlDomain($rawUrl);
        }

        // Extract relative storage path
        $cleanPath = ltrim(str_replace(['/storage/', '\\'], ['', '/'], $parsedPath), '/');

        // Prevent thumbs/ directory in cleanPath if already pointing to a thumbnail
        if (str_contains($cleanPath, 'thumbs/')) {
            return $this->formatUrlDomain($rawUrl);
        }

        // Look up in Media DB table
        $media = Media::where('path', $cleanPath)
            ->orWhere('path', 'like', '%' . basename($cleanPath))
            ->first();

        if ($media && $media->type === 'image') {
            $thumbnails = $media->metadata['thumbnails'] ?? [];
            if (!empty($thumbnails[$variant])) {
                $variantPath = $thumbnails[$variant];
                return $this->formatUrlDomain(Storage::disk($media->disk)->url($variantPath));
            }

            // Generate thumbnails on the fly if missing
            $generated = $this->generateThumbnails($media);
            if (!empty($generated[$variant])) {
                return $this->formatUrlDomain(Storage::disk($media->disk)->url($generated[$variant]));
            }
        }

        // Fallback for static default images / non-DB files located in storage
        $diskPath = storage_path('app/public/' . $cleanPath);
        if (file_exists($diskPath)) {
            $dir = dirname($cleanPath);
            if (str_ends_with(str_replace('\\', '/', $dir), '/thumbs')) {
                $dir = dirname($dir);
            }
            $filename = pathinfo($cleanPath, PATHINFO_FILENAME);
            
            $settings = app(SettingsService::class);
            $quality = (int) $settings->get('media_image_quality', 75);
            $thumbW = (int) $settings->get('media_thumb_width', 150);
            $thumbH = (int) $settings->get('media_thumb_height', 150);
            $featW = (int) $settings->get('media_featured_width', 565);
            $featH = (int) $settings->get('media_featured_height', 375);

            $dims = $variant === 'thumb' ? [$thumbW, $thumbH] : [$featW, $featH];

            $imageInfo = @getimagesize($diskPath);
            if ($imageInfo !== false) {
                [$targetW, $targetH] = $this->calculateProportionalDimensions($imageInfo[0], $imageInfo[1], $dims[0], $dims[1]);
                $relativeThumbPath = $dir . '/thumbs/' . $filename . '_' . $variant . '_' . $targetW . 'x' . $targetH . '.webp';
                $fullThumbPath = storage_path('app/public/' . $relativeThumbPath);

                if (file_exists($fullThumbPath)) {
                    return $this->formatUrlDomain(Storage::disk('public')->url($relativeThumbPath));
                }

                @mkdir(dirname($fullThumbPath), 0755, true);
                if ($this->resizeAndSaveWebp($diskPath, $fullThumbPath, $targetW, $targetH, $quality)) {
                    return $this->formatUrlDomain(Storage::disk('public')->url($relativeThumbPath));
                }
            }
        }

        return $this->formatUrlDomain($rawUrl);
    }

    /**
     * Format URL domain to match the current request host (fixes http://localhost vs http://127.0.0.1:8000 port mismatches)
     */
    public function formatUrlDomain(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }

        if (request()->hasHeader('Host') || app()->runningInConsole() === false) {
            $currentOrigin = request()->getSchemeAndHttpHost();
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i', $url)) {
                return preg_replace('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i', $currentOrigin, $url);
            }
        }

        return $url;
    }

    /**
     * Resize image and save as WebP with alpha transparency support
     */
    public function resizeAndSaveWebp(string $sourcePath, string $targetPath, int $targetW, int $targetH, int $quality = 75): bool
    {
        try {
            $info = @getimagesize($sourcePath);
            if ($info === false) return false;

            $mime = $info['mime'];
            $srcImage = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($sourcePath),
                'image/png' => @imagecreatefrompng($sourcePath),
                'image/webp' => @imagecreatefromwebp($sourcePath),
                'image/gif' => @imagecreatefromgif($sourcePath),
                default => null,
            };

            if (!$srcImage) return false;

            $origW = $info[0];
            $origH = $info[1];

            $dstImage = imagecreatetruecolor($targetW, $targetH);
            if (!$dstImage) {
                imagedestroy($srcImage);
                return false;
            }

            // Preserve PNG / WebP transparency
            if (in_array($mime, ['image/png', 'image/webp', 'image/gif'])) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $targetW, $targetH, $transparent);
            }

            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

            $success = false;
            if (function_exists('imagewebp')) {
                $success = imagewebp($dstImage, $targetPath, $quality);
            } else if (function_exists('imagejpeg')) {
                $success = imagejpeg($dstImage, $targetPath, $quality);
            }

            imagedestroy($srcImage);
            imagedestroy($dstImage);

            return $success;
        } catch (\Throwable $e) {
            return false;
        }
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

        // Delete thumbnails if exist
        if (!empty($media->metadata['thumbnails'])) {
            foreach ($media->metadata['thumbnails'] as $thumbPath) {
                Storage::disk($media->disk)->delete($thumbPath);
            }
        }

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

