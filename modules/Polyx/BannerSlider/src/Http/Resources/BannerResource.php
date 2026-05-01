<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
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
            'type' => $this->type ?? 'image',
            'image_id' => $this->image_id,
            'image_url' => $this->image?->url ?? null,
            'image' => $this->image ? [
                'id' => $this->image->id,
                'url' => $this->image->url,
                'name' => $this->image->name,
                'file_name' => $this->image->file_name,
            ] : null,
            'link' => $this->link,
            'link_target' => $this->link_target ?? '_self',
            'link_rel' => $this->link_rel,
            'order' => $this->order,
            'active' => $this->active,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'description' => $this->description,
            'content' => $this->content,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'button_target' => $this->button_target ?? '_self',
            'button_rel' => $this->button_rel,
            'background_color' => $this->background_color,
            'button_bg_color' => $this->button_bg_color,
            'button_text_color' => $this->button_text_color,
            'text_color' => $this->text_color,
            'gradient_color' => $this->gradient_color,
            'gradient_degree' => $this->gradient_degree ?? 135,
            'button_gradient_color' => $this->button_gradient_color,
            'button_gradient_degree' => $this->button_gradient_degree ?? 135,
            'button_hover_bg_color' => $this->button_hover_bg_color,
            'button_hover_text_color' => $this->button_hover_text_color,
            'button_hover_gradient_color' => $this->button_hover_gradient_color,
            'countdown_enabled' => $this->countdown_enabled ?? false,
            'countdown_date' => $this->countdown_date?->toISOString(),
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
