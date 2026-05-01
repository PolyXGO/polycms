<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Facades\Hook;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'frontend_url' => $this->frontend_url,
            'type' => $this->type,
            'status' => $this->status,
            'layout' => $this->layout ?? 'default',
            'layout_template_id' => $this->layout_template_id,
            'template_theme' => $this->template_theme,
            'excerpt' => $this->excerpt,
            'content_raw' => $this->content_raw,
            'content_html' => Hook::applyFilters('post.content.render', $this->content_html ?? '', $this->resource),
            'published_at' => $this->published_at?->toISOString(),
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'meta' => [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
                'keywords' => $this->meta_keywords,
                'og_image' => $this->og_image,
            ],
            'meta_fields' => $this->whenLoaded('meta', fn() => $this->meta->pluck('meta_value', 'meta_key')->toArray(), []),
            'featured_image' => $this->featured_image,
            'show_featured_image' => (bool) ($this->show_featured_image ?? true),
            'layout_template' => $this->whenLoaded('layoutTemplate', fn (): array => [
                'id' => $this->layoutTemplate?->id,
                'name' => $this->layoutTemplate?->name,
                'slug' => $this->layoutTemplate?->slug,
            ]),
            'views' => $this->views,
            'order' => $this->order,
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
