<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $project = null;
        $firstProject = $this->relationLoaded('projects') ? $this->projects->first() : null;
        if (!$firstProject && !empty($this->translation_group_id) && class_exists(\Modules\Polyx\ProjectHub\Models\Project::class)) {
            $productIds = \App\Models\Product::withoutGlobalScope('locale')
                ->where('translation_group_id', $this->translation_group_id)
                ->pluck('id')
                ->toArray();
            if (!empty($productIds)) {
                $firstProject = \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')
                    ->whereHas('products', fn ($q) => $q->withoutGlobalScope('locale')->whereIn('products.id', $productIds))
                    ->where('status', 'published')
                    ->first();
            }
        }
        if ($firstProject) {
            $project = [
                'id' => $firstProject->id,
                'name' => $firstProject->name,
                'slug' => $firstProject->slug,
                'frontend_url' => $firstProject->frontend_url,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'frontend_url' => $this->frontend_url,
            'featured_image_url' => $this->featured_image_url,
            'thumbnail_url' => $this->thumbnail_url,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'status' => $this->status,
            'stock_quantity' => $this->stock_quantity,
            'project' => $project,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
