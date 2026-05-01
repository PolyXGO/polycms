<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Media upload(\Illuminate\Http\UploadedFile $file, array $data = [])
 * @method static bool delete(\App\Models\Media $media)
 * @method static string getUrl(\App\Models\Media $media)
 * 
 * @see \App\Services\MediaService
 */
class MediaService extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'media.service';
    }
}

