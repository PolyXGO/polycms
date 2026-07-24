<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;

class HeadingWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $tag = $config['tag'] ?? 'h3';
        $color = $config['color'] ?? '';
        $fontWeight = $config['font_weight'] ?? 'font-bold';
        $alignment = $config['alignment'] ?? 'left';
        $customClass = $config['custom_class'] ?? '';

        $title = $config['text'] ?? $instance->title ?? '';

        if (empty($title)) {
            return '';
        }

        $classes = array_filter([
            'widget-heading',
            $fontWeight,
            $customClass
        ]);
        
        $alignStyle = '';
        if ($alignment === 'center') {
            $alignStyle = 'text-align: center;';
        } elseif ($alignment === 'right') {
            $alignStyle = 'text-align: right;';
        }
        
        $colorStyle = '';
        if ($color) {
            $colorStyle = "color: {$color};";
        }

        $styleAttr = '';
        if ($alignStyle || $colorStyle) {
            $styleAttr = ' style="' . $alignStyle . $colorStyle . '"';
        }

        $classStr = implode(' ', $classes);

        return "<{$tag} class=\"{$classStr}\"{$styleAttr}>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</{$tag}>";
    }
}
