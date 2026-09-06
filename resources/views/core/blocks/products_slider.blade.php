{{-- Core PolyCMS Products Slider & Showcase Landing Block --}}
@php
    $instanceUid = 'pcs_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 10);
    $blockId = 'polycms_products_' . $instanceUid;
    $trackId = 'track_' . $instanceUid;
    
    // Heading & Title handling (Default empty if explicitly omitted or set to empty)
    $heading = array_key_exists('heading', $attrs) ? $attrs['heading'] : ($attrs['title'] ?? '');
    $subtitle = $attrs['subtitle'] ?? '';
    $filterBy = $attrs['filter_by'] ?? $attrs['sort'] ?? $attrs['sort_by'] ?? 'featured';
    $layout = $attrs['layout'] ?? 'slider'; // 'slider' or 'grid'
    $count = (int) ($attrs['count'] ?? $attrs['limit'] ?? ($layout === 'slider' ? 8 : 6));
    $columns = (int) ($attrs['columns'] ?? 3);
    $categoryId = $attrs['category_id'] ?? '';
    $offset = (int) ($attrs['offset'] ?? 0);
    $showViewAll = ($attrs['show_view_all'] ?? 'yes') !== 'no' && ($attrs['show_view_all'] ?? true) !== false && !empty($attrs['show_view_all']);
    $viewAllUrl = $attrs['view_all_url'] ?? '/products';
    $showMedia = ($attrs['show_media'] ?? 'yes') !== 'no' && ($attrs['show_media'] ?? true) !== false;
    $showTitle = ($attrs['show_title'] ?? 'yes') !== 'no' && ($attrs['show_title'] ?? true) !== false;
    $showCategories = ($attrs['show_categories'] ?? 'yes') !== 'no' && ($attrs['show_categories'] ?? true) !== false;
    $showPrice = ($attrs['show_price'] ?? 'yes') !== 'no' && ($attrs['show_price'] ?? true) !== false;
    $showBadge = ($attrs['show_badge'] ?? 'yes') !== 'no' && ($attrs['show_badge'] ?? true) !== false;
    $minRequiredItems = (int) ($attrs['min_required_items'] ?? 4);

    // Auto Slider (Autoplay, Continuous Seamless Rolling, Direction) Settings
    $rawAutoplay = $attrs['slider_autoplay'] ?? $attrs['autoplay'] ?? false;
    $isAutoplay = ($rawAutoplay === true || $rawAutoplay === 1 || in_array($rawAutoplay, ['yes', 'true', '1', 1], true));
    $sliderMode = $attrs['slider_mode'] ?? 'continuous'; // 'continuous' (seamless marquee) or 'stepped'
    $sliderDirection = strtolower((string)($attrs['slider_direction'] ?? $attrs['direction'] ?? 'left')); // 'left' or 'right'
    $rawSpeed = (float) ($attrs['slider_speed'] ?? $attrs['autoplay_speed'] ?? 4);
    // If stepped: seconds -> ms (e.g. 4s -> 4000ms). If continuous: speed in px/second (e.g. 35px/s).
    $steppedSpeed = ($rawSpeed > 0 && $rawSpeed < 100) ? ((int)($rawSpeed * 1000)) : ($rawSpeed > 0 ? (int)$rawSpeed : 4000);
    $continuousSpeed = ($rawSpeed > 0 && $rawSpeed <= 20) ? (float)(140 / max(1, $rawSpeed)) : 35.0; // px/s
    $pauseOnHover = ($attrs['pause_on_hover'] ?? true) !== false && ($attrs['pause_on_hover'] ?? '') !== 'no';

    $selectionMode = $attrs['selection_mode'] ?? 'filter';
    $customProductIds = array_values(array_filter((array)($attrs['custom_product_ids'] ?? $attrs['product_ids'] ?? [])));

    if ($selectionMode === 'custom' && !empty($customProductIds)) {
        $customQuery = \App\Models\Product::with(['categories', 'media'])
            ->whereIn('id', $customProductIds);

        if (!is_admin_user()) {
            $customQuery->where('status', 'published');
        }

        $fetchedProducts = $customQuery->get();
        // Sort strictly according to the order in $customProductIds array (Database-agnostic Collection Sorting)
        $products = $fetchedProducts->sortBy(function($p) use ($customProductIds) {
            $pos = array_search($p->id, $customProductIds);
            return $pos !== false ? $pos : 999999;
        })->values();
    } else {
        // Build Product Query
        $query = \App\Models\Product::with(['categories', 'media']);

        if (!is_admin_user()) {
            $query->where('status', 'published')
                ->where('slug', 'not like', 'test-%');
        }

        // Category filter with localized translation resolution
        if (!empty($categoryId)) {
            if (class_exists(\App\Models\Category::class) && in_array(\App\Traits\HasTranslations::class, class_uses_recursive(\App\Models\Category::class))) {
                $catModel = \App\Models\Category::withoutGlobalScope('locale')->find($categoryId);
                if ($catModel && isset($catModel->locale)) {
                    $currentLocale = app()->getLocale();
                    if ($catModel->locale !== $currentLocale) {
                        $translatedCat = $catModel->getTranslation($currentLocale);
                        if ($translatedCat) {
                            $categoryId = $translatedCat->id;
                        }
                    }
                }
            }
            $query->whereHas('categories', function($q) use ($categoryId) {
                $q->where('product_categories.id', $categoryId);
            });
        }

        // Database driver compatibility for Sales calculation
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            $rawSalesSql = "((SELECT COALESCE(SUM(quantity), 0) FROM order_items JOIN orders ON orders.id = order_items.order_id WHERE order_items.product_id = products.id AND orders.status NOT IN ('cancelled', 'failed')) + CAST(COALESCE(settings->>'external_sales', '0') AS INTEGER) + CAST(COALESCE(settings->>'sales_offset', '0') AS INTEGER)) DESC";
        } elseif ($driver === 'sqlite') {
            $rawSalesSql = "((SELECT COALESCE(SUM(quantity), 0) FROM order_items JOIN orders ON orders.id = order_items.order_id WHERE order_items.product_id = products.id AND orders.status NOT IN ('cancelled', 'failed')) + CAST(COALESCE(json_extract(settings, '$.external_sales'), '0') AS INTEGER) + CAST(COALESCE(json_extract(settings, '$.sales_offset'), '0') AS INTEGER)) DESC";
        } else {
            $rawSalesSql = "((SELECT COALESCE(SUM(quantity), 0) FROM order_items JOIN orders ON orders.id = order_items.order_id WHERE order_items.product_id = products.id AND orders.status NOT IN ('cancelled', 'failed')) + CAST(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(settings, '$.external_sales')), ''), '0') AS SIGNED) + CAST(COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(settings, '$.sales_offset')), ''), '0') AS SIGNED)) DESC";
        }

        // Apply Sorting / Filter Logic
        if (in_array($filterBy, ['featured', 'features'], true)) {
            $featuredQuery = (clone $query)->where('featured', true)->orderBy('created_at', 'desc');
            if ($offset > 0) {
                $featuredQuery->skip($offset);
            }
            $products = $featuredQuery->take($count)->get();

            // If fewer than minRequiredItems, backfill with best sellers!
            if ($products->count() < $minRequiredItems) {
                $needed = $count - $products->count();
                $existingIds = $products->pluck('id')->toArray();

                $backfillQuery = (clone $query)->whereNotIn('id', $existingIds)
                    ->orderByRaw($rawSalesSql)
                    ->orderBy('id', 'desc');

                $bestSellersBackfill = $backfillQuery->take($needed)->get();
                $products = $products->concat($bestSellersBackfill);
            }
        } elseif (in_array($filterBy, ['best_sellers', 'bestsellers', 'best_seller'], true)) {
            $query->orderByRaw($rawSalesSql)->orderBy('id', 'desc');
            if ($offset > 0) {
                $query->skip($offset);
            }
            $products = $query->take($count)->get();
        } elseif (in_array($filterBy, ['best_rated', 'rating'], true)) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'avg_rating')) {
                $query->orderBy('avg_rating', 'desc');
            } else {
                $query->orderBy('views', 'desc')->orderBy('created_at', 'desc');
            }
            if ($offset > 0) {
                $query->skip($offset);
            }
            $products = $query->take($count)->get();
        } elseif (in_array($filterBy, ['trending', 'popular', 'views'], true)) {
            $query->orderBy('views', 'desc')->orderBy('created_at', 'desc');
            if ($offset > 0) {
                $query->skip($offset);
            }
            $products = $query->take($count)->get();
        } elseif (in_array($filterBy, ['price_asc', 'price_low_high'], true)) {
            $query->orderByRaw("COALESCE(NULLIF(sale_price, 0), price) ASC");
            if ($offset > 0) {
                $query->skip($offset);
            }
            $products = $query->take($count)->get();
        } elseif (in_array($filterBy, ['price_desc', 'price_high_low'], true)) {
            $query->orderByRaw("COALESCE(NULLIF(sale_price, 0), price) DESC");
            if ($offset > 0) {
                $query->skip($offset);
            }
            $products = $query->take($count)->get();
        } else { // 'newest' / default
            $query->orderBy('created_at', 'desc');
            if ($offset > 0) {
                $query->skip($offset);
            }
            $products = $query->take($count)->get();
        }
    }

    // Header Icon (Only if explicitly provided via attrs)
    $headerIcon = $attrs['heading_icon'] ?? $attrs['icon'] ?? null;

    $hasHeading = !empty(trim((string)$heading));
    $hasSubtitle = !empty(trim((string)$subtitle));
    $hasViewAll = $showViewAll && !empty($viewAllUrl);
    $hasSliderNav = $layout === 'slider';
    $hasHeader = $hasHeading || $hasSubtitle || $hasViewAll || $hasSliderNav;
