<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Post;
use App\Models\WidgetInstance;

class BlogPostsWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];

        $limit = max(1, (int) ($config['limit'] ?? 5));
        $orderBy = $this->sanitizeOrderBy($config['order_by'] ?? 'published_at');
        $orderDirection = $this->sanitizeDirection($config['order_direction'] ?? 'desc');
        $categoryIds = $this->sanitizeIds($config['category_ids'] ?? []);

        $query = Post::published()
            ->ofType('post')
            ->orderBy($orderBy, $orderDirection);

        if (!empty($categoryIds)) {
            $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
        }

        $posts = $query->limit($limit)->get();

        if ($posts->isEmpty()) {
            return '';
        }

        $title = $instance->title ?: 'Blog Posts';

        $html = '<div class="widget widget-blog-posts">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        $html .= '<ul class="widget-list">';

        foreach ($posts as $post) {
            $url = url('/posts/' . urlencode($post->slug));
            $html .= '<li>';
            $html .= '<a href="' . e($url) . '">' . e($post->title) . '</a>';

            if ($post->published_at) {
                $html .= '<span class="widget-meta">' . e($post->published_at->format('M d, Y')) . '</span>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    protected function sanitizeOrderBy(string $orderBy): string
    {
        return in_array($orderBy, ['published_at', 'title'], true) ? $orderBy : 'published_at';
    }

    protected function sanitizeDirection(string $direction): string
    {
        return strtolower($direction) === 'asc' ? 'asc' : 'desc';
    }

    /**
     * @param mixed $value
     * @return array<int, int>
     */
    protected function sanitizeIds(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(
                array_filter(
                    array_map('intval', $value),
                    fn($id) => $id > 0
                )
            );
        }

        if (is_string($value) && trim($value) !== '') {
            $parts = preg_split('/[\s,]+/', $value);
            if ($parts !== false) {
                return array_values(
                    array_filter(
                        array_map('intval', $parts),
                        fn($id) => $id > 0
                    )
                );
            }
        }

        return [];
    }
}

