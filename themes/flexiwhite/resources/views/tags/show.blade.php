@extends('layouts.app')

@section('title', '#' . $tag->name)
@section('description', $tag->description ?? _l('Posts tagged with') . ' ' . $tag->name)

@section('breadcrumb')
    @php
        $bItems = [
            ['label' => _l('Home'), 'url' => url('/')]
        ];
        if (isset($contentType) && $contentType === 'product') {
            $bItems[] = ['label' => _l('Products'), 'url' => route('products.index')];
        } else {
            $bItems[] = ['label' => _l('Blog'), 'url' => route('posts.index')];
        }
        $bItems[] = ['label' => '#' . $tag->name, 'url' => null];
    @endphp
    @include('partials.breadcrumb', ['items' => $bItems])
@endsection

@section('content')
@php
    if (isset($contentType) && $contentType === 'product') {
        $columns = (int) theme_get_option('flexiwhite_products_columns', 3);
        $defaultView = theme_get_option('flexiwhite_products_default_view', 'grid');
    } else {
        $columns = (int) theme_get_option('flexiwhite_posts_columns', 3);
        $defaultView = theme_get_option('flexiwhite_posts_default_view', 'grid');
        $cardStyle = theme_get_option('flexiwhite_posts_card_style', 'image_first');
        $isTitleFirst = $cardStyle === 'title_first';
    }
@endphp

