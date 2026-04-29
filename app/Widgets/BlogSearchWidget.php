<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\WidgetInstance;

class BlogSearchWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $title = $instance->title ?: _l('Search');
        $placeholder = $config['placeholder'] ?? _l('Search blog...');

        // Use route if available, otherwise fallback to /posts
        try {
            $postsArchiveUrl = route('posts.index');
        } catch (\Exception $e) {
            $postsArchiveUrl = url('/posts');
        }
        $html = '<div class="widget widget-blog-search">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        $html .= '<form action="' . e($postsArchiveUrl) . '" method="get" class="widget-search-form">';
        $html .= '<label class="sr-only" for="widget-search-input">Search</label>';
        $html .= '<input id="widget-search-input" type="search" name="search" placeholder="' . e($placeholder) . '" />';
        $html .= '<button type="submit">' . e(_l('Search')) . '</button>';
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }
}

