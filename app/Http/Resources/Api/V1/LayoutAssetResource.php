<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Services\LayoutAssetPreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LayoutAssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $previewService = app(LayoutAssetPreviewService::class);

        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'name' => $this->name,
            'slug' => $this->slug,
            'key' => $this->key,
            'category' => $this->category,
            'description' => $this->description,
            'layout' => $this->layout,
            'content_raw' => $this->content_raw,
            'content_html' => $this->content_html,
            'preview_url' => $previewService->buildPreviewUrl($this->resource),
            'preview_image' => $this->preview_image,
            'meta' => $this->meta ?? [],
            'applies_to' => $this->applies_to ?? [],
            'is_system' => (bool) $this->is_system,
            'source_type' => $this->source_type,
            'source_name' => $this->source_name,
            'author' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null,
            'assigned_posts_count' => $this->whenCounted('assignedPosts'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
