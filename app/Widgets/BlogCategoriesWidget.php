<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Category;
use App\Models\WidgetInstance;

class BlogCategoriesWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $displayCount = (bool) ($config['display_count'] ?? false);
        $hierarchical = (bool) ($config['hierarchical'] ?? true);
        $hideEmpty = (bool) ($config['hide_empty'] ?? true);

        $categories = Category::ofType('post')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        if ($hideEmpty) {
            $categories = $categories->filter(fn(Category $category) => ($category->posts_count ?? 0) > 0);
        }

        if ($categories->isEmpty()) {
            return '';
        }

        $title = $instance->title ?: 'Categories';
        $html = '<div class="widget widget-blog-categories">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';

        if ($hierarchical) {
            $grouped = $categories->groupBy(fn(Category $category) => $category->parent_id ?: 0);
            $html .= $this->renderCategoryList($grouped, 0, $displayCount);
        } else {
            $html .= '<ul class="widget-list">';
            foreach ($categories as $category) {
                $html .= $this->renderCategoryItem($category, $displayCount);
            }
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<int|string, \Illuminate\Support\Collection<int, Category>> $grouped
     */
    protected function renderCategoryList(array $grouped, int $parentId, bool $displayCount): string
    {
        if (!isset($grouped[$parentId])) {
            return '';
        }

        $html = '<ul class="widget-list">';

        foreach ($grouped[$parentId] as $category) {
            $html .= '<li>';
            $html .= $this->renderCategoryLink($category, $displayCount);

            $childList = $this->renderCategoryList($grouped, (int) $category->id, $displayCount);
            if ($childList !== '') {
                $html .= $childList;
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    protected function renderCategoryItem(Category $category, bool $displayCount): string
    {
        return '<li>' . $this->renderCategoryLink($category, $displayCount) . '</li>';
    }

    protected function renderCategoryLink(Category $category, bool $displayCount): string
    {
        $url = url('/categories/' . urlencode($category->slug) . '?type=post');
        $label = e($category->name);

        if ($displayCount) {
            $count = (int) ($category->posts_count ?? 0);
            $label .= ' <span class="count">(' . $count . ')</span>';
        }

        return '<a href="' . e($url) . '">' . $label . '</a>';
    }
}