<div class="container section">
    <div class="listing-header">
        <div>
            <span class="badge" style="margin-bottom: 0.5rem;">{{ _l('Tag') }}</span>
            <h1 class="listing-title">#{{ $tag->name }}</h1>
            @if($tag->description)
                <p class="listing-subtitle">{{ $tag->description }}</p>
            @endif
        </div>
        @include('partials.listing-toolbar', [
            'defaultView' => $defaultView,
            'target' => isset($contentType) && $contentType === 'product' ? 'products-listing' : 'tag-listing',
        ])
    </div>

    @if(isset($contentType) && $contentType === 'product')
        <div id="products-listing"
             class="listing-container"
             data-columns="{{ $columns }}"
             style="--listing-columns: {{ $columns }}">
            <script>/*<![CDATA[*/(function(){try{var v=localStorage.getItem('polycms_view_products-listing');if(v==='list'||v==='grid')document.currentScript.parentElement.classList.add('is-'+v);else document.currentScript.parentElement.classList.add('is-grid')}catch(e){document.currentScript.parentElement.classList.add('is-grid')}})();/*]]>*/</script>

            @forelse($products ?? [] as $product)
                <article class="listing-card">
                    {{-- Image --}}
                    <a href="{{ $product->frontend_url }}" class="listing-card__image">
                        @php $imageUrl = $product->featured_image_url; @endphp
                        @if(!empty($imageUrl))
                            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" {!! media_lazy_attr() !!}>
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
                        @if($product->categories && $product->categories->count() > 0)
                            <div class="listing-card__meta" style="margin-bottom: 8px;">
                                @php $displayCategory = $product->categories->sortByDesc('depth')->first(); @endphp
                                <a href="{{ $displayCategory->frontend_url }}" class="badge">{{ $displayCategory->name }}</a>
                            </div>
                        @endif

                        <h3 class="listing-card__title">
                            <a href="{{ $product->frontend_url }}">{{ $product->name }}</a>
                        </h3>

                        @if(!empty($product->short_description))
                            <p class="listing-card__excerpt" style="margin-bottom: 12px;">{{ Str::limit(strip_tags($product->short_description), 120) }}</p>
                        @endif

                        <div class="listing-card__meta" style="margin-bottom: 12px;">
                            @if($product->stock_status === 'in_stock')
                                <span class="stock-in">✓ {{ _l('In Stock') }}</span>
                            @elseif($product->stock_status === 'out_of_stock')
                                <span class="stock-out">✗ {{ _l('Out of Stock') }}</span>
                            @endif
                            @if($product->sku)
                                <span class="listing-card__sku">SKU: {{ $product->sku }}</span>
                            @endif
                        </div>

                        <div class="listing-card__price-row" style="display: flex; justify-content: space-between; align-items: center; gap: 8px; border-top: 1px solid var(--border-color, #e2e8f0); padding-top: 10px; margin-top: 10px; flex-wrap: wrap;">
                            <div class="listing-card__price" style="margin: 0;">
                                @php
                                    $effectivePrice = (float) $product->effective_price;
                                    $regularPrice = (float) $product->price;
                                    $services = $product->relationLoaded('services') ? $product->services : $product->services()->get();
                                    if ($services && $services->isNotEmpty()) {
                                        $validServicePrices = $services->pluck('price')->filter(fn($p) => $p !== null && (float)$p > 0)->map(fn($p) => (float)$p);
                                        if ($validServicePrices->isNotEmpty()) {
                                            $regularPrice = $validServicePrices->min();
                                        }
                                    }
                                    $salePrice = (float) ($product->sale_price ?? 0);
                                    $hasSale = ($salePrice > 0 && $salePrice < $regularPrice) || ($effectivePrice > 0 && $effectivePrice < $regularPrice);
                                    $currentPrice = $effectivePrice > 0 ? $effectivePrice : $regularPrice;
                                    $strikePrice = $hasSale ? $regularPrice : null;
                                @endphp
                                @if($hasSale && $strikePrice > $currentPrice)
                                    <span class="price-current">{{ format_currency($currentPrice) }}</span>
                                    <span class="price-original">{{ format_currency($strikePrice) }}</span>
                                    <span class="price-badge">{{ _l('SALE') }}</span>
                                @else
                                    <span class="price-current">{{ format_currency($currentPrice) }}</span>
                                @endif
                            </div>
                            
                            <div class="listing-card__icons" style="display: flex; align-items: center; gap: 10px; font-size: 0.8rem; color: #64748b;">
                                @if($product->sales_count > 0)
                                    <span class="stats-sales" style="display: inline-flex; align-items: center; gap: 2px;" title="{{ _l('Sold') }}">
                                        <i class="fas fa-shopping-cart" style="font-size: 0.75rem; color: #64748b;"></i>
                                        <span>{{ $product->sales_count }}</span>
                                    </span>
                                @endif
                                @if($product->review_count > 0)
                                    <span class="stats-rating" style="display: inline-flex; align-items: center; gap: 2px; color: #f59e0b;" title="{{ _l('Rating') }}">
                                        <i class="fas fa-star" style="font-size: 0.75rem;"></i>
                                        <span style="font-weight: 600;">{{ number_format($product->avg_rating, 1) }}</span>
                                        <span style="color: #64748b;">({{ $product->review_count }})</span>
                                    </span>
                                @endif
                                <span class="stats-views" style="display: inline-flex; align-items: center; gap: 3px;" title="{{ _l('Views') }}">
                                    <i class="far fa-eye" style="font-size: 0.8rem;"></i>
                                    <span>{{ $product->views ?? 0 }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="listing-empty text-center py-12">
                    <p class="text-muted text-lg">{{ _l('No products found with this tag.') }}</p>
                </div>
            @endforelse
        </div>

        @if(isset($products) && method_exists($products, 'links'))
            <div style="margin-top: 4rem;">
                {{ $products->links() }}
            </div>
        @endif
    @else
        <div id="tag-listing"
             class="listing-container"
             data-columns="{{ $columns }}"
             style="--listing-columns: {{ $columns }}">
            <script>/*<![CDATA[*/(function(){try{var v=localStorage.getItem('polycms_view_tag-listing');if(v==='list'||v==='grid')document.currentScript.parentElement.classList.add('is-'+v);else document.currentScript.parentElement.classList.add('is-grid')}catch(e){document.currentScript.parentElement.classList.add('is-grid')}})();/*]]>*/</script>

            @forelse($posts as $post)
                <article class="listing-card {{ $isTitleFirst ? 'card-title-first' : '' }}">
                    {{-- Image --}}
                    <a href="{{ $post->frontend_url }}" class="listing-card__image">
                        @php $thumbnail = $post->featured_image_url; @endphp
                        @if($thumbnail)
                            <img src="{{ $thumbnail }}" alt="{{ $post->title }}" {!! media_lazy_attr() !!}>
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
                        <div class="listing-card__meta">
                            @if($post->categories && $post->categories->count() > 0)
                                @php $displayCategory = $post->categories->whereNotNull('parent_id')->first() ?? $post->categories->first(); @endphp
                                <a href="{{ $displayCategory->frontend_url ?? '#' }}" class="badge">{{ $displayCategory->name }}</a>
                            @endif
                        </div>

                        <h3 class="listing-card__title">
                            <a href="{{ $post->frontend_url }}">{{ $post->title }}</a>
                        </h3>

                        @if($post->excerpt || $post->content_html)
                            <p class="listing-card__excerpt">
                                {{ Str::limit(strip_tags($post->excerpt ?: $post->content_html), 120) }}
                            </p>
                        @endif

                        <div class="listing-card__author">
                            @if($post->user)
                                <span>{{ _l('By') }} <a href="{{ route('authors.show', $post->user) }}">{{ $post->user->name }}</a></span>
                            @endif
                            
                            @if(theme_get_option('flexiwhite_post_show_date', 'show') === 'show')
                                @if($post->user) <span style="margin: 0 0.5rem; color: var(--geist-accents-3);">&bull;</span> @endif
                                <span>{{ format_post_date($post->published_at ?? $post->created_at) }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="listing-empty text-center py-12">
                    <p class="text-muted text-lg">{{ _l('No posts found with this tag.') }}</p>
                </div>
            @endforelse
        </div>

        @if(isset($posts) && method_exists($posts, 'links'))
            <div style="margin-top: 4rem;">
                {{ $posts->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
