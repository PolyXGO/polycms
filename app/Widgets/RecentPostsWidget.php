<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Post;
use App\Models\WidgetInstance;

class RecentPostsWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $limit = $config['limit'] ?? 5;
        $title = $instance->title ?: 'Recent Posts';

        $posts = Post::published()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        $html = '<div class="widget widget-recent-posts">';
        $html .= '<h3 class="widget-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '<ul class="widget-list">';

        foreach ($posts as $post) {
            $html .= '<li>';
            $html .= '<a href="/posts/' . htmlspecialchars($post->slug, ENT_QUOTES, 'UTF-8') . '">';
            $html .= htmlspecialchars($post->title, ENT_QUOTES, 'UTF-8');
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }
}
