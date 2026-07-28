<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;
use App\Services\SettingsService;

class SocialLinksWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $layout = $config['layout'] ?? 'list_with_labels';
        $title = $instance->title ?: '';

        $settings = app(SettingsService::class);

        $settings = app(SettingsService::class);
        $socialLinks = $settings->get('social_links');

        if (is_string($socialLinks)) {
            $socialLinks = json_decode($socialLinks, true);
        }

        if (empty($socialLinks) || !is_array($socialLinks)) {
            // Check legacy individual keys first to maintain compatibility if socials settings was not saved yet
            $defaults = [
                'social_facebook' => ['name' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/polyxgoltd'],
                'social_youtube' => ['name' => 'YouTube', 'icon' => 'youtube', 'url' => 'https://headrandom.com/GlU63s5D'],
                'social_github' => ['name' => 'GitHub', 'icon' => 'github', 'url' => 'https://github.com/polyxgo'],
                'social_envato' => ['name' => 'Envato', 'icon' => 'envato', 'url' => 'https://codecanyon.net/user/polyxgo/portfolio'],
                'social_twitter' => ['name' => 'Twitter/X', 'icon' => 'twitter', 'url' => ''],
                'social_instagram' => ['name' => 'Instagram', 'icon' => 'instagram', 'url' => ''],
                'social_linkedin' => ['name' => 'LinkedIn', 'icon' => 'linkedin', 'url' => ''],
            ];
            
            $socialLinks = [];
            foreach ($defaults as $key => $info) {
                $val = $settings->get($key);
                $url = !empty($val) ? $val : $info['url'];
                if (!empty($url)) {
                    $socialLinks[] = [
                        'name' => $info['name'],
                        'icon' => $info['icon'],
                        'url' => $url
                    ];
                }
            }
        }

        if (empty($socialLinks)) {
            return '';
        }

        $class = 'widget widget-social-links';
        if ($layout === 'horizontal_icons') {
            $class = 'widget-social-links';
        }
        $html = '<div class="' . $class . '">';

        if ($title && $layout === 'list_with_labels') {
            $html .= '<h3 class="widget-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
        }

        $html .= '<div class="widget-content">';

        if ($layout === 'horizontal_icons') {
            $html .= '<div class="footer-social-icons" style="display: flex; gap: 0.5rem; align-items: center;">';
            foreach ($socialLinks as $item) {
                $url = $item['url'] ?? '';
                if (empty($url)) continue;
                $name = $item['name'] ?? 'Social';
                $iconVal = $item['icon'] ?? 'ki-share';
                
                // Get the Icon HTML (SVG or font icon)
                $iconHtml = '';
                if (str_starts_with(trim($iconVal), '<svg')) {
                    $iconHtml = $iconVal;
                } else {
                    $brandSvg = get_brand_svg($iconVal);
                    if (!empty($brandSvg)) {
                        $iconHtml = $brandSvg;
                    } else {
                        $iconClass = $iconVal;
                        if (str_starts_with($iconClass, 'ki-') && !str_contains($iconClass, 'ki-outline')) {
                            $iconClass = 'ki-outline ' . $iconClass;
                        }
                        $iconHtml = '<i class="' . e($iconClass) . '" style="font-size: 1.125rem;"></i>';
                    }
                }

                if (str_starts_with(trim($iconHtml), '<svg')) {
                    $iconHtml = preg_replace('/<svg([^>]*)>/i', '<svg$1 style="width: 100%; height: 100%; display: block; overflow: hidden;">', $iconHtml);
                    $iconHtml = '<span class="social-icon-svg-wrapper" style="width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; overflow: hidden; fill: currentColor;">' . $iconHtml . '</span>';
                }

                $html .= '<a href="' . e($url) . '" target="_blank" title="' . e($name) . '" style="width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--geist-accents-2); display: flex; align-items: center; justify-content: center; color: var(--geist-accents-5); text-decoration: none; transition: all 0.2s; overflow: hidden;" onmouseover="this.style.borderColor=\'var(--geist-foreground)\'; this.style.color=\'var(--geist-foreground)\'; this.style.background=\'var(--geist-accents-1)\';" onmouseout="this.style.borderColor=\'var(--geist-accents-2)\'; this.style.color=\'var(--geist-accents-5)\'; this.style.background=\'transparent\';">';
                $html .= $iconHtml;
                $html .= '</a>';
            }
            $html .= '</div>';
        } else {
            $html .= '<ul style="list-style: none; padding: 0; margin: 0; line-height: 2.2;">';
            foreach ($socialLinks as $item) {
                $url = $item['url'] ?? '';
                if (empty($url)) continue;
                $name = $item['name'] ?? 'Social';
                $html .= '<li><a href="' . e($url) . '" target="_blank" style="color: var(--geist-accents-5); text-decoration: none; font-size: 0.9rem; transition: color 0.2s;" onmouseover="this.style.color=\'var(--geist-foreground)\'" onmouseout="this.style.color=\'var(--geist-accents-5)\'">' . e($name) . '</a></li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function getSocialName(string $key): string
    {
        return match ($key) {
            'social_facebook' => 'Facebook',
            'social_youtube' => 'YouTube',
            'social_github' => 'GitHub',
            'social_envato' => 'Envato',
            'social_twitter' => 'Twitter/X',
            'social_instagram' => 'Instagram',
            'social_linkedin' => 'LinkedIn',
            default => 'Social',
        };
    }

    protected function getSocialLabel(string $key): string
    {
        return match ($key) {
            'social_facebook' => 'Facebook Page',
            'social_youtube' => 'YouTube Channel',
            'social_github' => 'GitHub Profile',
            'social_envato' => 'Envato Portfolio',
            'social_twitter' => 'Twitter / X',
            'social_instagram' => 'Instagram Profile',
            'social_linkedin' => 'LinkedIn Profile',
            default => 'Social Profile',
        };
    }

    protected function getIconSvg(string $key): string
    {
        return match ($key) {
            'social_facebook' => '<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"/></svg>',
            'social_youtube' => '<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.507a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.87.507 9.388.507 9.388.507s7.518 0 9.388-.507a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
            'social_github' => '<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.577.688.48C19.137 20.162 22 16.418 22 12c0-5.523-4.477-10-10-10z"/></svg>',
            'social_envato' => '<svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24"><path d="M18.8 8.4C17.6 6.8 15.6 6 13.6 6c-2.8 0-5.2 1.6-6.4 4-.4-.4-.8-1.2-.8-1.6 0-.8.4-1.6 1.2-2 .8-.4 1.6-.4 2-.4 1.6 0 3.2-.8 3.2-2.4 0-1.2-1.2-2.4-2.8-2.4C8.4 1.2 5.2 3.6 4.4 6.8c-.8 2.8.4 5.6 2.4 7.2.8.8.8 1.6.4 2.4l-.8.8c-.8.8-1.6.8-2.4.4-2-1.2-3.2-3.6-3.2-6.4C.8 7.2 3.2 3.6 6.8 2.4c.8-.4 1.6-.8 2.4-.8 2 0 3.6 1.2 4.4 2.8.4-.4.8-1.2.8-1.6 0-1.2-1.2-2-2.4-2-1.2 0-2.4.8-2.4 2 0 .8.4 1.2 1.2 1.2.4 0 .8-.4.8-.8 0-.4-.4-.4-.4-.4 0-.4.4-.4.4-.4.4 0 .8.4.8.8 0 1.2-1.2 2-2.4 2C8.4 5.2 6.8 4 6.4 2.8 5.6 4.8 6.4 6.8 8.4 8c.8.4 1.2 1.2 1.2 2 0 1.2-1.2 2.4-2.8 2.4-2.4 0-4.4-1.6-4.8-4C1.6 10.4.8 12.8.8 15.2c0 4.4 3.6 8 8 8s8-3.6 8-8c0-2.8-1.2-5.6-3.2-7.2-.8-.4-.8-1.2-.4-2l.8-.8c.8-.8 1.6-.8 2.4-.4 2.4 1.6 3.6 4.4 3.6 7.6 0 2.4-1.2 4.8-2.8 6.4 2.4-1.6 4-4.4 4-7.6 0-2.8-1.2-5.6-3.2-7.2z"/></svg>',
            'social_twitter' => '<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'social_instagram' => '<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>',
            'social_linkedin' => '<svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>',
            default => '',
        };
    }
}
