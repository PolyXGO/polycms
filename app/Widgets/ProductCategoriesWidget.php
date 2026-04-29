<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Category;
use App\Models\WidgetInstance;

class ProductCategoriesWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];
        $displayCount = (bool) ($config['display_count'] ?? false);
        $hideEmpty = (bool) ($config['hide_empty'] ?? true);

        $categories = Category::ofType('product')
            ->withCount('products')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        if ($hideEmpty) {
            $categories = $categories->filter(fn(Category $category) => ($category->products_count ?? 0) > 0);
        }

        if ($categories->isEmpty()) {
            return '';
        }

        $grouped = $categories->groupBy(fn(Category $category) => $category->parent_id ?: 0);
        $title = $instance->title ?: 'Product Categories';

        $html = '<div class="widget widget-product-categories">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        $html .= $this->renderCategoryList($grouped, 0, $displayCount);
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<int|string, \Illuminate\Support\Collection<int, Category>> $grouped
     */
    protected function renderCategoryList($grouped, int $parentId, bool $displayCount): string
    {
        if (!isset($grouped[$parentId])) {
            return '';
        }

        $html = '<ul class="widget-list">';

        foreach ($grouped[$parentId] as $category) {
            $html .= '<li>';
            $html .= $this->renderCategoryLink($category, $displayCount);

            $children = $this->renderCategoryList($grouped, (int) $category->id, $displayCount);
            if ($children !== '') {
                $html .= $children;
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    protected function renderCategoryLink(Category $category, bool $displayCount): string
    {
        $url = theme_permalink_url('categories', $category->slug, 'single');
        $label = e($category->name);

        if ($displayCount) {
            $count = (int) ($category->products_count ?? 0);
            $label .= ' <span class="count">(' . $count . ')</span>';
        }

        return '<a href="' . e($url) . '">' . $label . '</a>';
    }
}

