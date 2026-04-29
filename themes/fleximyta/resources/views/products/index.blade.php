@extends('layouts.app')

@section('title', __('Products'))

@section('content')
<section class="page-header-section">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-header-title">{{ __('Our Products') }}</h1>
            <p class="page-header-excerpt">{{ __('Browse our product catalog') }}</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container">
        <div class="grid" style="gap: 25px;">
            @forelse($products ?? [] as $product)
                <div class="product-card">
                    <a href="{{ route('products.show', ['slug' => $product->slug]) }}">
                        @if($product->media && $product->media->count() > 0)
                            <img src="{{ $product->media->first()->url ?? '' }}" alt="{{ $product->name }}">
                        @else
                            <div style="width: 100%; height: 250px; background: #f0f2ff; display: flex; align-items: center; justify-content: center; color: var(--gray);">
                                <i class="fas fa-image" style="font-size: 48px;"></i>
                            </div>
                        @endif
                    </a>

                    <div class="product-card-content">
                        <h2 class="product-title">
                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}">
                                {{ $product->name }}
                            </a>
                        </h2>
                        
                        @if(!empty($product->short_description))
                            <p class="product-excerpt line-clamp-2">
                                {{ $product->short_description }}
                            </p>
                        @endif
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f2ff;">
                            <div>
                                <div style="font-size: 24px; font-weight: 800; background: var(--gradient); -webkit-background-clip: text; background-clip: text; color: transparent;">
                                    {{ format_currency($product->price) }}
                                </div>
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <div style="font-size: 16px; color: var(--gray); text-decoration: line-through; margin-top: 4px;">
                                        {{ format_currency($product->sale_price) }}
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}" class="primary-btn" style="padding: 10px 20px; font-size: 14px;">
                                {{ __('View Details') }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state-container">
                    <div class="empty-state-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <p class="empty-state-text">{{ __('No products found.') }}</p>
                </div>
            @endforelse
        </div>

        @if(isset($products) && method_exists($products, 'links'))
            <div class="pagination" style="margin-top: 50px;">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
