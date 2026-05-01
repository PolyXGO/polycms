<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $includeTree = $request->boolean('tree', false);
        $includeChildren = $request->boolean('children', false);
        $includeAncestors = $request->boolean('ancestors', false);

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'frontend_url' => $this->frontend_url,
            'type' => $this->type,
            'summary' => $this->summary,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'image' => $this->image,
            'order' => $this->order,
            'template_theme' => $this->template_theme,
            'meta' => $this->meta ?? [],
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
            'posts_count' => $this->posts_count ?? 0,
        ];

        if (isset($this->usage_count)) {
            $data['usage_count'] = (int) $this->usage_count;
        }

        if (($includeTree || $includeChildren) && $this->relationLoaded('children')) {
            $data['children'] = CategoryResource::collection($this->children);
        }

        if ($includeAncestors && $this->relationLoaded('ancestors')) {
            $data['ancestors'] = CategoryResource::collection($this->ancestors);
        }

        if ($this->relationLoaded('parent')) {
            $data['parent'] = $this->parent ? new CategoryResource($this->parent) : null;
        }

        return $data;
    }
}
