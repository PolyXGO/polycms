@extends('layouts.app')

@section('title', $product->name ?? _l('Product'))
@section('description', $product->meta_description ?? $product->short_description ?? '')

@section('breadcrumb')
    @php
        $productArchiveUrl = theme_permalink_url('products', '', 'archive');
        $breadcrumbs = [
            ['label' => _l('Home'), 'url' => url('/')],
            ['label' => _l('Products'), 'url' => $productArchiveUrl],
            ['label' => $product->name, 'url' => null],
        ];
        $breadcrumbs = \App\Facades\Hook::applyFilters('theme.breadcrumbs.product', $breadcrumbs, $product);
    @endphp
    @include('partials.breadcrumb', ['items' => $breadcrumbs])
@endsection

@section('content')
<div class="container section">
    <div class="grid-sidebar">
        
        <!-- Main Content Column -->
        <div>

            <!-- Product Grid Layout (Image left, Summary right) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; margin-bottom: 4rem;">
                    <!-- Product Image Gallery -->
                    <div>
                        @if($product->media && $product->media->count() > 0)
                            <div style="margin-bottom: 1rem; border-radius: var(--radius); overflow: hidden; background-color: var(--geist-accents-1);">
                                <img id="main-product-image" src="{{ $product->media->first()->url ?? '' }}" alt="{{ $product->name }}" style="width: 100%; aspect-ratio: 4/3; object-fit: cover;">
                            </div>
                            
                            @if($product->media->count() > 1)
                                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                                    @foreach($product->media as $index => $media)
                                        <button onclick="document.getElementById('main-product-image').src='{{ $media->url ?? '' }}'" style="border: 2px solid transparent; border-radius: var(--radius); overflow: hidden; cursor: pointer; padding: 0; background: var(--geist-accents-1);" aria-label="{{ _l('View image') }} {{ $index + 1 }}">
                                            <img src="{{ $media->url ?? '' }}" alt="{{ $product->name }} thumbnail" style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div style="width: 100%; aspect-ratio: 4/3; border-radius: var(--radius); background-color: var(--geist-accents-1); display: flex; align-items: center; justify-content: center; color: var(--geist-accents-4);">
                                <span>{{ _l('No Image') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Product Summary -->
                    <div style="display: flex; flex-direction: column; justify-content: center;">
                        <h1 class="post-title" style="font-size: 2.5rem; text-align: left; margin-bottom: 1rem;">
                            {{ $product->name }}
                        </h1>

                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="product-price" style="font-size: 2rem;">{{ format_currency($product->sale_price) }}</span>
                                <span class="product-price-strike" style="font-size: 1.25rem;">{{ format_currency($product->price) }}</span>
                                <span class="badge" style="background-color: var(--geist-foreground); color: var(--geist-background);">{{ _l('Sale') }}</span>
                            @else
                                <span class="product-price" style="font-size: 2rem;">{{ format_currency($product->price) }}</span>
                            @endif
                        </div>

                        @if($product->stock_status === 'in_stock')
                            <p style="color: var(--geist-success); font-weight: 500; font-size: 0.875rem; margin-bottom: 2rem;">&#10003; {{ _l('In Stock') }}</p>
                        @elseif($product->stock_status === 'out_of_stock')
                            <p style="color: var(--geist-error); font-weight: 500; font-size: 0.875rem; margin-bottom: 2rem;">&#10007; {{ _l('Out of Stock') }}</p>
                        @else
                            <p style="color: var(--geist-accents-5); font-weight: 500; font-size: 0.875rem; margin-bottom: 2rem;">&circlearrowright; {{ _l('On Backorder') }}</p>
                        @endif

                        @if(!empty($product->short_description))
                            <div style="color: var(--geist-accents-6); font-size: 1.125rem; line-height: 1.6; margin-bottom: 2rem;">
                                <p>{{ $product->short_description }}</p>
                            </div>
                        @endif

                        <div style="margin-top: auto;">
                            <button class="btn btn-primary" style="width: 100%; padding: 1rem;">
                                {{ _l('Add to Cart') }}
                            </button>
                        </div>

                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--geist-accents-2); font-size: 0.875rem; color: var(--geist-accents-5);">
                            @if(!empty($product->sku))
                                <div style="display: flex; margin-bottom: 0.5rem;">
                                    <span style="width: 100px; font-weight: 600;">{{ _l('SKU') }}</span>
                                    <span style="color: var(--geist-foreground);">{{ $product->sku }}</span>
                                </div>
                            @endif
                            @if($product->categories && $product->categories->count() > 0)
                                <div style="display: flex;">
                                    <span style="width: 100px; font-weight: 600;">{{ _l('Category') }}</span>
                                    <div>
                                        @foreach($product->categories as $category)
                                            <a href="{{ route('categories.show', ['slug' => $category->slug]) }}" style="color: var(--geist-foreground); text-decoration: underline;">
                                                {{ $category->name }}
                                            </a>
                                            @if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                </div>
            </div>

            <!-- Product Details -->
            @if(!empty($product->description_html) && trim(strip_tags($product->description_html)) !== '')
                <div style="padding-top: 4rem; border-top: 1px solid var(--geist-accents-2);">
                    <h2 style="font-size: 1.75rem; margin-bottom: 2rem;">{{ _l('Description') }}</h2>
                    <div class="prose">
                        {!! $product->description_html !!}
                    </div>
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