@endphp

<style>
/* PolyCMS Core Products Slider & Touch Track Styles (Strict Isolation & Prose Immunity) */
.polycms-products-slider-wrap,
.fw-featured-slider-wrap {
    width: 100%;
    margin-bottom: 2rem;
    box-sizing: border-box;
    position: relative;
}
.polycms-products-slider-track,
.fw-featured-slider-track {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scroll-snap-type: none !important;
    scroll-behavior: auto;
    -webkit-overflow-scrolling: touch;
    padding: 4px 2px 14px 2px;
    scrollbar-width: none;
    -ms-overflow-style: none;
    touch-action: pan-x;
}
.polycms-products-slider-track::-webkit-scrollbar,
.fw-featured-slider-track::-webkit-scrollbar {
    display: none;
}

/* Base Card Container */
.polycms-products-slider-wrap .polycms-products-slide-item,
.fw-featured-slider-wrap .fw-featured-slide-item,
.polycms-products-slide-item,
.fw-featured-slide-item {
    flex: 0 0 230px !important;
    width: 230px !important;
    max-width: 230px !important;
    background: var(--geist-background, #ffffff) !important;
    border: 1px solid var(--color-border, #e2e8f0) !important;
    border-radius: var(--radius, 12px) !important;
    overflow: hidden !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease !important;
    display: flex !important;
    flex-direction: column !important;
    box-sizing: border-box !important;
    margin: 0 !important;
}
.polycms-products-slide-item:hover,
.fw-featured-slide-item:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08) !important;
    border-color: var(--geist-accents-3, #cbd5e1) !important;
}

/* Card Image Area */
.polycms-products-slider-wrap .polycms-products-item-image,
.fw-featured-slider-wrap .fw-featured-item-image,
.polycms-products-item-image,
.fw-featured-item-image {
    position: relative !important;
    width: 100% !important;
    aspect-ratio: 16 / 10 !important;
    overflow: hidden !important;
    background: var(--geist-accents-1, #f8fafc) !important;
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
}
.polycms-products-item-image img,
.fw-featured-item-image img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    transition: transform 0.3s ease !important;
    pointer-events: none !important;
    display: block !important;
    margin: 0 !important;
}
.polycms-products-slide-item:hover .polycms-products-item-image img,
.fw-featured-slide-item:hover .fw-featured-item-image img {
    transform: scale(1.04) !important;
}

/* Status Badges */
.polycms-products-slider-wrap .polycms-products-badge,
.fw-featured-slider-wrap .fw-featured-badge,
.polycms-products-badge,
.fw-featured-badge {
    position: absolute !important;
    top: 8px !important;
    left: 8px !important;
    padding: 3px 8px !important;
    font-size: 0.6875rem !important; /* 11px */
    font-weight: 700 !important;
    background: rgba(15, 23, 42, 0.88) !important;
    color: #fef08a !important;
    border-radius: 6px !important;
    backdrop-filter: blur(6px) !important;
    -webkit-backdrop-filter: blur(6px) !important;
    z-index: 2 !important;
    pointer-events: none !important;
    line-height: 1 !important;
    margin: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important;
    font-family: inherit !important;
}
.polycms-products-badge .badge-text {
    line-height: 1 !important;
    white-space: nowrap !important;
}
.polycms-products-badge-bestseller,
.fw-bestseller-badge {
    background: rgba(234, 88, 12, 0.92) !important;
    color: #fff !important;
}
.polycms-products-badge-trending {
    background: rgba(147, 51, 234, 0.92) !important;
    color: #fff !important;
}
.polycms-products-badge-rated {
    background: rgba(202, 138, 4, 0.92) !important;
    color: #fff !important;
}

/* Header Icon Container */
.polycms-slider-header-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 28px !important;
    height: 28px !important;
    border-radius: 50% !important;
    background: var(--header-icon-bg, #fef08a) !important;
    color: var(--header-icon-color, #a16207) !important;
    font-size: 0.85rem !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
    font-family: 'Apple Color Emoji', 'Segoe UI Emoji', 'Noto Color Emoji', sans-serif !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}
.polycms-slider-header-icon svg {
    display: block !important;
}
html.dark .polycms-slider-header-icon,
.dark .polycms-slider-header-icon {
    background: var(--header-icon-bg-dark, rgba(234, 179, 8, 0.18)) !important;
    color: var(--header-icon-color-dark, #facc15) !important;
}

/* Card Body */
.polycms-products-slider-wrap .polycms-products-item-body,
.fw-featured-slider-wrap .fw-featured-item-body,
.polycms-products-slide-item .polycms-products-item-body,
.fw-featured-slide-item .fw-featured-item-body {
    padding: 10px 12px 12px 12px !important;
    display: flex !important;
    flex-direction: column !important;
    flex-grow: 1 !important;
    margin: 0 !important;
    box-sizing: border-box !important;
}

/* Category Label */
.polycms-products-slider-wrap .polycms-products-item-cat,
.fw-featured-slider-wrap .fw-featured-item-cat,
.polycms-products-slide-item .polycms-products-item-cat,
.fw-featured-slide-item .fw-featured-item-cat {
    font-size: 0.625rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.04em !important;
    color: var(--geist-accents-5, #64748b) !important;
    margin: 0 0 4px 0 !important;
    padding: 0 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

/* Strict Title Protection against .prose h3 overrides */
.polycms-products-slider-wrap .polycms-products-item-title,
.fw-featured-slider-wrap .fw-featured-item-title,
.polycms-products-slide-item .polycms-products-item-title,
.fw-featured-slide-item .fw-featured-item-title,
.polycms-products-slide-item h3,
.fw-featured-slide-item h3,
.prose .polycms-products-slide-item h3,
.prose .fw-featured-slide-item h3 {
    font-size: 0.8125rem !important;
    font-weight: 600 !important;
    line-height: 1.35 !important;
    margin: 0 0 6px 0 !important;
    padding: 0 !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    min-height: 2.2rem !important;
    max-height: 2.2rem !important;
    height: 2.2rem !important;
    letter-spacing: normal !important;
    color: var(--geist-foreground, #0f172a) !important;
}
.polycms-products-slider-wrap .polycms-products-item-title a,
.fw-featured-slider-wrap .fw-featured-item-title a,
.polycms-products-slide-item h3 a,
.fw-featured-slide-item h3 a,
.prose .polycms-products-slide-item h3 a,
.prose .fw-featured-slide-item h3 a {
    font-size: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
    color: var(--geist-foreground, #0f172a) !important;
    text-decoration: none !important;
    display: inline !important;
    transition: color 0.15s ease !important;
}
.polycms-products-slider-wrap .polycms-products-item-title a:hover,
.fw-featured-slider-wrap .fw-featured-item-title a:hover,
.polycms-products-slide-item h3 a:hover,
.fw-featured-slide-item h3 a:hover,
.prose .polycms-products-slide-item h3 a:hover,
.prose .fw-featured-slide-item h3 a:hover {
    color: var(--primary-color, #3b82f6) !important;
}

/* Price Row */
.polycms-products-slider-wrap .polycms-products-item-price,
.fw-featured-slider-wrap .fw-featured-item-price,
.polycms-products-slide-item .polycms-products-item-price,
.fw-featured-slide-item .fw-featured-item-price {
    margin-top: auto !important;
    padding-top: 4px !important;
    font-size: 0.875rem !important;
    font-weight: 700 !important;
    color: var(--geist-foreground, #0f172a) !important;
    display: flex !important;
    align-items: baseline !important;
    gap: 6px !important;
    line-height: 1 !important;
}
.polycms-products-item-price .price-old,
.fw-featured-item-price .price-old {
    font-size: 0.72rem !important;
    color: var(--geist-accents-4, #94a3b8) !important;
    text-decoration: line-through !important;
    font-weight: 400 !important;
}

/* Nav Buttons */
.polycms-slider-prev-btn,
.polycms-slider-next-btn,
.fw-slider-prev-btn,
.fw-slider-next-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid var(--color-border, #e2e8f0);
    background: var(--geist-background, #fff);
    color: var(--geist-foreground, #334155);
    font-size: 1.05rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.polycms-slider-prev-btn:hover,
.polycms-slider-next-btn:hover,
.fw-slider-prev-btn:hover,
.fw-slider-next-btn:hover {
    background: var(--primary-color, #3b82f6);
    color: #ffffff;
    border-color: var(--primary-color, #3b82f6);
}

/* Dark Mode */
html.dark .polycms-products-slide-item,
.dark .polycms-products-slide-item,
html.dark .fw-featured-slide-item,
.dark .fw-featured-slide-item {
    background: #0f172a !important;
    border-color: rgba(148, 163, 184, 0.2) !important;
}
html.dark .polycms-slider-prev-btn,
html.dark .polycms-slider-next-btn,
.dark .polycms-slider-prev-btn,
.dark .polycms-slider-next-btn,
html.dark .fw-slider-prev-btn,
html.dark .fw-slider-next-btn,
.dark .fw-slider-prev-btn,
.dark .fw-slider-next-btn {
    background: #1e293b;
    border-color: rgba(148, 163, 184, 0.25);
    color: #cbd5e1;
}

@media (max-width: 640px) {
    .polycms-products-slider-wrap .polycms-products-slide-item,
    .fw-featured-slider-wrap .fw-featured-slide-item,
    .polycms-products-slide-item,
    .fw-featured-slide-item {
        flex: 0 0 190px !important;
        width: 190px !important;
        max-width: 190px !important;
    }
}
</style>

@if(isset($products) && $products->count() > 0)
    <div class="polycms-products-slider-wrap fw-featured-slider-wrap" id="{{ $blockId }}" data-instance="{{ $instanceUid }}">
        {{-- Section Header (Only rendered when heading, subtitle, view all, or slider nav buttons exist) --}}
        @if($hasHeader)
        <div class="polycms-products-slider-header fw-featured-slider-header" style="display: flex; align-items: center; justify-content: {{ $hasHeading || $hasSubtitle ? 'space-between' : 'flex-end' }}; margin-bottom: 1.125rem;">
            @if($hasHeading || $hasSubtitle)
            <div style="display: flex; align-items: center; gap: 10px;">
                @if($hasHeading && !empty($headerIcon))
                <div class="polycms-slider-header-icon" style="--header-icon-bg: {{ $headerBg }}; --header-icon-color: {{ $headerColor }}; --header-icon-bg-dark: {{ $headerBgDark }}; --header-icon-color-dark: {{ $headerColorDark }};">
                    {!! $headerIcon !!}
                </div>
                @endif
                <div>
                    @if($hasHeading)
                    <h2 class="polycms-products-slider-title fw-featured-slider-title" style="font-size: 1.125rem; font-weight: 700; margin: 0; color: var(--geist-foreground, inherit);">
                        {{ $heading }}
                    </h2>
                    @endif
                    @if($hasSubtitle)
                        <p style="font-size: 0.75rem; color: var(--geist-accents-5, #64748b); margin: 2px 0 0 0;">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @endif

            <div style="display: flex; align-items: center; gap: 8px;">
                @if($hasViewAll)
                    <a href="{{ $viewAllUrl }}" class="btn btn-sm btn-ghost" style="font-size: 0.75rem; font-weight: 600; text-decoration: none; color: var(--primary-color, #3b82f6); margin-right: 4px;">
                        {{ __('View All') }} &rarr;
                    </a>
                @endif

                @if($hasSliderNav)
                <div class="polycms-slider-nav-btns fw-slider-nav-btns" style="display: flex; align-items: center; gap: 6px;">
                    <button type="button" class="polycms-slider-prev-btn fw-slider-prev-btn" aria-label="{{ __('Previous') }}" data-nav="prev">‹</button>
                    <button type="button" class="polycms-slider-next-btn fw-slider-next-btn" aria-label="{{ __('Next') }}" data-nav="next">›</button>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($layout === 'grid')
            {{-- Grid Layout --}}
            <div class="listing-container is-grid" style="--listing-columns: {{ $columns }}; display: grid; grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr)); gap: 1rem;">
                @foreach($products as $product)
                    <article class="polycms-products-slide-item fw-featured-slide-item" style="flex: 1 1 auto; width: 100%;">
                        @if($showMedia)
                        <a href="{{ $product->frontend_url }}" class="polycms-products-item-image fw-featured-item-image">
                            @php $imageUrl = $product->featured_image_url; @endphp
                            @if(!empty($imageUrl))
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" {!! media_lazy_attr() !!}>
                            @else
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif

                            @if($showBadge)
                                @if($product->featured)
                                    <span class="polycms-products-badge polycms-products-badge-featured fw-featured-badge">
                                        <span class="badge-text">{{ __('Featured') }}</span>
                                    </span>
                                @elseif(in_array($filterBy, ['best_sellers', 'bestsellers', 'best_seller'], true) || (!empty($minRequiredItems) && !$product->featured))
                                    <span class="polycms-products-badge polycms-products-badge-bestseller fw-featured-badge fw-bestseller-badge">
                                        <span class="badge-text">{{ __('Best Seller') }}</span>
                                    </span>
                                @elseif(in_array($filterBy, ['trending', 'popular', 'views'], true))
                                    <span class="polycms-products-badge polycms-products-badge-trending fw-featured-badge">
                                        <span class="badge-text">{{ __('Trending') }}</span>
                                    </span>
                                @elseif(in_array($filterBy, ['best_rated', 'rating'], true))
                                    <span class="polycms-products-badge polycms-products-badge-rated fw-featured-badge">
                                        <span class="badge-text">{{ __('Best Rated') }}</span>
                                    </span>
                                @endif
                            @endif
                        </a>
                        @endif

                        <div class="polycms-products-item-body fw-featured-item-body">
                            @if($showCategories && $product->categories && $product->categories->count() > 0)
                                <div class="polycms-products-item-cat fw-featured-item-cat">{{ $product->categories->first()->name }}</div>
                            @endif

                            @if($showTitle)
                            <h3 class="polycms-products-item-title fw-featured-item-title">
                                <a href="{{ $product->frontend_url }}">{{ $product->name }}</a>
                            </h3>
                            @endif

                            @if($showPrice)
                            <div class="polycms-products-item-price fw-featured-item-price">
                                @if($product->sale_price && $product->sale_price > 0 && $product->sale_price < $product->price)
                                    <span class="price-current font-bold text-emerald-600">${{ number_format((float)$product->sale_price, 2) }}</span>
                                    <span class="price-old">${{ number_format((float)$product->price, 2) }}</span>
                                @else
                                    <span class="price-current font-bold">{{ $product->price > 0 ? '$' . number_format((float)$product->price, 2) : __('Free') }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            {{-- Slider Touch Track Layout with Independent Instance Handling --}}
            <div id="{{ $trackId }}" class="polycms-products-slider-track fw-featured-slider-track" data-slider-instance="{{ $instanceUid }}">
                @foreach($products as $product)
                    <article class="polycms-products-slide-item fw-featured-slide-item">
                        @if($showMedia)
                        <a href="{{ $product->frontend_url }}" class="polycms-products-item-image fw-featured-item-image">
                            @php $imageUrl = $product->featured_image_url; @endphp
                            @if(!empty($imageUrl))
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" {!! media_lazy_attr() !!}>
                            @else
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif

                            @if($showBadge)
                                @if($product->featured)
                                    <span class="polycms-products-badge polycms-products-badge-featured fw-featured-badge">
                                        <span class="badge-text">{{ __('Featured') }}</span>
                                    </span>
                                @elseif(in_array($filterBy, ['best_sellers', 'bestsellers', 'best_seller'], true) || (!empty($minRequiredItems) && !$product->featured))
                                    <span class="polycms-products-badge polycms-products-badge-bestseller fw-featured-badge fw-bestseller-badge">
                                        <span class="badge-text">{{ __('Best Seller') }}</span>
                                    </span>
                                @elseif(in_array($filterBy, ['trending', 'popular', 'views'], true))
                                    <span class="polycms-products-badge polycms-products-badge-trending fw-featured-badge">
                                        <span class="badge-text">{{ __('Trending') }}</span>
                                    </span>
                                @elseif(in_array($filterBy, ['best_rated', 'rating'], true))
                                    <span class="polycms-products-badge polycms-products-badge-rated fw-featured-badge">
                                        <span class="badge-text">{{ __('Best Rated') }}</span>
                                    </span>
                                @endif
                            @endif
                        </a>
                        @endif

                        <div class="polycms-products-item-body fw-featured-item-body">
                            @if($showCategories && $product->categories && $product->categories->count() > 0)
                                <div class="polycms-products-item-cat fw-featured-item-cat">{{ $product->categories->first()->name }}</div>
                            @endif

                            @if($showTitle)
                            <h3 class="polycms-products-item-title fw-featured-item-title">
                                <a href="{{ $product->frontend_url }}">{{ $product->name }}</a>
                            </h3>
                            @endif

                            @if($showPrice)
                            <div class="polycms-products-item-price fw-featured-item-price">
                                @if($product->sale_price && $product->sale_price > 0 && $product->sale_price < $product->price)
                                    <span class="price-current font-bold text-emerald-600">${{ number_format((float)$product->sale_price, 2) }}</span>
                                    <span class="price-old">${{ number_format((float)$product->price, 2) }}</span>
                                @else
                                    <span class="price-current font-bold">{{ $product->price > 0 ? '$' . number_format((float)$product->price, 2) : __('Free') }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Isolated Independent Slider Instance Engine --}}
            <script>
            (function() {
                var instanceUid = '{{ $instanceUid }}';
                var root = document.getElementById('{{ $blockId }}');
                var track = document.getElementById('{{ $trackId }}');
                if (!track || !root) return;

                // Ensure browser scroll-snap is disabled on track to prevent sub-pixel locking
                track.style.scrollSnapType = 'none';
                track.style.scrollBehavior = 'auto';

                var isAutoplay = {{ $isAutoplay ? 'true' : 'false' }};
                var mode = '{{ $sliderMode }}'; // 'continuous' or 'stepped'
                var direction = '{{ $sliderDirection }}'; // 'left' or 'right'
                var dirMultiplier = (direction === 'right' || direction === 'backward' || direction === 'reverse') ? -1 : 1;
                var continuousSpeed = {{ $continuousSpeed }}; // px/sec
                var steppedSpeed = {{ $steppedSpeed }}; // ms
                var pauseOnHover = {{ $pauseOnHover ? 'true' : 'false' }};

                var isInteracting = false;
                var resumeTimer = null;
                var singleSetWidth = 0;
                var rafId = null;
                var intervalId = null;
                var lastTimestamp = 0;

                // Clone initial set to ensure seamless infinite looping without jumping
                var originalItems = Array.prototype.slice.call(track.children);
                if (originalItems.length >= 2) {
                    originalItems.forEach(function(item) {
                        var clone = item.cloneNode(true);
                        clone.setAttribute('aria-hidden', 'true');
                        clone.setAttribute('data-cloned', 'true');
                        track.appendChild(clone);
                    });
                }

                function computeSingleSetWidth() {
                    var total = 0;
                    originalItems.forEach(function(el) {
                        total += el.offsetWidth + 16; // width + gap
                    });
                    singleSetWidth = total || (track.scrollWidth / 2);
                }

                computeSingleSetWidth();
                window.addEventListener('resize', computeSingleSetWidth);

                function getScrollStep() {
                    var firstItem = track.querySelector('.polycms-products-slide-item, .fw-featured-slide-item');
                    return firstItem ? (firstItem.offsetWidth + 16) : 246;
                }

                function scheduleResume(delayMs) {
                    if (resumeTimer) {
                        clearTimeout(resumeTimer);
                        resumeTimer = null;
                    }
                    if (!isAutoplay) return;

                    resumeTimer = setTimeout(function() {
                        isInteracting = false;
                        lastTimestamp = performance.now();
                    }, delayMs || 3500);
                }

                function pauseTemporary() {
                    isInteracting = true;
                    if (resumeTimer) {
                        clearTimeout(resumeTimer);
                        resumeTimer = null;
                    }
                }

                // Nav button bindings (Prev / Next scoped strictly to this root instance)
                var prevBtn = root.querySelector('[data-nav="prev"]');
                var nextBtn = root.querySelector('[data-nav="next"]');

                if (prevBtn) {
                    prevBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        pauseTemporary();
                        var step = getScrollStep();
                        
                        // Instant wrap jump without smooth animation
                        track.style.scrollBehavior = 'auto';
                        if (track.scrollLeft <= 5) {
                            track.scrollLeft += singleSetWidth;
                        }
                        void track.offsetWidth; // Force reflow
                        
                        // Perform smooth step backwards
                        track.style.scrollBehavior = 'smooth';
                        track.scrollBy({ left: -step, behavior: 'smooth' });
                        scheduleResume(4000);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        pauseTemporary();
                        var step = getScrollStep();

                        track.style.scrollBehavior = 'smooth';
                        track.scrollBy({ left: step, behavior: 'smooth' });

                        setTimeout(function() {
                            if (track && track.scrollLeft >= singleSetWidth) {
                                track.style.scrollBehavior = 'auto';
                                track.scrollLeft -= singleSetWidth;
                                void track.offsetWidth;
                            }
                        }, 400);

                        scheduleResume(4000);
                    });
                }

                // Touch & Mobile Gestures Support (Natural swipe with boundary wrap and auto-resume)
                track.addEventListener('touchstart', function() {
                    pauseTemporary();
                    track.style.scrollBehavior = 'auto';
                }, { passive: true });

                track.addEventListener('touchmove', function() {
                    pauseTemporary();
                    if (singleSetWidth > 0) {
                        if (track.scrollLeft >= singleSetWidth * 1.8) {
                            track.scrollLeft -= singleSetWidth;
                        } else if (track.scrollLeft <= 0) {
                            track.scrollLeft += singleSetWidth;
                        }
                    }
                }, { passive: true });

                track.addEventListener('touchend', function() {
                    scheduleResume(3500);
                }, { passive: true });

                // Desktop Hover Pausing
                if (pauseOnHover) {
                    track.addEventListener('mouseenter', function() {
                        pauseTemporary();
                    });
                    track.addEventListener('mouseleave', function() {
                        scheduleResume(1500);
                    });
                }

                // Continuous 1-Direction Smooth Marquee Flow
                function continuousLoop(timestamp) {
                    if (!lastTimestamp) lastTimestamp = timestamp;
                    var delta = (timestamp - lastTimestamp) / 1000;
                    lastTimestamp = timestamp;

                    if (!isInteracting && track && singleSetWidth > 0) {
                        track.style.scrollBehavior = 'auto';
                        if (dirMultiplier === 1) {
                            track.scrollLeft += continuousSpeed * delta;
                            if (track.scrollLeft >= singleSetWidth) {
                                track.scrollLeft -= singleSetWidth;
                            }
                        } else {
                            track.scrollLeft -= continuousSpeed * delta;
                            if (track.scrollLeft <= 0) {
                                track.scrollLeft += singleSetWidth;
                            }
                        }
                    }

                    rafId = requestAnimationFrame(continuousLoop);
                }

                // Stepped 1-Direction Slide
                function steppedTick() {
                    if (isInteracting || !track || singleSetWidth <= 0) return;
                    var step = getScrollStep() * dirMultiplier;
                    if (dirMultiplier === -1 && track.scrollLeft <= 5) {
                        track.style.scrollBehavior = 'auto';
                        track.scrollLeft += singleSetWidth;
                        void track.offsetWidth;
                    }
                    track.style.scrollBehavior = 'smooth';
                    track.scrollBy({ left: step, behavior: 'smooth' });
                    setTimeout(function() {
                        if (track && track.scrollLeft >= singleSetWidth) {
                            track.style.scrollBehavior = 'auto';
                            track.scrollLeft -= singleSetWidth;
                            void track.offsetWidth;
                        }
                    }, 500);
                }

                // Start Autoplay Engine
                if (isAutoplay) {
                    if (mode === 'continuous') {
                        rafId = requestAnimationFrame(continuousLoop);
                    } else {
                        intervalId = setInterval(steppedTick, steppedSpeed);
                    }
                }
            })();
            </script>
        @endif
    </div>
@endif
