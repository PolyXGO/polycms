<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;

class BlogSearchWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $title = $instance->title ?: 'Search';
        $placeholder = $config['placeholder'] ?? 'Search blog...';

        $html = '<div class="widget widget-blog-search">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        $html .= '<form action="' . e(url('/posts')) . '" method="get" class="widget-search-form">';
        $html .= '<label class="sr-only" for="widget-search-input">Search</label>';
        $html .= '<input id="widget-search-input" type="search" name="search" placeholder="' . e($placeholder) . '" />';
        $html .= '<button type="submit">' . e('Search') . '</button>';
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }
}

