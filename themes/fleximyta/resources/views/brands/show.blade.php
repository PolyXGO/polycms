@extends('layouts.app')

@section('title', $brand->name)

@section('content')
<section class="page-header-section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 50px;">
            <div style="display: inline-block; padding: 8px 20px; background: rgba(99, 102, 241, 0.1); border-radius: 50px; margin-bottom: 20px;">
                <i class="fas fa-copyright" style="color: #6366f1; margin-right: 8px;"></i>
                <span style="color: #6366f1; font-weight: 600; font-size: 14px;">BRAND</span>
            </div>
            <h1 class="page-header-title">{{ $brand->name }}</h1>
            @if($brand->description)
                <div class="page-header-excerpt">
                    {!! $brand->description !!}
                </div>
            @endif
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container">
        {{-- Products Grid --}}
        <div class="grid">
            @forelse($products ?? [] as $product)
                <div class="product-card">
                    @if(!empty($product->featured_image))
                        <a href="{{ route('products.show', ['slug' => $product->slug]) }}" style="display: block; aspect-ratio: 1; overflow: hidden;">
                            <img src="{{ $product->featured_image }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                        </a>
                    @endif

                    <div style="padding: 20px;">
                        @if($product->categories && $product->categories->count() > 0)
                            <a href="{{ route('product-categories.show', ['slug' => $product->categories->first()->slug]) }}" style="display: inline-block; font-size: 12px; color: #6366f1; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600;">
                                {{ $product->categories->first()->name }}
                            </a>
                        @endif

                        <h3 style="margin: 0 0 12px; font-size: 18px; font-weight: 600;">
                            <a href="{{ route('products.show', ['slug' => $product->slug]) }}" style="color: var(--dark); text-decoration: none;">
                                {{ $product->name }}
                            </a>
                        </h3>

                        @if($product->price)
                            <div style="font-size: 20px; font-weight: 700; color: #6366f1; margin-bottom: 12px;">
                                ${{ number_format($product->price, 2) }}
                            </div>
                        @endif

                        <a href="{{ route('products.show', ['slug' => $product->slug]) }}" class="primary-btn" style="padding: 10px 20px; font-size: 14px;">
                            <span>View Details</span>
                            <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state-container">
                    <div class="empty-state-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <p class="empty-state-text">No products found from this brand.</p>
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
