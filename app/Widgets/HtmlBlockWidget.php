<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;

class HtmlBlockWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $content = $config['content'] ?? '';
        $title = $instance->title ?: '';

        $html = '<div class="widget widget-html-block">';
        
        if ($title) {
            $html .= '<h3 class="widget-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
        }

        $html .= '<div class="widget-content">';
        $html .= $content;
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
