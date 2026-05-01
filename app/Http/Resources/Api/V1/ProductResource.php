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
            'type' => $this->type,
            'layout' => $this->layout ?? 'default',
            'settings' => $this->settings && !empty($this->settings) ? $this->settings : new \stdClass(),
            'slug' => $this->slug,
            'frontend_url' => $this->frontend_url,
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
            'allow_refund' => (bool) $this->allow_refund,
            'refund_window_days' => $this->refund_window_days,
            'refund_policy_note' => $this->refund_policy_note,
            'effective_refund_window_days' => $this->effective_refund_window_days,
            'effective_refund_policy_note' => $this->effective_refund_policy_note,
            'order' => $this->order,
            'layout' => $this->layout,
            'template_theme' => $this->template_theme,
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
            'services' => $this->whenLoaded('services'),
            'variants' => $this->when($this->relationLoaded('variants'), function () {
                return $this->variants->map(function ($variant) {
                    $arr = $variant->toArray();
                    if ($variant->relationLoaded('image') && $variant->image) {
                        // Include basic media attributes for the frontend to consume
                        $arr['image'] = [
                            'id' => $variant->image->id,
                            'url' => $variant->image->url,
                            'file_name' => $variant->image->file_name,
                        ];
                    }
                    return $arr;
                });
            }),
            'attributes' => $this->when($this->relationLoaded('variantAttributes'), function () {
                return $this->variantAttributes->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->id,
                        'position' => $attr->pivot->position,
                        'selected_value_ids' => is_string($attr->pivot->selected_value_ids) 
                            ? json_decode($attr->pivot->selected_value_ids, true) 
                            : $attr->pivot->selected_value_ids,
                        'is_specification' => (bool) ($attr->pivot->is_specification ?? true),
                        'globalDef' => [
                            'id' => $attr->id,
                            'name' => $attr->name,
                            'slug' => $attr->slug,
                            'display_type' => $attr->display_type,
                            'values' => $attr->values,
                        ]
                    ];
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
