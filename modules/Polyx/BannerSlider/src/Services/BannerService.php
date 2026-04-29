<?php

declare(strict_types=1);

namespace Modules\Polyx\BannerSlider\Services;

use Modules\Polyx\BannerSlider\Models\BannerSlider;

class BannerService
{
    /**
     * Get all active banners (respecting dates and active status)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveBanners()
    {
        return BannerSlider::with('image')
            ->active()
            ->scheduled()
            ->ordered()
            ->get()
            ->filter(fn ($banner) => $banner->isActive());
    }

    /**
     * Get banners for topbar display
     *
     * @return array
     */
    public function getBannersForTopbar(): array
    {
        $banners = $this->getActiveBanners();

        return $banners->map(function ($banner) {
            return [
                'id' => $banner->id,
                'title' => $banner->title,
                'type' => $banner->type ?? 'image',
                'image_url' => $banner->image?->url ?? null,
                'link' => $banner->link,
                'link_target' => $banner->link_target ?? '_self',
                'link_rel' => $banner->link_rel,
                'description' => $banner->description,
                'content' => $banner->content,
                'button_text' => $banner->button_text,
                'button_link' => $banner->button_link,
                'button_target' => $banner->button_target ?? '_self',
                'button_rel' => $banner->button_rel,
                'background_color' => $banner->background_color,
                'button_bg_color' => $banner->button_bg_color,
                'button_text_color' => $banner->button_text_color,
                'text_color' => $banner->text_color,
                'gradient_color' => $banner->gradient_color,
                'gradient_degree' => $banner->gradient_degree ?? 135,
                'button_gradient_color' => $banner->button_gradient_color,
                'button_gradient_degree' => $banner->button_gradient_degree ?? 135,
                'button_hover_bg_color' => $banner->button_hover_bg_color,
                'button_hover_text_color' => $banner->button_hover_text_color,
                'button_hover_gradient_color' => $banner->button_hover_gradient_color,
                'countdown_enabled' => $banner->countdown_enabled ?? false,
                'countdown_date' => $banner->countdown_date?->toISOString(),
                'order' => $banner->order,
            ];
        })->toArray();
    }

    /**
     * Get banner slider settings
     *
     * @return array
     */
    public function getSettings(): array
    {
        return \Modules\Polyx\BannerSlider\Models\BannerSliderSetting::allAsArray();
    }

    /**
     * Render banners HTML (for hook filter)
     *
     * @return string
     */
    public function renderBanners(): string
    {
        $banners = $this->getBannersForTopbar();

        if (empty($banners)) {
            return '';
        }

        $html = '<div class="banner-slider">';
        foreach ($banners as $banner) {
            $html .= '<a href="' . htmlspecialchars($banner['link'] ?? '#') . '" target="' . htmlspecialchars($banner['link_target']) . '">';
            $html .= '<img src="' . htmlspecialchars($banner['image_url']) . '" alt="' . htmlspecialchars($banner['title'] ?? '') . '">';
            $html .= '</a>';
        }
        $html .= '</div>';

        return $html;
    }
}
