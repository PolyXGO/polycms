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

        $query = Product::query()
            ->where('locale', $instance->locale ?: app()->getLocale());

        if (!is_admin_user()) {
            $query->published();
            $query->where('slug', 'not like', 'test-%');
        }

        $query->orderBy($orderBy, $orderDirection);

        // Exclude currently viewed product and its translation group variants
        $currentProduct = view()->shared('product') ?? request()->route('product');
        if (!($currentProduct instanceof Product) && request()->route('slug')) {
            $currentProduct = Product::withoutGlobalScope('locale')->where('slug', request()->route('slug'))->first();
        }
        if ($currentProduct instanceof Product) {
            $excludeIds = [$currentProduct->id];
            if (!empty($currentProduct->translation_group_id)) {
                $groupProductIds = Product::withoutGlobalScope('locale')
                    ->where('translation_group_id', $currentProduct->translation_group_id)
                    ->pluck('id')
                    ->toArray();
                $excludeIds = array_unique(array_merge($excludeIds, $groupProductIds));
            }
            $query->whereNotIn('id', $excludeIds);
        }

        if (!empty($categoryIds)) {
            $query->whereHas('categories', fn($q) => $q->whereIn('product_categories.id', $categoryIds));
        }

        $products = $query->limit($limit)->get();

        if ($products->isEmpty()) {
            return '';
        }

        $title = $instance->title ? _l($instance->title) : _l('Latest Products');

        $viewName = 'widgets.products';

        if (view()->exists("theme::{$viewName}")) {
            return view("theme::{$viewName}", [
                'products' => $products,
                'title' => $title,
                'instance' => $instance,
                'config' => $config,
            ])->render();
        }

        if (view()->exists($viewName)) {
            return view($viewName, [
                'products' => $products,
                'title' => $title,
                'instance' => $instance,
                'config' => $config,
            ])->render();
        }

        $html = '<div class="widget widget-products">';
        $html .= '<h3 class="widget-title">' . e($title) . '</h3>';
        $html .= '<ul class="widget-list">';

        foreach ($products as $product) {
            $url = theme_permalink_url('products', $product->slug, 'single');
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
                e(format_currency($product->price)) .
                '</span> <span class="price-new">' .
                e(format_currency($product->sale_price)) .
                '</span></span>';
        }

        if ($product->price !== null) {
            return '<span class="widget-price">' . e(format_currency($product->price)) . '</span>';
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

