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
            'summary' => $this->summary,
            'description' => $this->description,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'path' => $this->path,
            'depth' => $this->depth,
            'image' => $this->image,
            'order' => $this->order,
            'posts_count' => $this->posts_count ?? 0,
            'products_count' => $this->products_count ?? 0,
            'full_name' => $this->full_name,
            'is_root' => $this->isRoot(),
            'is_leaf' => $this->isLeaf(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        if ($includeChildren && $this->relationLoaded('children')) {
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
