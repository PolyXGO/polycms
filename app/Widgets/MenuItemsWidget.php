<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;

class MenuItemsWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $items = $config['items'] ?? [];
        $customClass = $config['custom_class'] ?? '';
        $layout = $config['layout'] ?? 'list_with_labels';

        if (empty($items)) {
            return '';
        }

        $locale = app()->getLocale();
        
        $classes = array_filter([
            'widget-menu-items',
            $customClass
        ]);
        if ($layout === 'horizontal') {
            $classes[] = 'widget-menu-items-horizontal';
        }
        $classStr = implode(' ', $classes);

        $html = "<div class=\"{$classStr}\">";

        if ($layout === 'horizontal') {
            $html .= '<div class="footer-menu-horizontal" style="display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">';
            foreach ($items as $item) {
                $title = $item['title'] ?? '';

                if (empty($title)) {
                    continue;
                }

                $link = $item['link'] ?? '#';
                $rel = $item['rel'] ?? '';
                $target = $item['target'] ?? '_self';
                $icon = $item['icon'] ?? '';

                $relAttr = $rel ? ' rel="' . e($rel) . '"' : '';
                $targetAttr = $target ? ' target="' . e($target) . '"' : '';

                $html .= '<a href="' . e($link) . '"' . $targetAttr . $relAttr . ' style="color: var(--geist-foreground); font-weight: 600; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.color=\'var(--geist-link)\'" onmouseout="this.style.color=\'var(--geist-foreground)\'">';
                
                if ($icon) {
                    if (str_starts_with(trim($icon), '<svg')) {
                        $html .= $icon;
                    } elseif (\App\Support\IconRegistry::has($icon)) {
                        $svg = \App\Support\IconRegistry::render($icon, '', 14, 14);
                        $svg = str_replace('<svg ', '<svg style="width: 14px; height: 14px; flex-shrink: 0; display: inline-block; vertical-align: middle; fill: none; stroke: currentColor; stroke-width: 2;" ', $svg);
                        $html .= $svg;
                    } else {
                        $html .= '<i class="' . e($icon) . '"></i>';
                    }
                }
                
                $html .= e($title);
                $html .= '</a>';
            }
            $html .= '</div>';
        } else {
            $html .= '<ul style="list-style: none; padding: 0; margin: 0; line-height: 2.2;">';

            foreach ($items as $item) {
                $title = $item['title'] ?? '';

                if (empty($title)) {
                    continue;
                }

                $link = $item['link'] ?? '#';
                
                $rel = $item['rel'] ?? '';
                $target = $item['target'] ?? '_self';
                $icon = $item['icon'] ?? '';

                $relAttr = $rel ? ' rel="' . e($rel) . '"' : '';
                $targetAttr = $target ? ' target="' . e($target) . '"' : '';

                $html .= '<li>';
                $html .= '<a href="' . e($link) . '"' . $targetAttr . $relAttr . ' style="color: var(--geist-accents-5); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; display: inline-flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.color=\'var(--geist-foreground)\'" onmouseout="this.style.color=\'var(--geist-accents-5)\'">';
                
                if ($icon) {
                    if (str_starts_with(trim($icon), '<svg')) {
                        $html .= $icon;
                    } elseif (\App\Support\IconRegistry::has($icon)) {
                        $svg = \App\Support\IconRegistry::render($icon, '', 16, 16);
                        $svg = str_replace('<svg ', '<svg style="width: 16px; height: 16px; flex-shrink: 0; display: inline-block; vertical-align: middle; fill: none; stroke: currentColor; stroke-width: 2;" ', $svg);
                        $html .= $svg;
                    } else {
                        $html .= '<i class="' . e($icon) . '"></i>';
                    }
                }
                
                $html .= e($title);
                $html .= '</a>';
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }
}
