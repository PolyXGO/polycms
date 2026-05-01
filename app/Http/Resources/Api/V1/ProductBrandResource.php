<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBrandResource extends JsonResource
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

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'frontend_url' => $this->frontend_url,
            'summary' => $this->summary,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'order' => $this->order,
            'image' => $this->image,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];

        if (($includeTree || $includeChildren) && $this->relationLoaded('children')) {
            $data['children'] = ProductBrandResource::collection($this->children);
        }

        if ($this->relationLoaded('parent')) {
            $data['parent'] = $this->parent ? new ProductBrandResource($this->parent) : null;
        }

        return $data;
    }
}
