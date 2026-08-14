{{-- Featured Products Touch Slider Partial for FlexiWhite Theme (delegates to Core Products Slider) --}}
@php
    $showFeaturedSlider = theme_get_option('flexiwhite_featured_products_slider', 'show') === 'show';
    $isSearchOrFilter = request()->filled('search') || request()->filled('min_price') || request()->filled('max_price');
    $minRequiredItems = (int) theme_get_option('flexiwhite_featured_slider_min_items', 4);
    $maxSliderItems = (int) theme_get_option('flexiwhite_featured_slider_max_items', 8);

    // Rule: If total products in this category/catalog <= 12, hide the slider to avoid redundancy & confusion
    $totalContextProducts = isset($products) && method_exists($products, 'total') 
        ? $products->total() 
        : (isset($products) ? count($products) : 0);

    if ($totalContextProducts <= 12) {
        $showFeaturedSlider = false;
    }
@endphp

@if($showFeaturedSlider && !$isSearchOrFilter)
    @include('core.blocks.products_slider', [
        'attrs' => [
            'heading' => _l('Featured Products'),
            'filter_by' => 'featured',
            'layout' => 'slider',
            'count' => $maxSliderItems,
            'min_required_items' => $minRequiredItems,
            'category_id' => (isset($category) && $category instanceof \App\Models\ProductCategory) ? $category->id : '',
            'show_view_all' => false,
            'slider_autoplay' => true,
            'slider_mode' => 'continuous',
            'slider_speed' => 4,
            'pause_on_hover' => true,
        ]
    ])
@endif
