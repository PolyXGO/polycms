<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
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
            'summary' => $this->summary,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'image' => $this->image,
            'order' => $this->order,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];

        if (isset($this->usage_count)) {
            $data['usage_count'] = (int) $this->usage_count;
        }

        if (($includeTree || $includeChildren) && $this->relationLoaded('children')) {
            $data['children'] = ProductCategoryResource::collection($this->children);
        }

        if ($includeAncestors && $this->relationLoaded('ancestors')) {
            $data['ancestors'] = ProductCategoryResource::collection($this->ancestors);
        }

        if ($this->relationLoaded('parent')) {
            $data['parent'] = $this->parent ? new ProductCategoryResource($this->parent) : null;
        }

        return $data;
    }
}
