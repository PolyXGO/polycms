{{-- Active Filters & Breadcrumbs Row Component (Strictly for product catalog) --}}
@php
    $isProductListing = isset($products) && (!isset($posts) || (isset($contentType) && $contentType === 'product'));

    $activeFilterTags = [];
    $currentUrl = request()->url();
    $queryParams = request()->except(['page']);

    $buildRemoveUrl = function($removeKeys) use ($currentUrl, $queryParams) {
        $cleanParams = $queryParams;
        foreach ((array) $removeKeys as $k) {
            unset($cleanParams[$k]);
        }
        return $currentUrl . (!empty($cleanParams) ? '?' . http_build_query($cleanParams) : '');
    };

    if ($isProductListing) {
        // On sale filter
        if (request()->boolean('on_sale') || request('filter') === 'on_sale') {
            $activeFilterTags[] = [
                'label' => _l('On sale'),
                'remove_url' => $buildRemoveUrl(['on_sale', 'filter']),
            ];
        }

        // Featured filter
        if (request()->boolean('featured') || request('filter') === 'featured' || request('sort') === 'featured' || request('sort') === 'features') {
            $activeFilterTags[] = [
                'label' => _l('Featured'),
                'remove_url' => $buildRemoveUrl(['featured', 'filter']),
            ];
        }

        // Sort tabs filter tags
        $sort = request('sort', request('sort_by'));
        if ($sort && !in_array($sort, ['newest', 'latest', 'created_at'])) {
            $sortLabels = [
                'best_sellers' => _l('Best sellers'),
                'bestsellers' => _l('Best sellers'),
                'best-sellers' => _l('Best sellers'),
                'best_seller' => _l('Best sellers'),
                'best_rated' => _l('Best rated'),
                'best-rated' => _l('Best rated'),
                'rating' => _l('Best rated'),
                'trending' => _l('Trending'),
                'popular' => _l('Trending'),
                'views' => _l('Trending'),
                'price' => _l('Price') . ' (' . (strtolower(request('order', request('sort_order', 'asc'))) === 'desc' ? _l('High to Low') : _l('Low to High')) . ')',
                'price_asc' => _l('Price: Low to High'),
                'price_desc' => _l('Price: High to Low'),
            ];
            if (isset($sortLabels[$sort])) {
                $activeFilterTags[] = [
                    'label' => $sortLabels[$sort],
                    'remove_url' => $buildRemoveUrl(['sort', 'sort_by', 'order', 'sort_order']),
                ];
            }
        }

        // Search query
        if (request()->filled('search')) {
            $activeFilterTags[] = [
                'label' => _l('Search') . ': "' . e(request('search')) . '"',
                'remove_url' => $buildRemoveUrl(['search']),
            ];
        }

        // Price range
        if (request()->filled('min_price') || request()->filled('max_price')) {
            $priceLabel = '$' . (request('min_price') ?: '0') . ' - $' . (request('max_price') ?: '∞');
            $activeFilterTags[] = [
                'label' => _l('Price') . ': ' . $priceLabel,
                'remove_url' => $buildRemoveUrl(['min_price', 'max_price']),
            ];
        }
    }
@endphp

@if($isProductListing && !empty($activeFilterTags))
    <div class="fw-active-filters-row" style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 12px; margin-bottom: 16px;">
        <span style="font-size: 0.8125rem; font-weight: 600; color: var(--geist-accents-6, #475569);">
            {{ isset($products) && method_exists($products, 'total') ? $products->total() : count($products ?? []) }} {{ _l('items in') }} 
            @if(isset($category))
                <a href="{{ $category->frontend_url }}" style="color: inherit; text-decoration: underline;">{{ $category->name }}</a>
            @elseif(isset($brand))
                <a href="{{ url('/brands/' . $brand->slug) }}" style="color: inherit; text-decoration: underline;">{{ $brand->name }}</a>
            @else
                <span>{{ _l('All Categories') }}</span>
            @endif
        </span>
        <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 6px;">
            @foreach($activeFilterTags as $tag)
                <span class="fw-filter-tag" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; font-size: 0.8125rem; font-weight: 500; border: 1px solid var(--color-border, #cbd5e1); border-radius: 6px; background: var(--geist-background, #fff); color: var(--geist-foreground, #1e293b);">
                    <span>{{ $tag['label'] }}</span>
                    <a href="{{ $tag['remove_url'] }}" style="color: var(--geist-accents-4, #94a3b8); text-decoration: none; font-size: 0.875rem; font-weight: 700; line-height: 1; margin-left: 2px;" title="{{ _l('Remove filter') }}">✕</a>
                </span>
            @endforeach
            <a href="{{ request()->url() }}" class="fw-clear-all-link" style="font-size: 0.8125rem; font-weight: 500; color: var(--geist-accents-5, #64748b); text-decoration: underline; margin-left: 4px;">
                {{ _l('Clear all') }}
            </a>
        </div>
    </div>
@endif
