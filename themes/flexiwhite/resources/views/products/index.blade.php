@extends('layouts.app')

@section('title', _l('Products'))

@section('breadcrumb')
    @include('partials.breadcrumb', ['items' => [
        ['label' => _l('Home'), 'url' => url('/')],
        ['label' => _l('Products'), 'url' => null],
    ]])
@endsection

@section('content')
@php
    $columns = (int) theme_get_option('flexiwhite_products_columns', 3);
    $defaultView = theme_get_option('flexiwhite_products_default_view', 'grid');
@endphp

<div class="container section">
    <div class="grid-sidebar">

        <!-- Main Content Column -->
        <div>
            <div class="listing-header">
                <div>
                    <h1 class="listing-title">{{ _l('Products') }}</h1>
                    <p class="listing-subtitle">{{ _l('Browse our product catalog') }}</p>
                </div>
                @include('partials.listing-toolbar', [
                    'defaultView' => $defaultView,
                    'target' => 'products-listing',
                ])
            </div>

            <div id="products-listing"
                 class="listing-container {{ $defaultView === 'list' ? 'is-list' : 'is-grid' }}"
                 data-columns="{{ $columns }}"
                 style="--listing-columns: {{ $columns }}">

                @forelse($products ?? [] as $product)
                    <article class="listing-card">
                        {{-- Image --}}
                        <a href="{{ route('products.show', ['slug' => $product->slug]) }}" class="listing-card__image">
                            @if($product->media && $product->media->count() > 0)
                                <img src="{{ $product->media->first()->url ?? '' }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <div class="listing-card__no-image">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif
                        </a>

                        {{-- Content --}}
                        <div class="listing-card__body">
                            <h3 class="listing-card__title">
                                <a href="{{ route('products.show', ['slug' => $product->slug]) }}">{{ $product->name }}</a>
                            </h3>

                            <div class="listing-card__price">
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="price-current">{{ format_currency($product->sale_price) }}</span>
                                    <span class="price-original">{{ format_currency($product->price) }}</span>
                                    <span class="price-badge">{{ _l('SALE') }}</span>
                                @else
                                    <span class="price-current">{{ format_currency($product->price) }}</span>
                                @endif
                            </div>

                            @if($product->categories && $product->categories->count() > 0)
                                <div class="listing-card__meta">
                                    <a href="{{ route('categories.show', ['slug' => $product->categories->first()->slug]) }}" class="badge">{{ $product->categories->first()->name }}</a>
                                </div>
                            @endif

                            @if(!empty($product->short_description))
                                <p class="listing-card__excerpt">{{ Str::limit($product->short_description, 120) }}</p>
                            @endif

                            <div class="listing-card__meta">
                                @if($product->stock_status === 'in_stock')
                                    <span class="stock-in">✓ {{ _l('In Stock') }}</span>
                                @elseif($product->stock_status === 'out_of_stock')
                                    <span class="stock-out">✗ {{ _l('Out of Stock') }}</span>
                                @endif
                                @if($product->sku)
                                    <span class="listing-card__sku">SKU: {{ $product->sku }}</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="listing-empty">
                        <p>{{ _l('No products found.') }}</p>
                    </div>
                @endforelse
            </div>

            @if(isset($products) && method_exists($products, 'links'))
                <div style="margin-top: 4rem;">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <aside>
            @if(theme_widget_area_has_content('sidebar_shop'))
                @include('partials.widget-area', [
                    'key' => 'sidebar_shop',
                    'class' => 'sidebar-widget-stack',
                    'title' => _l('Shop Sidebar'),
                ])
            @else
                @include('partials.sidebar-shop-fallback')
            @endif
        </aside>

    </div>
</div>
@endsection
