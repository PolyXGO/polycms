<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Product;
use App\Models\WidgetInstance;

class ProductsWidget
{
    public function render(WidgetInstance $instance): string
    {
        $config = $instance->config ?? [];

        $limit = max(1, (int) ($config['limit'] ?? 4));
        $orderBy = $this->sanitizeOrderBy($config['order_by'] ?? 'created_at');
        $orderDirection = $this->sanitizeDirection($config['order_direction'] ?? 'desc');
        $categoryIds = $this->sanitizeIds($config['category_ids'] ?? []);

        $query = Product::published()->orderBy($orderBy, $orderDirection);

        if (!empty($categoryIds)) {
            $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
        }

        $products = $query->limit($limit)->get();

        if ($products->isEmpty()) {
            return '';
        }

        $title = $instance->title ?: 'Products';

        $html = '<div class="widget widget-products">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        $html .= '<ul class="widget-list">';

        foreach ($products as $product) {
            $url = url('/products/' . urlencode($product->slug));
            $html .= '<li>';
            $html .= '<a href="' . e($url) . '">' . e($product->name) . '</a>';
            $html .= $this->renderPrice($product);
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    protected function renderPrice(Product $product): string
    {
        if ($product->sale_price !== null && $product->sale_price < $product->price) {
            return '<span class="widget-price"><span class="price-old">' .
                e(number_format((float) $product->price, 2)) .
                '</span> <span class="price-new">' .
                e(number_format((float) $product->sale_price, 2)) .
                '</span></span>';
        }

        if ($product->price !== null) {
            return '<span class="widget-price">' . e(number_format((float) $product->price, 2)) . '</span>';
        }

        return '';
    }

    protected function sanitizeOrderBy(string $orderBy): string
    {
        $allowed = ['created_at', 'price', 'name'];
        return in_array($orderBy, $allowed, true) ? $orderBy : 'created_at';
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

