<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'description_blocks' => $this->description_blocks,
            'description_html' => $this->description_html,
            // Commonly-used flattened fields for admin forms
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'compare_at_price' => $this->sale_price ? (float) $this->sale_price : null,
            'cost_price' => $this->cost_price ? (float) $this->cost_price : null,
            'stock_status' => $this->stock_status,
            'stock_quantity' => $this->stock_quantity,
            'manage_stock' => (bool) $this->manage_stock,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'pricing' => [
                'price' => (float) $this->price,
                'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
                'cost_price' => $this->cost_price ? (float) $this->cost_price : null,
                'effective_price' => (float) $this->effective_price,
                'on_sale' => $this->isOnSale(),
            ],
            'inventory' => [
                'stock_status' => $this->stock_status,
                'stock_quantity' => $this->stock_quantity,
                'manage_stock' => $this->manage_stock,
                'stock_low_threshold' => $this->stock_low_threshold,
                'in_stock' => $this->isInStock(),
            ],
            'status' => $this->status,
            'featured' => $this->featured,
            'order' => $this->order,
            'meta' => [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
                'keywords' => $this->meta_keywords,
            ],
            'dimensions' => [
                'weight' => $this->weight ? (float) $this->weight : null,
                'length' => $this->length ? (float) $this->length : null,
                'width' => $this->width ? (float) $this->width : null,
                'height' => $this->height ? (float) $this->height : null,
            ],
            'views' => $this->views,
            'published_at' => $this->published_at?->toISOString(),
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'author' => $this->when($this->relationLoaded('user') || $this->user, function () {
                $user = $this->user;
                return $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null;
            }),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'brands' => CategoryResource::collection($this->whenLoaded('brands')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
