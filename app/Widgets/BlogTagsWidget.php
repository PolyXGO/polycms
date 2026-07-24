<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Tag;
use App\Models\WidgetInstance;

class BlogTagsWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];

        $limit = max(1, (int) ($config['limit'] ?? 20));
        $orderBy = $config['order_by'] ?? 'count';
        $orderDirection = strtolower($config['order_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = Tag::ofType('post')->withCount('posts');

        if ($orderBy === 'name') {
            $query->orderBy('name', $orderDirection);
        } else {
            $query->orderBy('posts_count', $orderDirection);
        }

        $tags = $query->limit($limit)->get();

        if ($tags->isEmpty()) {
            return '';
        }

        $title = $instance->title ?: 'Tags';

        $html = '<div class="widget widget-blog-tags">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        $html .= '<div class="tag-cloud">';

        $maxCount = max($tags->pluck('posts_count')->all());
        $minCount = min($tags->pluck('posts_count')->all());
        $range = max(1, $maxCount - $minCount);

        foreach ($tags as $tag) {
            $count = max(1, (int) $tag->posts_count);
            $size = 0.8 + (($count - $minCount) / $range) * 0.7; // between 0.8em and 1.5em
            // Use route if available, otherwise fallback to query string
            $url = theme_permalink_url('tags', $tag->slug, 'post');
            $html .= '<a href="' . e($url) . '" style="font-size:' . number_format($size, 2) . 'em">';
            $html .= e($tag->name);
            $html .= '</a> ';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}

