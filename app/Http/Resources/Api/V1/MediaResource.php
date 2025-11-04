<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'disk' => $this->disk,
            'path' => $this->path,
            'url' => $this->url,
            'size' => $this->size,
            'type' => $this->type,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'width' => $this->width,
            'height' => $this->height,
            'uploader' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
            ] ?? null,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
