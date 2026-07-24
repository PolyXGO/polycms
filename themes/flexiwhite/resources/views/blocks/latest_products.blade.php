@php
    $heading = $attrs['heading'] ?? __('Featured Products');
    $count = (int) ($attrs['count'] ?? 6);
    $columns = (int) ($attrs['columns'] ?? 3);
    $showViewAll = $attrs['show_view_all'] ?? true;
    $viewAllUrl = $attrs['view_all_url'] ?? '/products';

    $categoryId = $attrs['category_id'] ?? '';
    $offset = (int) ($attrs['offset'] ?? 0);
    $showPrice = $attrs['show_price'] ?? true;
    $showMedia = $attrs['show_media'] ?? true;
    $showTitle = $attrs['show_title'] ?? true;
    $showCategories = $attrs['show_categories'] ?? true;
    $showExcerpt = $attrs['show_excerpt'] ?? true;

    $query = \App\Models\Product::where('status', 'published')
        ->where('slug', 'not like', 'test-%')
        ->where('locale', app()->getLocale())
        ->latest('published_at');

    if (!empty($categoryId)) {
        // Resolve localized category if needed to match localized products
        if (class_exists(\App\Models\Category::class) && in_array(\App\Traits\HasTranslations::class, class_uses_recursive(\App\Models\Category::class))) {
            $category = \App\Models\Category::withoutGlobalScope('locale')->find($categoryId);
            if ($category && isset($category->locale)) {
                $currentLocale = app()->getLocale();
                if ($category->locale !== $currentLocale) {
                    $translatedCategory = $category->getTranslation($currentLocale);
                    if ($translatedCategory) {
                        $categoryId = $translatedCategory->id;
                    }
                }
            }
        }
        $query->whereHas('categories', function($q) use ($categoryId) {
            $q->where('product_categories.id', $categoryId);
        });
    } else {
        // "All categories" - but only categories of the current locale to prevent mixup
        $query->whereHas('categories', function($q) {
            $q->where('product_categories.locale', app()->getLocale());
        });
    }

    if ($offset > 0) {
        $query->skip($offset);
    }

    $products = $query->take($count)->get();

    // Support layout and spacing settings
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding_css'] ?? $attrs['padding'] ?? '';

    $inlineStyles = [];
    if ($margin !== '') {
        $inlineStyles[] = "margin: {$margin}";
    }
    if ($padding !== '') {
        $inlineStyles[] = "padding: {$padding}";
    }
    $styleAttr = !empty($inlineStyles) ? 'style="' . implode('; ', $inlineStyles) . '"' : '';
@endphp

@if($products->count() > 0)
<section class="section" {!! $styleAttr !!}>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="margin: 0;">{{ $heading }}</h2>
            @if($showViewAll)
                <a href="{{ $viewAllUrl }}" class="btn btn-secondary">{{ __('View All') }} &rarr;</a>
            @endif
        </div>

        <div class="listing-container is-grid" style="--listing-columns: {{ $columns }}">
            @foreach($products as $product)
                <article class="listing-card">
                    {{-- Image --}}
                    @if($showMedia)
                    <a href="{{ $product->frontend_url }}" class="listing-card__image">
                        @php 
                            $thumbnail = $product->featured_image_url;
                        @endphp
                        @if($thumbnail)
                            <img src="{{ $thumbnail }}" alt="{{ $product->name }}" {!! media_lazy_attr() !!}>
                        @else
                            <div class="listing-card__no-image">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            </div>
                        @endif
                    </a>
                    @endif

                    {{-- Content --}}
                    @if($showTitle || $showCategories || $showPrice || $showExcerpt)
                    <div class="listing-card__body">
                        @if($showCategories)
                        <div class="listing-card__meta" style="margin-bottom: 8px;">
                            @if($product->categories && $product->categories->count() > 0)
                                @php $displayCategory = $product->categories->sortByDesc('depth')->first(); @endphp
                                <a href="{{ $displayCategory->frontend_url }}" class="badge">{{ $displayCategory->name }}</a>
                            @endif
                        </div>
                        @endif

                        @if($showTitle)
                        <h3 class="listing-card__title">
                            <a href="{{ $product->frontend_url }}">{{ $product->name }}</a>
                        </h3>
                        @endif

                        @if($showExcerpt && !empty($product->short_description))
                            <p class="listing-card__excerpt" style="margin-bottom: 12px;">
                                {{ Str::limit(strip_tags($product->short_description), 120) }}
                            </p>
                        @endif

                        <div class="listing-card__price-row" style="display: flex; justify-content: space-between; align-items: center; gap: 8px; border-top: 1px solid var(--border-color, #e2e8f0); padding-top: 10px; margin-top: 10px; flex-wrap: wrap;">
                            @if($showPrice)
                            <div class="listing-card__price" style="margin: 0;">
                                @php
                                    $hasSale = !empty($product->sale_price) && (float)$product->sale_price !== (float)$product->price;
                                    $currentPrice = $hasSale ? min((float)$product->price, (float)$product->sale_price) : (float)$product->price;
                                    $strikePrice = $hasSale ? max((float)$product->price, (float)$product->sale_price) : null;
                                @endphp
                                @if($hasSale)
                                    <span class="price-current">{{ format_currency($currentPrice) }}</span>
                                    <span class="price-original">{{ format_currency($strikePrice) }}</span>
                                    <span class="price-badge">{{ _l('SALE') }}</span>
                                @else
                                    <span class="price-current">{{ format_currency($currentPrice) }}</span>
                                @endif
                            </div>
                            @endif
                            
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
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
