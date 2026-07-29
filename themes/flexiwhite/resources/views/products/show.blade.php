@extends('layouts.app')

@section('title', $product->name ?? _l('Product'))
@section('description', $product->meta_description ?? $product->short_description ?? '')

@push('styles')
    <link rel="stylesheet" href="{{ theme_asset('css/shop.css') }}?v={{ time() }}">
    <style>
        .single-product-tab-nav { display: flex; flex-wrap: wrap; gap: 24px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color, #e5e7eb); }
        .single-product-tab-link { display: inline-flex; align-items: center; padding: 10px 0; border-bottom: 2px solid transparent; font-weight: 600; color: var(--muted-color, #64748b); text-decoration: none; line-height: 1.3; }
        .single-product-tab-link.is-active { color: var(--text-color, #0f172a); border-bottom-color: var(--primary-color, #3b82f6); }
        .single-product-tab-content { min-height: 340px; }
        .single-product-tab-panel { display: none; }
        .single-product-tab-panel.is-active { display: block; }
        .single-product-tab-panel .faq-container { display: grid; gap: 10px; margin: 16px 0; padding: 0; }
        .single-product-tab-panel .faq-item { border: 1px solid var(--border-color, #e2e8f0); border-radius: 12px; overflow: hidden; background: #ffffff; }
        .single-product-tab-panel .faq-question { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px; font-weight: 700; color: #0f172a; cursor: pointer; }
        .single-product-tab-panel .faq-answer { display: none; padding: 0 14px 14px; color: #475569; }
        .single-product-tab-panel .faq-answer.active { display: block; }

        .dark .single-product-tab-nav { border-bottom-color: rgba(148, 163, 184, 0.25); }
        .dark .single-product-tab-link { color: #94a3b8; }
        .dark .single-product-tab-link:hover { color: #cbd5e1; }
        .dark .single-product-tab-link.is-active { color: #e2e8f0; border-bottom-color: #60a5fa; }
        .dark .single-product-tab-panel .faq-item { border-color: rgba(148, 163, 184, 0.35); background: rgba(15, 23, 42, 0.4); }
        .dark .single-product-tab-panel .faq-question { color: #e2e8f0; }
        .dark .single-product-tab-panel .faq-answer { color: #cbd5e1; }
        .dark .single-product-tab-panel .faq-question i { color: #93c5fd; }

        /* Single Product Stats Row Styling (Flat & Simple) */
        .single-product-stats-row {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            margin-top: 8px !important;
            margin-bottom: 14px !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            font-size: 0.9rem !important;
            color: #64748b !important;
            flex-wrap: wrap !important;
            line-height: 1.4 !important;
        }
        .single-product-stats-row .stats-num {
            color: #0f172a !important;
            font-weight: 700 !important;
        }
        .single-product-stats-row .stats-icon {
            color: #64748b !important;
            font-size: 0.85rem !important;
        }
        .single-product-stats-row .stats-separator {
            color: #cbd5e1 !important;
        }
        .single-product-stats-row .single-product-meta-item {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-bottom: 0 !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            color: inherit !important;
        }

        html.dark .single-product-stats-row,
        .dark .single-product-stats-row {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: #94a3b8 !important;
        }
        html.dark .single-product-stats-row .stats-num,
        .dark .single-product-stats-row .stats-num {
            color: #f8fafc !important;
        }
        html.dark .single-product-stats-row .stats-icon,
        .dark .single-product-stats-row .stats-icon {
            color: #94a3b8 !important;
        }
        html.dark .single-product-stats-row .stats-separator,
        .dark .single-product-stats-row .stats-separator {
            color: rgba(148, 163, 184, 0.3) !important;
        }

        /* Ratings Tab Styling (Light & Dark Mode) */
        .product-rating-avg-card {
            padding: 24px !important;
            border-radius: 12px !important;
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            text-align: center !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        }
        .product-rating-avg-title {
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 8px !important;
        }
        .product-rating-avg-score {
            font-size: 3.25rem !important;
            font-weight: 900 !important;
            color: #0f172a !important;
            line-height: 1.1 !important;
            margin-bottom: 8px !important;
        }
        .product-rating-avg-stars {
            display: flex !important;
            justify-content: center !important;
            gap: 4px !important;
            font-size: 1.25rem !important;
            color: #f59e0b !important;
            margin-bottom: 8px !important;
        }
        .product-rating-avg-count {
            font-size: 0.85rem !important;
            color: #64748b !important;
            margin: 0 !important;
        }

        .product-rating-breakdown-title {
            font-size: 1rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 !important;
            padding-bottom: 10px !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        .product-rating-breakdown-label {
            font-weight: 600 !important;
            color: #334155 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        .product-rating-breakdown-value {
            font-weight: 700 !important;
            color: #0f172a !important;
        }
        .product-rating-breakdown-sub {
            font-weight: 500 !important;
            color: #64748b !important;
            font-size: 0.8rem !important;
        }
        .product-rating-breakdown-total-row {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            padding-top: 12px !important;
            border-top: 1px dashed #e2e8f0 !important;
        }
        .product-rating-breakdown-total-label {
            color: #0f172a !important;
        }
        .product-rating-breakdown-total-val {
            color: #3b82f6 !important;
        }

        /* Dark mode overrides for Ratings Tab */
        html.dark .product-rating-avg-card,
        .dark .product-rating-avg-card {
            background: #1e293b !important;
            border-color: rgba(148, 163, 184, 0.2) !important;
            box-shadow: none !important;
        }
        html.dark .product-rating-avg-title,
        .dark .product-rating-avg-title {
            color: #94a3b8 !important;
        }
        html.dark .product-rating-avg-score,
        .dark .product-rating-avg-score {
            color: #ffffff !important;
        }
        html.dark .product-rating-avg-count,
        .dark .product-rating-avg-count {
            color: #94a3b8 !important;
        }
        html.dark .product-rating-breakdown-title,
        .dark .product-rating-breakdown-title {
            color: #f8fafc !important;
            border-bottom-color: rgba(148, 163, 184, 0.2) !important;
        }
        html.dark .product-rating-breakdown-label,
        .dark .product-rating-breakdown-label {
            color: #e2e8f0 !important;
        }
        html.dark .product-rating-breakdown-value,
        .dark .product-rating-breakdown-value {
            color: #ffffff !important;
        }
        html.dark .product-rating-breakdown-sub,
        .dark .product-rating-breakdown-sub {
            color: #94a3b8 !important;
        }
        html.dark .product-rating-breakdown-total-row,
        .dark .product-rating-breakdown-total-row {
            border-top-color: rgba(148, 163, 184, 0.2) !important;
        }
        html.dark .product-rating-breakdown-total-label,
        .dark .product-rating-breakdown-total-label {
            color: #f8fafc !important;
        }
        html.dark .product-rating-breakdown-total-val,
        .dark .product-rating-breakdown-total-val {
            color: #60a5fa !important;
        }

        .changelog-prose ul {
            list-style: none !important;
            padding-left: 0 !important;
            margin: 10px 0 !important;
        }
        .changelog-prose li {
            position: relative;
            padding-left: 16px !important;
            margin-bottom: 8px !important;
            line-height: 1.5;
            list-style: none !important;
        }
        .changelog-prose li::before {
            content: "-";
            position: absolute;
            left: 0;
            color: inherit;
        }
    </style>
@endpush

@section('breadcrumb')
    @php
        $productArchiveUrl = theme_permalink_url('products', '', 'archive');
        $breadcrumbs = [
            ['label' => _l('Home'), 'url' => url('/')],
            ['label' => _l('Products'), 'url' => $productArchiveUrl],
        ];
        if ($product->categories->count() > 0) {
            $displayCategory = $product->categories->sortByDesc('depth')->first();
            foreach (collect($displayCategory->breadcrumb ?? [])->filter() as $cat) {
                $breadcrumbs[] = ['label' => $cat->name, 'url' => $cat->frontend_url];
            }
        }
        $breadcrumbs[] = ['label' => $product->name, 'url' => null];
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
            <div class="single-product-grid">
                    <!-- Product Image Gallery -->
                    <div>
                        @if($product->media && $product->media->count() > 0)
                            <div class="single-product-image-wrap">
                                <img id="main-product-image" src="{{ $product->featured_image_url ?? '' }}" alt="{{ $product->name }}" class="single-product-image" {!! media_lazy_attr() !!}>
                            </div>
                            
                            @if($product->media->count() > 1)
                                <div class="single-product-thumbnails">
                                    @foreach($product->media as $index => $media)
                                        <button onclick="document.getElementById('main-product-image').src='{{ $media->url ?? '' }}'" class="single-product-thumbnail-btn" aria-label="{{ _l('View image') }} {{ $index + 1 }}">
                                            <img src="{{ $media->thumbnail_url ?? $media->url ?? '' }}" alt="{{ $product->name }} thumbnail" class="single-product-thumbnail-img" {!! media_lazy_attr() !!}>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="single-product-no-image">
                                <span>{{ _l('No Image') }}</span>
                            </div>
                        @endif

                        <!-- Product Media Actions (Live Preview, Gallery, Video) -->
                        @php
                            $demoUrl = trim((string) data_get($product->settings, 'demo_url', ''));
                            $galleryItems = collect($product->media ?? [])->map(function ($media) use ($product) {
                                return [
                                    'url' => $media->url ?? '',
                                    'thumbnail_url' => $media->thumbnail_url ?? $media->url ?? '',
                                    'alt' => $product->name,
                                ];
                            })->filter(fn ($item) => !empty($item['url']))->values();
                            $previewVideos = collect(data_get($product->settings, 'preview_videos', []))->filter(fn($v) => !empty($v['link']))->values();
                        @endphp

                        @php
                            $buttonCount = 0;
                            if (!empty($demoUrl)) $buttonCount++;
                            if ($galleryItems->isNotEmpty()) $buttonCount++;
                            if ($previewVideos->isNotEmpty()) $buttonCount++;
                        @endphp

                        <div class="product-media-grid cols-{{ $buttonCount }}" style="margin-top: 15px; width: 100%;">
                            @if(!empty($demoUrl))
                                <a href="{{ route('products.preview', ['slug' => $product->slug]) }}" target="_blank" class="media-action-btn media-action-btn--primary" style="text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; padding: 10px; border-radius: 8px; font-size: 0.9rem; background: var(--geist-success, #0070f3); color: #fff; border: 1px solid transparent; transition: all 0.2s; cursor: pointer; width: 100%; box-sizing: border-box;">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>{{ _l('Preview') }}</span>
                                </a>
                            @endif
                            @if($galleryItems->isNotEmpty())
                                <button type="button" data-open-screenshots class="media-action-btn" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; padding: 10px; border-radius: 8px; font-size: 0.9rem; background: #fff; color: #334155; border: 1px solid var(--geist-accents-2, #eaeaea); cursor: pointer; transition: all 0.2s; width: 100%; box-sizing: border-box;">
                                    <i class="far fa-images"></i>
                                    <span>{{ _l('Gallery') }}</span>
                                </button>
                            @endif
                            @if($previewVideos->isNotEmpty())
                                <button type="button" data-open-videos class="media-action-btn" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; padding: 10px; border-radius: 8px; font-size: 0.9rem; background: #fff; color: #334155; border: 1px solid var(--geist-accents-2, #eaeaea); cursor: pointer; transition: all 0.2s; width: 100%; box-sizing: border-box;">
                                    <i class="fas fa-play-circle"></i>
                                    <span>{{ _l('Video') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Product Summary -->
                    <div class="single-product-summary">
                        <h1 class="post-title" style="margin-bottom: 0.25rem;">
                            {{ $product->name }}
                        </h1>
                        {!! \App\Facades\Hook::doAction('theme.product.single.after_title', $product) !!}

                        @php
                            $effectivePrice = (float) $product->effective_price;
                            $regularPrice = (float) $product->price;
                            $salePrice = (float) ($product->sale_price ?? 0);
                            $hasSale = ($salePrice > 0 && $salePrice < $regularPrice) || ($effectivePrice > 0 && $effectivePrice < $regularPrice);
                            $currentPrice = ($effectivePrice > 0 && $effectivePrice < $regularPrice) ? $effectivePrice : (($salePrice > 0 && $salePrice < $regularPrice) ? $salePrice : $regularPrice);
                            $strikePrice = $hasSale ? $regularPrice : null;
                        @endphp
                        <div class="single-product-price-row">
                            @if($hasSale)
                                <span class="product-price single-product-price">{{ format_currency($currentPrice) }}</span>
                                <span class="product-price-strike single-product-price-strike">{{ format_currency($strikePrice) }}</span>
                                @if($effectivePrice > 0 && $effectivePrice < $regularPrice)
                                    <span class="badge" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-weight: 700;">{{ _l('Offer Deal') }}</span>
                                @else
                                    <span class="badge">{{ _l('Sale') }}</span>
                                @endif
                            @else
                                <span class="product-price single-product-price">{{ format_currency($currentPrice) }}</span>
                            @endif
                        </div>
                        {!! \App\Facades\Hook::doAction('theme.product.single.after_price', $product) !!}

                        <div class="single-product-stats-row">
                            {!! \App\Facades\Hook::doAction('theme.product.single.meta', $product) !!}
                            
                            @if($product->review_count > 0)
                                <span class="stats-rating" style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="display: inline-flex; align-items: center;">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($product->avg_rating))
                                                <i class="fas fa-star" style="color: #f59e0b; margin-right: 1px;"></i>
                                            @else
                                                <i class="far fa-star" style="color: #cbd5e1; margin-right: 1px;"></i>
                                            @endif
                                        @endfor
                                    </span>
                                    <strong class="stats-num">{{ number_format($product->avg_rating, 1) }}</strong>
                                    <span class="stats-label">({{ $product->review_count }} {{ _l('reviews') }})</span>
                                </span>
                            @endif

                            <span class="stats-views" style="display: inline-flex; align-items: center; gap: 4px;">
                                <i class="far fa-eye stats-icon"></i>
                                <strong class="stats-num">{{ $product->views ?? 0 }}</strong> <span class="stats-label">{{ _l('views') }}</span>
                            </span>
                        </div>

                        @if($product->stock_status === 'in_stock')
                            <p class="single-product-stock in-stock">&#10003; {{ _l('In Stock') }}</p>
                        @elseif($product->stock_status === 'out_of_stock')
                            <p class="single-product-stock out-of-stock">&#10007; {{ _l('Out of Stock') }}</p>
                        @elseif($product->stock_status === 'on_backorder')
                            <p class="single-product-stock on-backorder">&circlearrowright; {{ _l('On Backorder') }}</p>
                        @endif

                        @if(!empty($product->short_description))
                            <div class="single-product-short-desc">
                                <p>{{ $product->short_description }}</p>
                            </div>
                        @endif

                        @php
                            $defaultCartItem = [
                                'product_id' => $product->id,
                                'service_id' => null,
                                'variant_id' => null,
                                'name' => $product->name,
                                'price' => (float) $currentPrice,
                                'quantity' => 1,
                                'billing_cycle' => 'month',
                                'image_url' => $product->primaryImage()?->url,
                                'slug' => $product->slug,
                                'permalink' => $product->frontend_url
                            ];
                            $purchaseCtaSummary = data_get($product->settings, 'purchase_cta_summary');
                        @endphp
                        @if(!empty($purchaseCtaSummary))
                            <div class="single-product-purchase-cta" style="margin-top: 12px; margin-bottom: 16px; font-size: 0.95rem; line-height: 1.5; color: #475569;">
                                {!! nl2br(e($purchaseCtaSummary)) !!}
                            </div>
                        @endif

                        @php
                            $purchaseButtons = data_get($product->settings, 'purchase_options.buttons', []);
                            $activeButtons = collect($purchaseButtons)->filter(fn($btn) => data_get($btn, 'is_active', false))->values();
                            
                            $buttonPresets = collect();
                            $presetUuids = collect($activeButtons)->pluck('preset_uuid')->filter()->unique()->values();
                            if ($presetUuids->isNotEmpty()) {
                                $buttonPresets = \App\Models\CorePreset::whereIn('uuid', $presetUuids)->get()->keyBy('uuid');
                            }
                        @endphp

                        @foreach($activeButtons as $idx => $btn)
                            @php
                                $btnId = 'btn-purchase-' . ($btn['id'] ?? $idx);
                                $presetUuid = data_get($btn, 'preset_uuid');
                                $preset = $presetUuid ? $buttonPresets->get($presetUuid) : null;
                                $hasPreset = !empty($preset) && !empty($preset->payload);
                            @endphp
                            @if($hasPreset)
                                <style>
                                    .btn-preset-{{ $btnId }} {
                                        background-color: {{ data_get($preset->payload, 'bg_color', '#1e293b') }} !important;
                                        color: {{ data_get($preset->payload, 'text_color', '#fff') }} !important;
                                        border-color: {{ data_get($preset->payload, 'border_color', '#1e293b') }} !important;
                                        border-style: solid;
                                        border-width: 1px;
                                        border-radius: {{ data_get($preset->payload, 'border_radius', '6px') }} !important;
                                        padding-top: {{ data_get($preset->payload, 'inner_padding_top', 12) }}px !important;
                                        padding-bottom: {{ data_get($preset->payload, 'inner_padding_bottom', 12) }}px !important;
                                        padding-left: {{ data_get($preset->payload, 'inner_padding_left', 24) }}px !important;
                                        padding-right: {{ data_get($preset->payload, 'inner_padding_right', 24) }}px !important;
                                        font-size: {{ data_get($preset->payload, 'font_size', '0.875rem') }} !important;
                                        font-weight: {{ data_get($preset->payload, 'font_weight', '600') }} !important;
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                        transition: all 0.2s ease-in-out;
                                    }
                                    .btn-preset-{{ $btnId }}:hover {
                                        background-color: {{ data_get($preset->payload, 'hover_bg_color', '#334155') }} !important;
                                        color: {{ data_get($preset->payload, 'hover_text_color', '#fff') }} !important;
                                        border-color: {{ data_get($preset->payload, 'hover_border_color', '#334155') }} !important;
                                    }
                                </style>
                            @endif
                        @endforeach

                        @if($product->services && $product->services->isNotEmpty())
                            @php
                                $isCommerceOffersActive = class_exists(\App\Services\ModuleManager::class) 
                                    && app(\App\Services\ModuleManager::class)->isModuleEnabled('Polyx.CommerceOffers');

                                $baseRefPrice = (float) ($product->price ?? 0);
                                if ($baseRefPrice <= 0 && $product->services->isNotEmpty()) {
                                    $baseRefPrice = (float) ($product->services->first()->price ?? 1);
                                }

                                $currentOfferPrice = $isCommerceOffersActive ? (float) $product->effective_price : (float) ($product->price ?? 0);
                                $offerRatio = ($isCommerceOffersActive && $baseRefPrice > 0 && $currentOfferPrice > 0 && $currentOfferPrice < $baseRefPrice)
                                    ? ($currentOfferPrice / $baseRefPrice)
                                    : 1.0;

                                $tierRatios = [];
                                if ($isCommerceOffersActive && class_exists(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class)) {
                                    try {
                                        $offersService = app(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class);
                                        $offersMetrics = $offersService->getTierMetrics($product);
                                        if (!empty($offersMetrics['all_tiers'])) {
                                            foreach ($offersMetrics['all_tiers'] as $t) {
                                                $tPrice = (float) $t->price;
                                                $tierRatios[] = $baseRefPrice > 0 ? ($tPrice / $baseRefPrice) : 1.0;
                                            }
                                        }
                                    } catch (\Throwable $e) {}
                                }
                            @endphp

                            @if($product->services->count() === 1)
                                @php 
                                    $service = $product->services->first();
                                    $rawServicePrice = (float) ($service->price ?? $baseRefPrice);
                                    $serviceOfferPrice = round($rawServicePrice * $offerRatio, 2);
                                    $hasServiceSale = ($serviceOfferPrice < $rawServicePrice);
                                    $serviceTiers = [];
                                    foreach ($tierRatios as $ratio) {
                                        $serviceTiers[] = format_currency(round($rawServicePrice * $ratio, 2));
                                    }
                                @endphp
                                <input type="radio" name="selected_service_id" value="{{ $service->id }}" 
                                       data-price="{{ $serviceOfferPrice }}" 
                                       data-price-text="{{ format_currency($serviceOfferPrice) }}"
                                       data-raw-price="{{ $rawServicePrice }}"
                                       data-raw-price-text="{{ format_currency($rawServicePrice) }}"
                                       data-has-sale="{{ $hasServiceSale ? '1' : '0' }}"
                                       data-tiers="{{ json_encode($serviceTiers) }}"
                                       data-name="{{ $service->name }}"
                                       data-billing="{{ $service->access_type === 'subscription' ? ($service->duration_value . ' ' . $service->duration_unit . ($service->duration_value > 1 ? 's' : '')) : 'Lifetime' }}"
                                       checked 
                                       style="display: none;">
                                
                                @if(!empty($service->capabilities))
                                    <div class="product-features-list mb-4" style="margin-top: 15px; margin-bottom: 20px; width: 100%; max-width: 340px;">
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            @foreach($service->capabilities as $capKey => $capVal)
                                                <div style="display: flex; align-items: center;" class="text-slate-600 dark:text-zinc-300">
                                                    <i class="fa fa-check text-success" style="color: #10b981; margin-right: 10px; font-size: 0.9rem;"></i>
                                                    <span style="font-size: 0.85rem;">{{ $capVal }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="product-packages-selector mb-4" style="margin-top: 10px; margin-bottom: 20px; width: 100%; max-width: 340px;">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-zinc-300 mb-2" style="font-weight: 700; margin-bottom: 8px; display: block;">{{ _l('Choose a Package / Plan:') }}</label>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        @foreach($product->services as $index => $service)
                                            @php 
                                                $rawServicePrice = (float) ($service->price ?? $baseRefPrice);
                                                $serviceOfferPrice = round($rawServicePrice * $offerRatio, 2);
                                                $hasServiceSale = ($serviceOfferPrice < $rawServicePrice);
                                                $serviceTiers = [];
                                                foreach ($tierRatios as $ratio) {
                                                    $serviceTiers[] = format_currency(round($rawServicePrice * $ratio, 2));
                                                }
                                            @endphp
                                            <label class="package-option-label" style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; border: 2px solid {{ $index === 0 ? 'var(--primary-color, #3b82f6)' : '#e2e8f0' }}; border-radius: 12px; cursor: pointer; transition: all 0.2s; background: {{ $index === 0 ? 'rgba(59, 130, 246, 0.03)' : '#fff' }}; position: relative;">
                                                <input type="radio" name="selected_service_id" value="{{ $service->id }}" 
                                                       data-price="{{ $serviceOfferPrice }}" 
                                                       data-price-text="{{ format_currency($serviceOfferPrice) }}"
                                                       data-raw-price="{{ $rawServicePrice }}"
                                                       data-raw-price-text="{{ format_currency($rawServicePrice) }}"
                                                       data-has-sale="{{ $hasServiceSale ? '1' : '0' }}"
                                                       data-tiers="{{ json_encode($serviceTiers) }}"
                                                       data-name="{{ $service->name }}"
                                                       data-billing="{{ $service->access_type === 'subscription' ? ($service->duration_value . ' ' . $service->duration_unit . ($service->duration_value > 1 ? 's' : '')) : 'Lifetime' }}"
                                                       {{ $index === 0 ? 'checked' : '' }} 
                                                       style="margin-top: 4px; accent-color: var(--primary-color, #3b82f6);"
                                                       onchange="updateSelectedPackage(this)">
                                                <div style="flex: 1;">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 4px;">
                                                        <span style="font-weight: 700; color: #0f172a;" class="package-name-text">{{ $service->name }}</span>
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <span style="font-weight: 800; color: var(--primary-color, #3b82f6); font-size: 1.1rem;">{{ format_currency($serviceOfferPrice) }}</span>
                                                            @if($hasServiceSale)
                                                                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.85rem; font-weight: 500;">{{ format_currency($rawServicePrice) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #64748b;">
                                                        @if($service->access_type === 'subscription')
                                                            {{ $service->duration_value }} {{ ucfirst($service->duration_unit) }}{{ $service->duration_value > 1 ? 's' : '' }}
                                                            @if($service->is_recurring)
                                                                ({{ _l('Auto-renew') }})
                                                            @endif
                                                        @else
                                                            {{ _l('Lifetime / Never Expires') }}
                                                        @endif
                                                        
                                                        @if($service->trial_period_days > 0)
                                                            <span style="margin-left: 8px; color: #10b981; font-weight: 600;">+ {{ $service->trial_period_days }} {{ _l('days free trial') }}</span>
                                                        @endif
                                                    </div>
                                                    @if(!empty($service->capabilities))
                                                        <div style="margin-top: 8px; font-size: 0.8rem; display: flex; flex-direction: column; gap: 6px;">
                                                            @foreach($service->capabilities as $capKey => $capVal)
                                                                <div style="display: flex; align-items: center;" class="text-slate-600 dark:text-zinc-300">
                                                                    <i class="fa fa-check text-success" style="color: #10b981; margin-right: 8px; font-size: 0.85rem;"></i>
                                                                    <span>{{ $capVal }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Script to update styles and price on radio change -->
                            <script>
                                function updateSelectedPackage(radio) {
                                    // Update all label borders and backgrounds
                                    document.querySelectorAll('.package-option-label').forEach(label => {
                                        label.style.borderColor = '#e2e8f0';
                                        label.style.background = '#fff';
                                        const isDark = document.body.classList.contains('dark') || document.documentElement.classList.contains('dark');
                                        if (isDark) {
                                            label.style.borderColor = 'rgba(148, 163, 184, 0.2)';
                                            label.style.background = 'rgba(30, 41, 59, 0.2)';
                                        }
                                    });
                                    
                                    const activeLabel = radio.closest('.package-option-label');
                                    if (activeLabel) {
                                        activeLabel.style.borderColor = 'var(--primary-color, #3b82f6)';
                                        const isDark = document.body.classList.contains('dark') || document.documentElement.classList.contains('dark');
                                        activeLabel.style.background = isDark ? 'rgba(59, 130, 246, 0.1)' : 'rgba(59, 130, 246, 0.03)';
                                    }
                                    
                                    // Update price display
                                    const priceText = radio.getAttribute('data-price-text');
                                    const rawPriceText = radio.getAttribute('data-raw-price-text');
                                    const hasSale = radio.getAttribute('data-has-sale') === '1';

                                    const priceEl = document.querySelector('.single-product-price');
                                    if (priceEl) {
                                        priceEl.textContent = priceText;
                                    }
                                    const strikeEl = document.querySelector('.single-product-price-strike');
                                    if (strikeEl) {
                                        if (hasSale && rawPriceText) {
                                            strikeEl.textContent = rawPriceText;
                                            strikeEl.style.display = 'inline';
                                        } else {
                                            strikeEl.style.display = 'none';
                                        }
                                    }

                                    // Update Exclusive Offers & Deals Header price
                                    const offersHeaderPriceEl = document.querySelector('.offers-header-price');
                                    if (offersHeaderPriceEl) {
                                        offersHeaderPriceEl.textContent = priceText;
                                    }

                                    // Update Tier Boxes in Exclusive Offers & Deals
                                    const tiersAttr = radio.getAttribute('data-tiers');
                                    if (tiersAttr) {
                                        try {
                                            const tiers = JSON.parse(tiersAttr);
                                            const tierBoxes = document.querySelectorAll('.offers-tier-price-box');
                                            tierBoxes.forEach((box, idx) => {
                                                if (tiers[idx] !== undefined) {
                                                    box.textContent = tiers[idx];
                                                }
                                            });
                                        } catch (e) {}
                                    }
                                }
                                
                                // Initial dark/light mode adjustment
                                document.addEventListener('DOMContentLoaded', () => {
                                    const isDark = document.body.classList.contains('dark') || document.documentElement.classList.contains('dark');
                                    document.querySelectorAll('.package-option-label').forEach(label => {
                                        const radio = label.querySelector('input[type="radio"]');
                                        if (isDark) {
                                            if (!radio.checked) {
                                                label.style.borderColor = 'rgba(148, 163, 184, 0.2)';
                                                label.style.background = 'rgba(30, 41, 59, 0.2)';
                                            } else {
                                                label.style.borderColor = 'var(--primary-color, #3b82f6)';
                                                label.style.background = 'rgba(59, 130, 246, 0.1)';
                                            }
                                            label.querySelector('.package-name-text').style.color = '#e2e8f0';
                                        }
                                    });
                                });
                            </script>
                        @endif

                        <script>
                            function handleAddToCart() {
                                if (typeof window.addToCart !== 'function') return;
                                
                                const selectedRadio = document.querySelector('input[name="selected_service_id"]:checked');
                                const basePayload = {!! json_encode($defaultCartItem ?? ["product_id" => $product->id, "quantity" => 1]) !!};
                                
                                if (selectedRadio) {
                                    const serviceId = parseInt(selectedRadio.value);
                                    const serviceName = selectedRadio.getAttribute('data-name');
                                    const price = parseFloat(selectedRadio.getAttribute('data-price'));
                                    const billingCycle = selectedRadio.getAttribute('data-billing');
                                    
                                    const payload = {
                                        ...basePayload,
                                        service_id: serviceId,
                                        service_name: serviceName,
                                        price: price,
                                        name: basePayload.name + ' - ' + serviceName,
                                        billing_cycle: billingCycle
                                    };
                                    window.addToCart(payload);
                                } else {
                                    window.addToCart(basePayload);
                                }
                            }
                        </script>

                        @if($activeButtons->isNotEmpty())
                            @php
                                $directBtn = $activeButtons->firstWhere('type', 'direct');
                                $externalBtns = $activeButtons->where('type', 'external');
                            @endphp

                            @if($directBtn)
                                @php
                                    $dPresetUuid = data_get($directBtn, 'preset_uuid');
                                    $dPreset = $dPresetUuid ? $buttonPresets->get($dPresetUuid) : null;
                                    $dHasPreset = !empty($dPreset) && !empty($dPreset->payload);
                                    $dBtnId = 'btn-purchase-direct';
                                @endphp
                                @if($dHasPreset)
                                    <style>
                                        .btn-preset-{{ $dBtnId }} {
                                            background-color: {{ data_get($dPreset->payload, 'bg_color', '#1e293b') }} !important;
                                            color: {{ data_get($dPreset->payload, 'text_color', '#fff') }} !important;
                                            border-color: {{ data_get($dPreset->payload, 'border_color', '#1e293b') }} !important;
                                            border-style: solid;
                                            border-width: 1px;
                                            border-radius: {{ data_get($dPreset->payload, 'border_radius', '6px') }} !important;
                                            padding-top: {{ data_get($dPreset->payload, 'inner_padding_top', 12) }}px !important;
                                            padding-bottom: {{ data_get($dPreset->payload, 'inner_padding_bottom', 12) }}px !important;
                                            padding-left: {{ data_get($dPreset->payload, 'inner_padding_left', 24) }}px !important;
                                            padding-right: {{ data_get($dPreset->payload, 'inner_padding_right', 24) }}px !important;
                                            font-size: {{ data_get($dPreset->payload, 'font_size', '0.875rem') }} !important;
                                            font-weight: {{ data_get($dPreset->payload, 'font_weight', '600') }} !important;
                                            display: inline-flex;
                                            align-items: center;
                                            justify-content: center;
                                            transition: all 0.2s ease-in-out;
                                            width: 100% !important;
                                        }
                                        .btn-preset-{{ $dBtnId }}:hover {
                                            background-color: {{ data_get($dPreset->payload, 'hover_bg_color', '#334155') }} !important;
                                            color: {{ data_get($dPreset->payload, 'hover_text_color', '#fff') }} !important;
                                            border-color: {{ data_get($dPreset->payload, 'hover_border_color', '#334155') }} !important;
                                        }
                                    </style>
                                @endif
                            @endif

                            @foreach($externalBtns as $extIdx => $extBtn)
                                @php
                                    $extPresetUuid = data_get($extBtn, 'preset_uuid');
                                    $extPreset = $extPresetUuid ? $buttonPresets->get($extPresetUuid) : null;
                                    $extHasPreset = !empty($extPreset) && !empty($extPreset->payload);
                                    $extBtnId = 'btn-purchase-ext-' . $extIdx;
                                @endphp
                                @if($extHasPreset)
                                    <style>
                                        .btn-preset-{{ $extBtnId }} {
                                            background-color: {{ data_get($extPreset->payload, 'bg_color', '#1e293b') }} !important;
                                            color: {{ data_get($extPreset->payload, 'text_color', '#fff') }} !important;
                                            border-color: {{ data_get($extPreset->payload, 'border_color', '#1e293b') }} !important;
                                            border-style: solid;
                                            border-width: 1px;
                                            border-radius: {{ data_get($extPreset->payload, 'border_radius', '6px') }} !important;
                                            padding-top: {{ data_get($extPreset->payload, 'inner_padding_top', 12) }}px !important;
                                            padding-bottom: {{ data_get($extPreset->payload, 'inner_padding_bottom', 12) }}px !important;
                                            padding-left: {{ data_get($extPreset->payload, 'inner_padding_left', 24) }}px !important;
                                            padding-right: {{ data_get($extPreset->payload, 'inner_padding_right', 24) }}px !important;
                                            font-size: {{ data_get($extPreset->payload, 'font_size', '0.875rem') }} !important;
                                            font-weight: {{ data_get($extPreset->payload, 'font_weight', '600') }} !important;
                                            display: inline-flex;
                                            align-items: center;
                                            justify-content: center;
                                            transition: all 0.2s ease-in-out;
                                            width: 100% !important;
                                        }
                                        .btn-preset-{{ $extBtnId }}:hover {
                                            background-color: {{ data_get($extPreset->payload, 'hover_bg_color', '#334155') }} !important;
                                            color: {{ data_get($extPreset->payload, 'hover_text_color', '#fff') }} !important;
                                            border-color: {{ data_get($extPreset->payload, 'hover_border_color', '#334155') }} !important;
                                        }
                                    </style>
                                @endif
                            @endforeach
                        @endif

                        @php
                            $directBtnObj = isset($directBtn) ? $directBtn : null;
                            $defaultLabel = $directBtnObj ? data_get($directBtnObj, 'label', _l('Add to Cart')) : _l('Add to Cart');
                            $btnStatus = $product->stock_status ?? '';
                            $btnDisabled = in_array($btnStatus, ['out_of_stock', 'disabled_add_to_cart']);
                            $btnText = match($btnStatus) {
                                'disabled_add_to_cart' => _l('Sales Paused'),
                                'out_of_stock' => _l('Out of Stock'),
                                default => $defaultLabel,
                            };

                            $buttonProps = [
                                'disabled' => $btnDisabled,
                                'label' => $btnText,
                                'stock_status' => $btnStatus,
                            ];
                            if (class_exists('\App\Facades\Hook')) {
                                $buttonProps = \App\Facades\Hook::applyFilters('product.add_to_cart_button_props', $buttonProps, $product);
                            }
                            $finalDisabled = !empty($buttonProps['disabled']);
                            $finalLabel = $buttonProps['label'] ?? $btnText;
                        @endphp

                        <div class="single-product-actions product-purchase-grid" style="width: 100%; max-width: 340px; justify-content: flex-start; align-items: stretch;">
                            @if($activeButtons->isNotEmpty())
                                @if($directBtn)
                                    <button class="{{ $dHasPreset ? 'btn single-product-add-to-cart btn-preset-'.$dBtnId : 'btn btn-primary single-product-add-to-cart' }}" 
                                             style="width: 100%; {{ $finalDisabled ? 'opacity: 0.6; cursor: not-allowed;' : '' }}"
                                             {{ $finalDisabled ? 'disabled' : 'onclick=handleAddToCart();' }}>
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px; display: inline-block; vertical-align: middle;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        {{ $finalLabel }}
                                    </button>
                                @endif

                                @foreach($externalBtns as $extIdx => $extBtn)
                                    @php
                                        $extBtnId = 'btn-purchase-ext-' . $extIdx;
                                    @endphp
                                    <a href="{{ data_get($extBtn, 'url') }}" target="_blank" class="{{ $extHasPreset ? 'btn single-product-add-to-cart btn-preset-'.$extBtnId : 'btn btn-primary single-product-add-to-cart' }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; width: 100%;">
                                        @if(data_get($extBtn, 'platform') === 'shopee')
                                            <i class="fas fa-shopping-bag"></i>
                                        @elseif(data_get($extBtn, 'platform') === 'lazada')
                                            <i class="fas fa-heart"></i>
                                        @elseif(data_get($extBtn, 'platform') === 'tiki')
                                            <i class="fas fa-shopping-cart"></i>
                                        @else
                                            <i class="fas fa-external-link-alt"></i>
                                        @endif
                                        <span>{{ data_get($extBtn, 'label') }}</span>
                                        @if(data_get($extBtn, 'price'))
                                            <span class="ext-btn-price" style="margin-left: auto; opacity: 0.85; font-size: 0.85rem;">({{ format_currency(data_get($extBtn, 'price')) }})</span>
                                        @endif
                                    </a>
                                @endforeach
                            @else
                                <button class="btn btn-primary single-product-add-to-cart" 
                                        style="width: 100%; {{ $finalDisabled ? 'opacity: 0.6; cursor: not-allowed;' : '' }}"
                                        {{ $finalDisabled ? 'disabled' : 'onclick=handleAddToCart();' }}>
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px; display: inline-block; vertical-align: middle;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ $finalLabel }}
                                </button>
                            @endif
                        </div>

                        @php
                            $projectHubFreeRelease = null;
                            $freeDownloadRequiresAuth = true;
                            $freeDownloadUrl = null;

                            if (class_exists('\Modules\Polyx\ProjectHub\Models\Project')) {
                                $productIds = [$product->id];
                                if (!empty($product->translation_group_id)) {
                                    $groupProductIds = \App\Models\Product::withoutGlobalScope('locale')
                                        ->where('translation_group_id', $product->translation_group_id)
                                        ->pluck('id')
                                        ->toArray();
                                    $productIds = array_merge($productIds, $groupProductIds);
                                }
                                $slugProductIds = \App\Models\Product::withoutGlobalScope('locale')
                                    ->where('slug', $product->slug)
                                    ->pluck('id')
                                    ->toArray();
                                $productIds = array_unique(array_filter(array_merge($productIds, $slugProductIds)));

                                $projectHubProject = \Modules\Polyx\ProjectHub\Models\Project::whereHas('products', function ($q) use ($productIds) {
                                    $q->whereIn('products.id', $productIds);
                                })->first();
                                
                                if ($projectHubProject) {
                                    $projectHubFreeRelease = $projectHubProject->releases()
                                        ->where('status', 'published')
                                        ->whereNotNull('free_download_url')
                                        ->where('free_download_url', '!=', '')
                                        ->orderByDesc('released_at')
                                        ->orderByDesc('id')
                                        ->first();
                                        
                                    $freeDownloadRequiresAuth = (data_get($projectHubProject->settings, 'free_download_requires_auth', true) !== false);
                                    
                                    if ($projectHubFreeRelease) {
                                        $freeDownloadUrl = $projectHubFreeRelease->free_download_url;
                                        if ($freeDownloadUrl && (str_starts_with($freeDownloadUrl, 'http://') || str_starts_with($freeDownloadUrl, 'https://'))) {
                                            $parsedUrl = parse_url($freeDownloadUrl);
                                            if (isset($parsedUrl['path']) && str_starts_with($parsedUrl['path'], '/storage/')) {
                                                $freeDownloadUrl = $parsedUrl['path'];
                                            }
                                        }
                                    }
                                }
                            }
                        @endphp

                        @if($projectHubFreeRelease)
                             <!-- 1. Download Button (Visible when logged in OR when auth is not required) -->
                             <div id="free-download-button-wrapper" style="width: 100%; max-width: 340px; margin-top: 12px; margin-bottom: 8px; display: {{ (!$freeDownloadRequiresAuth || auth()->check()) ? 'block' : 'none' }};">
                                 <a id="free-download-link"
                                    href="{{ $freeDownloadRequiresAuth ? route('projects.download-free', $projectHubFreeRelease->id) : $freeDownloadUrl }}" 
                                    class="btn" 
                                    style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 12px; font-weight: 600; font-size: 0.875rem; border-radius: 6px; background-color: #10b981; color: #fff; transition: all 0.2s ease; border: 1px solid #10b981; text-decoration: none;"
                                    onmouseover="this.style.backgroundColor='#059669'; this.style.borderColor='#059669'"
                                    onmouseout="this.style.backgroundColor='#10b981'; this.style.borderColor='#10b981'"
                                    {{ !$freeDownloadRequiresAuth ? 'download' : '' }}>
                                     <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 8px; display: inline-block; vertical-align: middle;">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                     </svg>
                                     {{ _l('Download Free Version') }}
                                 </a>
                             </div>

                             <!-- 2. Register/Login Button (Visible only when auth is required AND user is guest) -->
                             @if($freeDownloadRequiresAuth && !auth()->check())
                                 <div id="free-login-button-wrapper" style="width: 100%; max-width: 340px; margin-top: 12px; margin-bottom: 8px; display: block;">
                                     <a href="{{ route('register') }}?redirect={{ urlencode(request()->fullUrl()) }}" 
                                        class="btn btn-secondary" 
                                        style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 12px; font-weight: 600; font-size: 0.875rem; border-radius: 6px; text-decoration: none; border-style: solid; border-width: 1px;">
                                         <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 8px; display: inline-block; vertical-align: middle;">
                                             <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                         </svg>
                                         {{ _l('Register / Login to Download Free') }}
                                     </a>
                                 </div>
                             @endif

                             <!-- 3. Client-side authentication check script (covers both web session & Sanctum localstorage) -->
                             <script>
                                 (function() {
                                     function checkClientAuth() {
                                         const hasAuthToken = !!localStorage.getItem('auth_token');
                                         const downloadBtn = document.getElementById('free-download-button-wrapper');
                                         const loginBtn = document.getElementById('free-login-button-wrapper');
                                         
                                         if (hasAuthToken) {
                                             if (loginBtn) loginBtn.style.display = 'none';
                                             if (downloadBtn) {
                                                 downloadBtn.style.display = 'block';
                                                 // Update link for admin to bypass web middleware if not web-session authenticated
                                                 const downloadLink = document.getElementById('free-download-link');
                                                 if (downloadLink) {
                                                     downloadLink.setAttribute('href', '{{ $freeDownloadUrl }}');
                                                     downloadLink.setAttribute('download', '');
                                                 }
                                             }
                                         }
                                     }
                                     
                                     // Check immediately and on DOMContentLoaded
                                     checkClientAuth();
                                     document.addEventListener('DOMContentLoaded', checkClientAuth);
                                     
                                     // Listen for Inertia navigation success to re-check
                                     document.addEventListener('inertia:success', checkClientAuth);
                                 })();
                             </script>
                        @endif

                        <div class="single-product-meta">
                            @if(!empty($product->sku))
                                <div class="single-product-meta-row">
                                    <span class="single-product-meta-label">{{ _l('SKU') }}</span>
                                    <span class="single-product-meta-value">{{ $product->sku }}</span>
                                </div>
                            @endif
                            @if($product->categories && $product->categories->count() > 0)
                                <div class="single-product-meta-row">
                                    <span class="single-product-meta-label">{{ _l('Category') }}</span>
                                    <div>
                                        @foreach($product->categories as $category)
                                            <a href="{{ $category->frontend_url }}" class="single-product-meta-link">
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

            <!-- Product Details Tabs -->
            @php
                $descriptionHtml = (string) ($product->description_html ?? '');
                $hasDescriptionTab = trim(strip_tags($descriptionHtml)) !== '';
                $faqItems = collect($productFaqItems ?? [])->values();
                $customTabs = collect($productCustomTabs ?? [])->values();
                $hasFaqTab = !empty($hasProductFaqTab) && $faqItems->isNotEmpty();
                $hasCustomTabs = !empty($hasProductCustomTabs) && $customTabs->isNotEmpty();
                $tabs = collect();
                $customTabPanels = $customTabs->map(function ($tab) {
                    $title = (string) ($tab['title'] ?? '');
                    $slug = \Illuminate\Support\Str::slug($title);
                    return [
                        'source_id' => (string) ($tab['id'] ?? ''),
                        'title' => $title,
                        'content' => (string) ($tab['content'] ?? ''),
                        'id' => $slug !== '' ? $slug : null,
                    ];
                })->filter(fn ($tab) => !empty($tab['id']) && !empty($tab['title']))->values();

                $privateContent = data_get($product->settings, 'private_content');
                $hasPrivateContent = !empty($privateContent);
                $hasActiveAccess = false;
                if (auth()->check()) {
                    $hasActiveAccess = \App\Models\Ecommerce\UserSubscription::where('user_id', auth()->id())
                        ->where('product_id', $product->id)
                        ->where('status', 'active')
                        ->exists();
                }

                if ($hasDescriptionTab) {
                    $tabs->push(['id' => 'description', 'title' => _l('Description'), 'type' => 'description']);
                }
                if ($hasPrivateContent) {
                    $tabs->push(['id' => 'premium-content', 'title' => _l('Premium Content'), 'type' => 'premium-content']);
                }
                if ($hasFaqTab) {
                    $tabs->push(['id' => 'faqs', 'title' => _l("FAQ's"), 'type' => 'faq']);
                }
                if (!empty($projectReleases) && ($projectReleases->isNotEmpty() || $projectFeatures->isNotEmpty())) {
                    $tabs->push(['id' => 'updates-roadmap', 'title' => _l('Updates & Roadmap'), 'type' => 'updates-roadmap']);
                }
                foreach ($customTabPanels as $tab) {
                    $tabs->push([
                        'id' => $tab['id'],
                        'source_id' => $tab['source_id'] ?? $tab['id'],
                        'title' => (string) ($tab['title'] ?? ''),
                        'type' => 'custom',
                        'content' => (string) ($tab['content'] ?? ''),
                    ]);
                }

                $tabs->push(['id' => 'rate', 'title' => _l('Ratings'), 'type' => 'rate']);
                $tabs->push(['id' => 'review', 'title' => _l('Comments'), 'type' => 'review']);

                $tabs = $tabs->filter(fn ($tab) => !empty($tab['title']))->values();

                
                // Order tabs based on settings
                $tabOrder = data_get($product->settings, 'tabs.tab_order', []);
                if (!empty($tabOrder) && is_array($tabOrder)) {
                    $tabs = $tabs->sortBy(function ($tab) use ($tabOrder) {
                        $matchId = $tab['source_id'] ?? $tab['id'];
                        $index = array_search($matchId, $tabOrder);
                        return $index !== false ? $index : 999;
                    })->values();
                }

                $configuredDefaultTabId = (string) ($defaultProductCustomTabId ?? '');
                $defaultTabId = $tabs->first()['id'] ?? 'description';
                if ($configuredDefaultTabId !== '') {
                    $matchedCustom = $customTabPanels->first(function ($tab) use ($configuredDefaultTabId) {
                        return (string) $tab['source_id'] === $configuredDefaultTabId
                            || (string) $tab['id'] === $configuredDefaultTabId;
                    });
                    if ($configuredDefaultTabId === 'description' && $tabs->contains(fn ($tab) => $tab['id'] === 'description')) {
                        $defaultTabId = 'description';
                    } elseif ($configuredDefaultTabId === 'faqs' && $tabs->contains(fn ($tab) => $tab['id'] === 'faqs')) {
                        $defaultTabId = 'faqs';
                    } elseif (!empty($matchedCustom['id']) && $tabs->contains(fn ($tab) => $tab['id'] === $matchedCustom['id'])) {
                        $defaultTabId = $matchedCustom['id'];
                    }
                }
            @endphp
            @if($tabs->isNotEmpty())
                <div class="single-product-details">
                    <div class="single-product-tab-nav" data-product-tabs data-default-tab="{{ $defaultTabId }}">
                        @foreach($tabs as $tab)
                            <a href="#" data-tab-id="{{ $tab['id'] }}" class="single-product-tab-link">{{ $tab['title'] }}</a>
                        @endforeach
                    </div>

                    <div class="single-product-tab-content">
                        @if($hasDescriptionTab)
                            <div id="description" class="single-product-tab-panel">
                                <div class="prose">
                                     {!! filter_content_lazy_images(\App\Facades\Hook::applyFilters('post.content.render', render_dynamic_blocks($descriptionHtml), $product)) !!}
                                </div>
                            </div>
                        @endif

                        @if($hasPrivateContent)
                            <div id="premium-content" class="single-product-tab-panel">
                                @if($hasActiveAccess)
                                    <div class="prose bg-emerald-50/10 dark:bg-emerald-950/10 p-6 rounded-xl border border-emerald-500/20" style="text-align: left;">
                                        <div style="display: flex; align-items: center; gap: 8px; color: #10b981; margin-bottom: 16px; font-weight: 750; font-size: 1.1rem;">
                                            <i class="fas fa-unlock"></i>
                                            <span>{{ _l('Unlocked Premium Content') }}</span>
                                        </div>
                                        {!! \Illuminate\Support\Str::markdown($privateContent) !!}
                                    </div>
                                @else
                                    <div style="text-align: center; padding: 48px 24px; border: 2px dashed #cbd5e1; border-radius: 16px; background: rgba(248, 250, 252, 0.5); max-width: 500px; margin: 20px auto;">
                                        <div style="color: #94a3b8; font-size: 2.5rem; margin-bottom: 16px;">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                        <h4 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                                            {{ _l('Premium Content Locked') }}
                                        </h4>
                                        <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 24px; line-height: 1.5;">
                                            {{ _l('This content is reserved for active subscribers. Please purchase a plan or log in to unlock.') }}
                                        </p>
                                        @if(!auth()->check())
                                            @include('partials.external-auth-prompt', [
                                                'message' => _l('Please log in to unlock premium content.'),
                                                'product' => $product ?? null,
                                            ])
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($hasFaqTab)
                            <div id="faqs" class="single-product-tab-panel">
                                @php
                                    $faqAccordionItems = $faqItems->map(function ($faq) {
                                        return [
                                            'title' => (string) ($faq['question'] ?? ''),
                                            'description' => render_dynamic_blocks(htmlspecialchars_decode((string) ($faq['answer'] ?? ''))),
                                            'open' => !empty($faq['open']),
                                            'is_html' => true,
                                        ];
                                    })->filter(fn ($item) => $item['title'] !== '' || $item['description'] !== '')->values()->all();
                                @endphp
                                @if(!empty($faqAccordionItems))
                                    <x-accordion :items="$faqAccordionItems" style="standard" />
                                @endif
                            </div>
                        @endif
                        @if(!empty($projectReleases) && ($projectReleases->isNotEmpty() || $projectFeatures->isNotEmpty()))
                            <div id="updates-roadmap" class="single-product-tab-panel">
                                <style>
                                    #updates-roadmap {
                                        --roadmap-text-primary: #0f172a;
                                        --roadmap-text-secondary: #1e293b;
                                        --roadmap-text-muted: #475569;
                                        --roadmap-text-date: #64748b;
                                        --roadmap-line: #e2e8f0;
                                        --roadmap-dot-bg: #fff;
                                        --roadmap-box-border: #cbd5e1;
                                        --roadmap-box-bg: #f8fafc;
                                        --roadmap-elapsed-color: #059669;
                                    }
                                    .dark #updates-roadmap,
                                    html.dark #updates-roadmap,
                                    body.dark #updates-roadmap {
                                        --roadmap-text-primary: #f8fafc;
                                        --roadmap-text-secondary: #e2e8f0;
                                        --roadmap-text-muted: #cbd5e1;
                                        --roadmap-text-date: #94a3b8;
                                        --roadmap-line: #334155;
                                        --roadmap-dot-bg: #1e293b;
                                        --roadmap-box-border: #475569;
                                        --roadmap-box-bg: rgba(30, 41, 59, 0.3);
                                        --roadmap-elapsed-color: #34d399;
                                    }
                                </style>
                                <div style="display: flex; flex-direction: column; gap: 40px; text-align: left; margin-top: 16px;">
                                    
                                    <!-- Section: What We're Working On -->
                                    <div>
                                        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; color: var(--roadmap-text-primary); border: 0; padding: 0;">
                                            {{ __('What We\'re Working On') }}
                                        </h3>
                                        @if($projectFeatures->isEmpty())
                                            <p style="color: var(--roadmap-text-date); font-style: italic;">{{ __('No feature updates scheduled currently.') }}</p>
                                        @else
                                            <div style="display: flex; flex-direction: column; gap: 24px; border-left: 2px solid var(--roadmap-line); padding-left: 20px; margin-left: 10px;">
                                                @foreach($projectFeatures as $feature)
                                                    @php
                                                        $dateStr = $feature['added_at'] ? \Carbon\Carbon::parse($feature['added_at'])->format('M d, Y') : '';
                                                    @endphp
                                                    <div class="project-feature-timeline-item" style="position: relative;" data-added-at="{{ $feature['added_at'] }}">
                                                        <div style="position: absolute; left: -27px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: {{ $feature['status'] === 'in_progress' ? '#ea580c' : '#0284c7' }}; border: 2px solid var(--roadmap-dot-bg); box-shadow: 0 0 0 2px var(--roadmap-line);"></div>
                                                        
                                                        <div style="display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                                                            <span style="font-size: 1rem; font-weight: 500; color: var(--roadmap-text-secondary); line-height: 1.5;">
                                                                {{ $feature['title'] }}
                                                                @if($dateStr)
                                                                    <span style="font-size: 0.85rem; color: var(--roadmap-text-date); font-weight: normal; margin-left: 6px;">({{ $dateStr }})</span>
                                                                @endif
                                                            </span>
                                                            @if($feature['status'] === 'in_progress')
                                                                <span style="font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 9999px; background: #ffedd5; color: #ea580c; white-space: nowrap;">
                                                                    {{ __('In Progress') }}
                                                                </span>
                                                            @else
                                                                <span style="font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 9999px; background: #e0f2fe; color: #0284c7; white-space: nowrap;">
                                                                    {{ __('Planned') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($feature['description'])
                                                            <p style="margin: 0; font-size: 0.95rem; color: var(--roadmap-text-muted); line-height: 1.6;">
                                                                {{ $feature['description'] }}
                                                            </p>
                                                        @endif
                                                        @if($feature['added_at'])
                                                            <div class="feature-duration-bar-container" style="margin-top: 8px; display: flex; align-items: center; gap: 12px;">
                                                                <span style="font-size: 0.8rem; color: var(--roadmap-text-date); font-weight: 500;">{{ $dateStr }}</span>
                                                                <div style="flex: 1; max-width: 200px; height: 6px; background: var(--roadmap-line); border-radius: 9999px; position: relative;">
                                                                    <div class="feature-duration-bar" style="position: absolute; left: 0; top: 0; height: 100%; width: 0%; background: linear-gradient(90deg, #10b981, #059669); border-radius: 9999px; transition: width 0.6s ease;"></div>
                                                                </div>
                                                                <span class="elapsed-days-text" style="font-size: 0.8rem; color: var(--roadmap-elapsed-color, #059669); font-weight: 600;">Calculating...</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Section: Recent Releases -->
                                    <div>
                                        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; color: var(--roadmap-text-primary); border: 0; padding: 0;">
                                            {{ __('Recent Releases') }}
                                        </h3>
                                        @if($projectReleases->isEmpty())
                                            <p style="color: var(--roadmap-text-date); font-style: italic;">{{ __('No release logs available.') }}</p>
                                        @else
                                            <div style="display: flex; flex-direction: column; gap: 24px; border-left: 2px solid var(--roadmap-line); padding-left: 20px; margin-left: 10px;">
                                                @foreach($projectReleases as $release)
                                                    <div style="position: relative;">
                                                        <div style="position: absolute; left: -27px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: #6366f1; border: 2px solid var(--roadmap-dot-bg); box-shadow: 0 0 0 2px var(--roadmap-line);"></div>
                                                        
                                                        <div style="display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 6px;">
                                                            <strong class="projecthub-release-version" data-project-id="{{ $project->id ?? '' }}" data-release-id="{{ $release['id'] ?? '' }}" style="font-size: 1.05rem; font-weight: 800; color: var(--roadmap-text-secondary);">
                                                                v{{ $release['version'] }}
                                                            </strong>
                                                            @php
                                                                $cleanTitle = trim(preg_replace('/^(version|v)\s*/i', '', strtolower($release['title'] ?? '')));
                                                                $cleanVer = trim(preg_replace('/^(version|v)\s*/i', '', strtolower($release['version'] ?? '')));
                                                                $showTitle = filled($release['title']) && $cleanTitle !== $cleanVer && $cleanTitle !== '';
                                                            @endphp
                                                            @if($showTitle)
                                                                <span style="font-size: 0.95rem; font-weight: 700; color: var(--roadmap-text-muted);">
                                                                    — {{ $release['title'] }}
                                                                </span>
                                                            @endif
                                                            <span style="font-size: 0.825rem; color: #94a3b8; font-weight: 500;">
                                                                ({{ $release['released_at'] }})
                                                            </span>
                                                        </div>
                                                         @if($release['summary'])
                                                             <div style="font-size: 0.95rem; line-height: 1.6; color: var(--roadmap-text-muted); margin-bottom: 8px;">
                                                                 @php
                                                                     $lines = explode("\n", $release['summary']);
                                                                     $firstHeader = true;
                                                                 @endphp
                                                                 @foreach($lines as $line)
                                                                     @php
                                                                         $trimmed = trim($line);
                                                                         if ($trimmed === '') {
                                                                             continue;
                                                                         }
                                                                         $upper = strtoupper($trimmed);
                                                                         $isHeader = in_array($upper, ['NEW', 'CHANGED', 'FIXED', 'OTHER']);
                                                                     @endphp
                                                                     @if($isHeader)
                                                                         <div style="font-weight: 700; color: var(--roadmap-text-secondary); margin-top: {{ $firstHeader ? '0' : '14px' }}; margin-bottom: 6px; font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase;">
                                                                             {{ $upper }}
                                                                         </div>
                                                                         @php $firstHeader = false; @endphp
                                                                     @elseif(str_starts_with($trimmed, '-'))
                                                                         @php
                                                                             $cleanText = ltrim(substr($trimmed, 1));
                                                                         @endphp
                                                                         <div style="margin-bottom: 5px; padding-left: 16px; text-indent: -16px; line-height: 1.65; color: var(--roadmap-text-muted);">
                                                                             <span style="font-weight: 500; margin-right: 4px; color: #64748b;">-</span>{{ $cleanText }}
                                                                         </div>
                                                                     @else
                                                                         <div style="margin-bottom: 5px; line-height: 1.65; color: var(--roadmap-text-muted);">
                                                                             {{ $trimmed }}
                                                                         </div>
                                                                     @endif
                                                                 @endforeach
                                                             </div>
                                                         @endif
                                                        @if($release['release_notes_html'])
                                                            <div class="changelog-prose text-left" style="font-size: 0.9rem; color: var(--roadmap-text-date); line-height: 1.6; text-align: left;">
                                                                {!! $release['release_notes_html'] !!}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                </div>

                                @if($projectUrl)
                                    <div style="margin-top: 40px; padding: 20px; border-radius: 12px; border: 1px dashed var(--roadmap-box-border); background: var(--roadmap-box-bg); text-align: center;">
                                        <p style="margin: 0 0 10px; font-size: 0.95rem; color: var(--roadmap-text-muted); font-weight: 600;">
                                            {{ __('Looking for full roadmap, documentation, or interactive release logs?') }}
                                        </p>
                                        <a href="{{ $projectUrl }}#changelogs" target="_blank" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; font-size: 0.9rem; text-decoration: none;">
                                            {{ __('Explore Our Project Portal') }}
                                            <i class="fas fa-external-link-alt" style="font-size: 0.8rem;"></i>
                                        </a>
                                    </div>
                                @endif

                                <script>
                                 document.addEventListener('DOMContentLoaded', () => {
                                     // Render edit release icons if logged in as admin in SPA (checking localStorage)
                                     const authToken = localStorage.getItem('auth_token');
                                     if (authToken) {
                                         document.querySelectorAll('.projecthub-release-version').forEach((el) => {
                                             const projectId = el.getAttribute('data-project-id');
                                             const releaseId = el.getAttribute('data-release-id');
                                             if (projectId) {
                                                 const editLink = document.createElement('a');
                                                 editLink.href = `/admin/project-hub/${projectId}/edit?tab=releases` + (releaseId ? `#release-${releaseId}` : '');
                                                 editLink.target = '_blank';
                                                 editLink.title = '{{ __("Edit Release") }}';
                                                 editLink.style.cssText = 'color: #6366f1; display: inline-flex; align-items: center; text-decoration: none; margin-left: 6px; vertical-align: middle;';
                                                 editLink.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>`;
                                                 el.after(editLink);
                                             }
                                         });
                                     }

                                     document.querySelectorAll('.project-feature-timeline-item').forEach((item) => {
                                         const addedStr = item.getAttribute('data-added-at');
                                         if (!addedStr) return;
                                         const addedDate = new Date(addedStr);
                                         if (isNaN(addedDate.getTime())) return;
                                         
                                         const today = new Date();
                                         addedDate.setHours(0,0,0,0);
                                         today.setHours(0,0,0,0);
                                         
                                         const diffTime = today - addedDate;
                                         const diffDays = Math.max(0, Math.floor(diffTime / (1000 * 60 * 60 * 24)));
                                         
                                         const daysTextEl = item.querySelector('.elapsed-days-text');
                                         if (daysTextEl) {
                                             daysTextEl.textContent = `${diffDays} days`;
                                         }
                                         
                                         const barEl = item.querySelector('.feature-duration-bar');
                                         if (barEl) {
                                             const percentage = Math.min(100, Math.max(8, (diffDays / 60) * 100));
                                             setTimeout(() => {
                                                 barEl.style.width = `${percentage}%`;
                                             }, 100);
                                         }
                                     });
                                 });
                                 </script>
                            </div>
                        @endif


                        @foreach($customTabPanels as $tab)
                            @php
                                $tabId = (string) ($tab['id'] ?? '');
                                $tabTitle = (string) ($tab['title'] ?? '');
                                $tabContent = (string) ($tab['content'] ?? '');
                            @endphp
                            @if($tabTitle !== '' && $tabContent !== '')
                                <div id="{{ $tabId }}" class="single-product-tab-panel">
                                    <div class="prose">{!! $tabContent !!}</div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Rate Tab Panel --}}
                        <div id="rate" class="single-product-tab-panel">
                            @php
                                $externalRating = (float) data_get($product->settings, 'external_rating', 0);
                                $externalRatingCount = (int) data_get($product->settings, 'external_rating_count', 0);
                                $localRatingColumn = (float) $product->getRawOriginal('avg_rating', 0);
                                $localRatingCountColumn = (int) $product->getRawOriginal('review_count', 0);
                                
                                $mergedAvg = $product->avg_rating;
                                $mergedCount = $product->review_count;
                            @endphp

                            @if(!empty($product->settings['envato_item_id']))
                                @php
                                    $envatoItemId = (string) $product->settings['envato_item_id'];
                                    $envatoUrl = \Modules\Polyx\MarketIntegration\Services\MarketService::getEnvatoReviewsUrl($envatoItemId);
                                @endphp
                                <div class="envato-sync-notice" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 18px; background: rgba(130, 180, 64, 0.06); border: 1px solid rgba(130, 180, 64, 0.15); border-radius: 12px; margin-top: 16px; margin-bottom: 8px; flex-wrap: wrap; text-align: left;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-info-circle" style="color: #82b440; font-size: 1.1rem; flex-shrink: 0;"></i>
                                        <span style="font-size: 0.9rem; color: #475569; line-height: 1.4;" class="dark:text-slate-300">
                                            {{ _l('This product is synced with Envato Market. You can view official customer feedback or write a review directly on Envato.') }}
                                        </span>
                                    </div>
                                    <a href="{{ $envatoUrl }}" target="_blank" rel="nofollow" class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 6px; background: #82b440; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#72a035'" onmouseout="this.style.background='#82b440'">
                                        <i class="fas fa-shopping-bag"></i>
                                        {{ _l('Rate & Review on Envato') }}
                                    </a>
                                </div>
                            @endif
                            
                            <div style="display: grid; grid-template-columns: 1fr; gap: 32px; margin-top: 16px;">
                                <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
                                    {{-- Left Column: Big Stats --}}
                                    <div class="product-rating-avg-card">
                                        <h4 class="product-rating-avg-title">{{ _l('Average Rating') }}</h4>
                                        <div class="product-rating-avg-score">{{ number_format($mergedAvg, 1) }}</div>
                                        <div class="product-rating-avg-stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= round($mergedAvg))
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star text-slate-300 dark:text-slate-600"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <p class="product-rating-avg-count">{{ _l('Based on') }} {{ $mergedCount }} {{ _l('ratings') }}</p>
                                    </div>

                                    {{-- Right Column: Merged Breakdown --}}
                                    <div style="display: flex; flex-direction: column; gap: 16px; justify-content: center;">
                                        <h4 class="product-rating-breakdown-title">
                                            {{ _l('Ratings Source Breakdown') }}
                                        </h4>
                                        
                                        {{-- Envato Market --}}
                                        @if($externalRatingCount > 0)
                                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.9rem;">
                                                <span class="product-rating-breakdown-label">
                                                    <i class="fas fa-shopping-bag" style="color: #82b440;"></i>
                                                    {{ _l('Envato Market') }}
                                                </span>
                                                <span class="product-rating-breakdown-value">
                                                    {{ number_format($externalRating, 1) }} ★ 
                                                    <span class="product-rating-breakdown-sub">({{ $externalRatingCount }} {{ _l('ratings') }})</span>
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Local Site --}}
                                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.9rem;">
                                            <span class="product-rating-breakdown-label">
                                                <i class="fas fa-globe" style="color: var(--primary-color, #3b82f6);"></i>
                                                {{ _l('Local Website') }}
                                            </span>
                                            <span class="product-rating-breakdown-value">
                                                {{ number_format($localRatingColumn, 1) }} ★ 
                                                <span class="product-rating-breakdown-sub">({{ $localRatingCountColumn }} {{ _l('reviews') }})</span>
                                            </span>
                                        </div>
                                        
                                        {{-- Total Merged --}}
                                        <div class="product-rating-breakdown-total-row">
                                            <span class="product-rating-breakdown-total-label">{{ _l('Merged Total') }}</span>
                                            <span class="product-rating-breakdown-total-val">
                                                {{ number_format($mergedAvg, 1) }} ★ 
                                                <span class="product-rating-breakdown-sub">({{ $mergedCount }} {{ _l('ratings') }})</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Review Tab Panel --}}
                        <div id="review" class="single-product-tab-panel">
                            @if(!empty($product->settings['envato_item_id']))
                                @php
                                    $envatoItemId = (string) $product->settings['envato_item_id'];
                                    $envatoUrl = \Modules\Polyx\MarketIntegration\Services\MarketService::getEnvatoCommentsUrl($envatoItemId);
                                @endphp
                                <div class="envato-sync-notice" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 18px; background: rgba(130, 180, 64, 0.06); border: 1px solid rgba(130, 180, 64, 0.15); border-radius: 12px; margin-top: 16px; margin-bottom: 24px; flex-wrap: wrap; text-align: left;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-info-circle" style="color: #82b440; font-size: 1.1rem; flex-shrink: 0;"></i>
                                        <span style="font-size: 0.9rem; color: #475569; line-height: 1.4;" class="dark:text-slate-300">
                                            {{ _l('This product is synced with Envato Market. You can view official customer discussions or leave a comment directly on Envato.') }}
                                        </span>
                                    </div>
                                    <a href="{{ $envatoUrl }}" target="_blank" rel="nofollow" class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 6px; background: #82b440; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#72a035'" onmouseout="this.style.background='#82b440'">
                                        <i class="fas fa-comments"></i>
                                        {{ _l('Comments on Envato') }}
                                    </a>
                                </div>
                            @endif
                            
                            {{-- Reviews List Container --}}
                            <div id="local-reviews-loader" style="text-align: center; padding: 24px 0;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem; color: var(--primary-color, #3b82f6);"></i>
                                <p style="font-size: 0.85rem; color: #64748b; margin-top: 8px;">{{ _l('Loading reviews...') }}</p>
                            </div>
                            
                            <div id="local-reviews-list" style="display: none; flex-direction: column; gap: 20px;"></div>
                            
                            {{-- Pagination Container --}}
                            <div id="local-reviews-pagination" style="display: none; margin-top: 24px; justify-content: center; gap: 8px;"></div>

                            {{-- Write Review / Comment Form --}}
                            <div id="write-review-section" style="margin-top: 40px; border-top: 1px solid var(--border-color, #e2e8f0); padding-top: 32px;" class="dark:border-slate-800">
                                @if(auth()->check())
                                    @php
                                        $reviewService = app(\App\Services\Ecommerce\ReviewService::class);
                                        $hasPurchasedProduct = $reviewService->hasPurchased(auth()->user(), $product);
                                        $canSubmitReview = $reviewService->canSubmit(auth()->user(), $product);
                                    @endphp
                                    @if($canSubmitReview)
                                        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 20px; color: var(--text-color, #0f172a); border: 0; padding: 0;">
                                            {{ $hasPurchasedProduct ? _l('Rate & Review this product') : _l('Leave a Comment / Question') }}
                                        </h3>
                                        
                                        <form id="ajax-submit-review-form" class="space-y-4" style="max-width: 600px;">
                                            @if($hasPurchasedProduct)
                                                <div style="margin-bottom: 16px;">
                                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">{{ _l('Your Rating *') }}</label>
                                                    <div id="star-rating-selector" style="display: flex; gap: 8px; font-size: 1.5rem; color: #cbd5e1; cursor: pointer;">
                                                        <i class="far fa-star" data-value="1"></i>
                                                        <i class="far fa-star" data-value="2"></i>
                                                        <i class="far fa-star" data-value="3"></i>
                                                        <i class="far fa-star" data-value="4"></i>
                                                        <i class="far fa-star" data-value="5"></i>
                                                    </div>
                                                    <input type="hidden" name="rating" id="selected-rating-value" required value="">
                                                </div>
                                            @else
                                                <div style="padding: 10px 14px; background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.15); border-radius: 8px; margin-bottom: 16px; font-size: 0.85rem; color: #475569;">
                                                    <i class="fas fa-info-circle" style="color: #3b82f6; margin-right: 6px;"></i>
                                                    {{ _l('Only verified purchasers can submit a Star Rating. You can leave a comment or question below.') }}
                                                </div>
                                            @endif
                                            
                                            <div style="margin-bottom: 16px;">
                                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">{{ _l('Subject / Title') }}</label>
                                                <input type="text" name="title" placeholder="{{ $hasPurchasedProduct ? _l('e.g. Awesome extension!') : _l('e.g. Question about installation') }}" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; background: var(--geist-background, #fff); color: var(--text-color, #0f172a);" class="dark:border-slate-800 dark:bg-slate-950">
                                            </div>

                                            <div style="margin-bottom: 20px;">
                                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">{{ _l('Content') }} *</label>
                                                <textarea name="content" rows="4" required placeholder="{{ $hasPurchasedProduct ? _l('Share details of your experience with this product...') : _l('Type your question or comment here...') }}" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; background: var(--geist-background, #fff); color: var(--text-color, #0f172a);" class="dark:border-slate-800 dark:bg-slate-950"></textarea>
                                            </div>
                                            
                                            <div id="review-submit-alert" style="display: none; padding: 12px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 16px;"></div>

                                            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                                                <span>{{ $hasPurchasedProduct ? _l('Submit Rate & Review') : _l('Post Comment') }}</span>
                                                <i class="fas fa-spinner fa-spin submit-spinner" style="display: none;"></i>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @include('partials.external-auth-prompt', [
                                        'message' => _l('Please log in to leave a comment or review.'),
                                        'product' => $product ?? null,
                                    ])
                                @endif
                            </div>
                        </div>
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
<script>
(() => {
    const container = document.querySelector('[data-product-tabs]');
    if (!container) return;
    const links = Array.from(container.querySelectorAll('.single-product-tab-link'));
    const panels = Array.from(document.querySelectorAll('.single-product-tab-panel'));
    if (!links.length || !panels.length) return;

    const activate = (id) => {
        links.forEach((link) => {
            const linkId = link.getAttribute('data-tab-id') || '';
            link.classList.toggle('is-active', linkId === id);
        });
        panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.id === id);
        });
    };

    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const id = link.getAttribute('data-tab-id') || '';
            if (!id) return;
            activate(id);
            history.replaceState(null, '', `#${id}`);
        });
    });

    const fromHash = (window.location.hash || '').replace('#', '');
    const defaultId = container.getAttribute('data-default-tab') || '';
    const initId = panels.some((panel) => panel.id === fromHash)
        ? fromHash
        : (panels.some((panel) => panel.id === defaultId) ? defaultId : panels[0].id);
    activate(initId);

    document.querySelectorAll('.single-product-tab-panel .faq-item').forEach((item) => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const icon = item.querySelector('.faq-question i');
        if (!question || !answer) return;
        if (answer.classList.contains('active')) {
            item.classList.add('is-open');
            if (icon) icon.classList.remove('fa-chevron-down');
            if (icon) icon.classList.add('fa-minus');
        } else {
            if (icon) icon.classList.remove('fa-minus');
            if (icon) icon.classList.add('fa-plus');
        }
        question.addEventListener('click', () => {
            const isOpen = answer.classList.toggle('active');
            item.classList.toggle('is-open', isOpen);
            if (icon) {
                icon.classList.toggle('fa-minus', isOpen);
                icon.classList.toggle('fa-plus', !isOpen);
            }
        });
    });

    // AJAX reviews implementation
    const productId = {{ $product->id }};
    const reviewsList = document.getElementById('local-reviews-list');
    const reviewsLoader = document.getElementById('local-reviews-loader');
    const reviewsPaginationContainer = document.getElementById('local-reviews-pagination');

    const renderStars = (rating) => {
        if (!rating || rating <= 0) return '';
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += i <= rating ? '<i class="fas fa-star" style="color: #f59e0b; margin-right: 2px;"></i>' : '<i class="far fa-star" style="color: #cbd5e1; margin-right: 2px;"></i>';
        }
        return starsHtml;
    };

    const loadReviews = async (page = 1) => {
        if (!reviewsList) return;
        try {
            if (reviewsLoader) reviewsLoader.style.display = 'block';
            if (reviewsList) reviewsList.style.display = 'none';
            if (reviewsPaginationContainer) reviewsPaginationContainer.style.display = 'none';

            const response = await fetch(`/api/v1/products/${productId}/reviews?page=${page}`);
            const data = await response.json();

            if (reviewsLoader) reviewsLoader.style.display = 'none';

            const reviewsData = data.reviews.data || [];
            if (reviewsData.length === 0) {
                reviewsList.innerHTML = `<div style="text-align: center; color: #64748b; padding: 24px 0; font-size: 0.95rem;">{{ _l("No reviews found.") }}</div>`;
                reviewsList.style.display = 'flex';
                return;
            }

            reviewsList.innerHTML = reviewsData.map((review) => {
                let username = review.user?.name || (review.metadata && review.metadata.reviewer_name) || 'Anonymous';
                const avatar = review.user?.avatar || 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp';
                const createdDate = new Date(review.created_at).toLocaleDateString();
                
                const purchasedBadge = review.verified_purchase 
                    ? `<span style="font-size: 10px; font-weight: 800; color: #ffffff; background: #222222; padding: 2px 6px; border-radius: 3px; text-transform: uppercase; margin-left: 6px; display: inline-block; vertical-align: middle; line-height: 1.2;" class="dark:bg-slate-700">PURCHASED</span>` 
                    : '';

                let sourceBadge = '';
                if (review.source === 'market') {
                    const platformName = review.source_platform === 'envato' ? 'Envato Market' : review.source_platform || 'Marketplace';
                    const badgeColor = review.source_platform === 'envato' ? '#82b440' : '#3b82f6';
                    sourceBadge = `<span style="font-size: 0.7rem; font-weight: 700; color: #ffffff; background: ${badgeColor}; padding: 2px 8px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 3px;" title="Synced from ${platformName}"><i class="fas fa-shopping-bag"></i> ${platformName}</span>`;
                    
                    if (review.source_platform === 'envato' && review.metadata && review.metadata.reviewer_name) {
                        const rawUsername = review.metadata.reviewer_name;
                        username = `<a href="https://codecanyon.net/user/${rawUsername}" target="_blank" rel="nofollow" style="color: var(--text-color, #0f172a); font-weight: 700; text-decoration: none; border-bottom: 1px dotted #94a3b8; transition: border-color 0.2s;" onmouseover="this.style.borderBottomColor='#3b82f6'" onmouseout="this.style.borderBottomColor='#94a3b8'" class="dark:text-slate-200">${rawUsername}</a>`;
                    }
                }

                return `
                    <div style="padding: 16px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 12px; background: #ffffff;" class="dark:border-slate-800 dark:bg-slate-900/10">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 12px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: #f1f5f9;">
                                    <img src="${avatar}" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <h5 style="font-size: 0.9rem; font-weight: 700; color: var(--text-color, #0f172a); margin: 0; display: inline-flex; align-items: center; flex-wrap: wrap;">${username}${purchasedBadge}</h5>
                                    <span style="font-size: 0.75rem; color: #94a3b8;">${createdDate}</span>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                                <div style="font-size: 0.9rem;">${renderStars(review.rating)}</div>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    ${sourceBadge}
                                </div>
                            </div>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-color, #0f172a); margin-bottom: 6px;">${review.title || ''}</h4>
                        <p style="font-size: 0.9rem; color: #475569; line-height: 1.5; margin: 0;" class="dark:text-slate-300">${review.content || ''}</p>
                    </div>
                `;
            }).join('');
            reviewsList.style.display = 'flex';

            // Pagination links
            const lastPage = data.reviews.last_page || 1;
            if (lastPage > 1) {
                const currentPage = data.reviews.current_page || 1;
                let paginationHtml = '';
                for (let i = 1; i <= lastPage; i++) {
                    const isActive = i === currentPage;
                    paginationHtml += `
                        <button class="btn ${isActive ? 'btn-primary' : 'btn-secondary'}" data-page="${i}" style="padding: 4px 10px; font-size: 0.8rem; min-width: 28px;">${i}</button>
                    `;
                }
                reviewsPaginationContainer.innerHTML = paginationHtml;
                reviewsPaginationContainer.style.display = 'flex';

                reviewsPaginationContainer.querySelectorAll('button').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const pageNum = parseInt(btn.getAttribute('data-page'));
                        loadReviews(pageNum);
                    });
                });
            }

        } catch (error) {
            console.error('Error loading reviews:', error);
            if (reviewsLoader) reviewsLoader.style.display = 'none';
        }
    };

    // Load initial reviews when reviews tab link is clicked
    const reviewsTabLink = container.querySelector('[data-tab-id="review"]');
    if (reviewsTabLink) {
        reviewsTabLink.addEventListener('click', () => {
            loadReviews();
        });
    }

    // Check if initial hash matches review
    if (window.location.hash === '#review') {
        loadReviews();
    }

    // Star rating selection logic
    const ratingSelector = document.getElementById('star-rating-selector');
    const ratingValInput = document.getElementById('selected-rating-value');
    if (ratingSelector && ratingValInput) {
        const stars = Array.from(ratingSelector.querySelectorAll('i'));
        stars.forEach((star) => {
            star.addEventListener('click', () => {
                const value = parseInt(star.getAttribute('data-value'));
                ratingValInput.value = value;
                stars.forEach((s, idx) => {
                    if (idx < value) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                        s.style.color = '#f59e0b';
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                        s.style.color = '#cbd5e1';
                    }
                });
            });
            star.addEventListener('mouseover', () => {
                const value = parseInt(star.getAttribute('data-value'));
                stars.forEach((s, idx) => {
                    if (idx < value) {
                        s.style.color = '#fbbf24';
                    }
                });
            });
            star.addEventListener('mouseout', () => {
                const currentVal = parseInt(ratingValInput.value || '0');
                stars.forEach((s, idx) => {
                    s.style.color = idx < currentVal ? '#f59e0b' : '#cbd5e1';
                });
            });
        });
    }

    // AJAX form submission
    const ajaxForm = document.getElementById('ajax-submit-review-form');
    const alertBox = document.getElementById('review-submit-alert');
    if (ajaxForm) {
        ajaxForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = ajaxForm.querySelector('button[type="submit"]');
            const spinner = ajaxForm.querySelector('.submit-spinner');
            
            if (!ratingValInput.value) {
                if (alertBox) {
                    alertBox.textContent = '{{ _l("Please select a star rating.") }}';
                    alertBox.className = 'bg-red-50 text-red-600 border border-red-200';
                    alertBox.style.display = 'block';
                }
                return;
            }

            try {
                if (submitBtn) submitBtn.disabled = true;
                if (spinner) spinner.style.display = 'inline-block';
                if (alertBox) alertBox.style.display = 'none';

                const formData = new FormData(ajaxForm);
                const payload = {
                    rating: parseInt(formData.get('rating')),
                    title: formData.get('title'),
                    content: formData.get('content'),
                };

                const response = await fetch(`/api/v1/products/${productId}/reviews`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.style.display = 'none';

                const result = await response.json();
                if (response.ok) {
                    if (alertBox) {
                        alertBox.textContent = '{{ _l("Review submitted successfully! It is pending moderation.") }}';
                        alertBox.className = 'bg-emerald-50 text-emerald-600 border border-emerald-200';
                        alertBox.style.display = 'block';
                    }
                    ajaxForm.reset();
                    // Reset stars
                    if (ratingSelector) {
                        ratingSelector.querySelectorAll('i').forEach((s) => {
                            s.classList.remove('fas');
                            s.classList.add('far');
                            s.style.color = '#cbd5e1';
                        });
                    }
                    ratingValInput.value = '';
                } else {
                    const errorMsg = result.message || '{{ _l("Failed to submit review.") }}';
                    if (alertBox) {
                        alertBox.textContent = errorMsg;
                        alertBox.className = 'bg-red-50 text-red-600 border border-red-200';
                        alertBox.style.display = 'block';
                    }
                }
            } catch (err) {
                console.error('Error submitting review:', err);
                if (submitBtn) submitBtn.disabled = false;
                if (spinner) spinner.style.display = 'none';
                if (alertBox) {
                    alertBox.textContent = '{{ _l("Network error occurred. Please try again.") }}';
                    alertBox.className = 'bg-red-50 text-red-600 border border-red-200';
                    alertBox.style.display = 'block';
                }
            }
        });
    }
})();
</script>

<style>
    .media-action-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .media-action-btn--primary:hover {
        background: #0051cb !important;
    }
    
    /* Modal structure based on fleximyta */
    .product-preview-modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 2147483647 !important;
        display: none;
        margin: 0 !important;
        padding: 0 !important;
    }
    .product-preview-modal.is-open {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    .product-preview-modal__overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: rgba(15, 23, 42, 0.88);
        backdrop-filter: blur(4px);
        z-index: 1;
    }
    .product-preview-modal__dialog {
        position: relative;
        z-index: 2;
        margin: auto !important;
        width: min(94vw, 1400px) !important;
        max-width: min(94vw, 1400px) !important;
        height: min(90vh, 860px) !important;
        max-height: min(90vh, 860px) !important;
        background: rgba(15, 23, 42, 0.96);
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .product-preview-modal__content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 0;
        overflow: hidden;
        padding: 16px;
        flex: 1;
    }
    .screenshots-viewer {
        background: transparent !important;
        border: none !important;
        border-radius: 0 !important;
        flex: 1;
        min-height: 0;
        height: 100%;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        padding: 8px;
        box-sizing: border-box;
    }
    .screenshots-viewer img {
        max-width: 100% !important;
        max-height: 100% !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        display: block !important;
        margin: auto !important;
    }
    .screenshots-thumbs {
        margin-top: 12px;
        padding: 8px 6px;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.58);
        display: flex;
        justify-content: center;
        gap: 10px;
        overflow-x: auto;
        flex: 0 0 auto;
    }
    .product-gallery-thumb {
        width: 60px;
        height: 45px;
        border-radius: 6px;
        border: 2px solid transparent;
        overflow: hidden;
        cursor: pointer;
        padding: 0;
        background: #0f172a;
    }
    .product-gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-gallery-thumb.active {
        border-color: #3b82f6;
    }
    .product-gallery-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid rgba(148, 163, 184, 0.3);
        color: #fff;
        font-size: 1.25rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        z-index: 10;
    }
    .product-gallery-nav:hover {
        background: rgba(30, 41, 59, 0.9);
        border-color: #3b82f6;
    }
    .product-gallery-nav.prev {
        left: 20px;
    }
    .product-gallery-nav.next {
        right: 20px;
    }
    .product-lightbox-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.38);
        background: rgba(15, 23, 42, 0.82);
        color: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 99;
        transition: all 0.2s;
    }
    .product-lightbox-close:hover {
        background: rgba(30, 41, 59, 0.96);
        border-color: #ef4444;
        color: #ffffff;
    }
</style>

@if($galleryItems->isNotEmpty())
<div class="product-preview-modal" id="screenshots-modal" aria-hidden="true">
    <div class="product-preview-modal__overlay" data-close-modal></div>
    <div class="product-preview-modal__dialog {{ $galleryItems->count() > 1 ? 'has-thumbs' : 'no-thumbs' }}">
        <button type="button" class="product-lightbox-close" data-close-modal aria-label="Close gallery">
            <i class="fas fa-times"></i>
        </button>
        <div class="product-preview-modal__content">
            <div class="screenshots-viewer">
                @if($galleryItems->count() > 1)
                    <button type="button" class="product-gallery-nav prev" data-modal-gallery-nav="prev" aria-label="Previous screenshot">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                @endif
                <img id="modal-screenshot-image" src="{{ $galleryItems->first()['url'] ?? '' }}" alt="{{ $product->name }}" {!! media_lazy_attr() !!}>
                @if($galleryItems->count() > 1)
                    <button type="button" class="product-gallery-nav next" data-modal-gallery-nav="next" aria-label="Next screenshot">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                @endif
            </div>
            @if($galleryItems->count() > 1)
                <div class="screenshots-thumbs" id="modal-screenshot-thumbs">
                    @foreach($galleryItems as $index => $item)
                        <button type="button" class="product-gallery-thumb {{ $index === 0 ? 'active' : '' }}" data-modal-thumb-index="{{ $index }}">
                            <img src="{{ $item['thumbnail_url'] ?? $item['url'] }}" alt="{{ $item['alt'] }}" {!! media_lazy_attr() !!}>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function initProductPreviewModals() {
        const screenshotsModal = document.getElementById('screenshots-modal');
        if (!screenshotsModal) return;

        const openModal = (modal) => {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        const closeModal = (modal) => {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        document.querySelectorAll('[data-open-screenshots]').forEach((btn) => {
            btn.addEventListener('click', () => {
                openModal(screenshotsModal);
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                closeModal(btn.closest('.product-preview-modal'));
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(screenshotsModal);
            }
        });

        // Screenshot modal viewer
        const thumbs = Array.from(document.querySelectorAll('[data-modal-thumb-index]'));
        const mainImage = document.getElementById('modal-screenshot-image');
        const galleryUrls = @json($galleryItems->pluck('url')->values());
        let active = 0;

        const setScreenshot = (index) => {
            if (!galleryUrls.length || !mainImage) return;
            if (index < 0) index = galleryUrls.length - 1;
            if (index >= galleryUrls.length) index = 0;
            active = index;
            mainImage.src = galleryUrls[active];
            thumbs.forEach((thumb, i) => thumb.classList.toggle('active', i === active));
        };

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const index = Number(thumb.getAttribute('data-modal-thumb-index'));
                if (Number.isFinite(index)) setScreenshot(index);
            });
        });

        document.querySelectorAll('[data-modal-gallery-nav]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const dir = btn.getAttribute('data-modal-gallery-nav');
                setScreenshot(dir === 'prev' ? active - 1 : active + 1);
            });
        });
    })();
</script>
@endif

@if($previewVideos->isNotEmpty())
<div class="product-preview-modal" id="videos-modal" aria-hidden="true">
    <div class="product-preview-modal__overlay" data-close-modal></div>
    <div class="product-preview-modal__dialog {{ $previewVideos->count() > 1 ? 'has-thumbs' : 'no-thumbs' }}">
        <button type="button" class="product-lightbox-close" data-close-modal aria-label="Close videos">
            <i class="fas fa-times"></i>
        </button>
        <div class="product-preview-modal__content">
            <div class="screenshots-viewer" style="background: #000;">
                <iframe id="modal-video-iframe" src="" frameborder="0" allowfullscreen style="width: 100%; height: 100%; aspect-ratio: 16/9; border-radius: 12px; border: none;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
            @if($previewVideos->count() > 1)
                <div class="screenshots-thumbs" id="modal-video-thumbs">
                    @foreach($previewVideos as $index => $video)
                        @php
                            $vidId = '';
                            $regExp = '/^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/';
                            if (preg_match($regExp, $video['link'], $match)) {
                                if (isset($match[2]) && strlen($match[2]) == 11) {
                                    $vidId = $match[2];
                                }
                            }
                            $thumbUrl = $vidId ? "https://img.youtube.com/vi/{$vidId}/mqdefault.jpg" : '';
                        @endphp
                        @if($thumbUrl)
                            <button type="button" class="product-gallery-thumb {{ $index === 0 ? 'active' : '' }}" data-modal-video-index="{{ $index }}">
                                <img src="{{ $thumbUrl }}" alt="{{ $video['title'] ?? '' }}">
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function initVideoPreviewModals() {
        const videosModal = document.getElementById('videos-modal');
        if (!videosModal) return;

        const videoIframe = document.getElementById('modal-video-iframe');
        const thumbs = Array.from(document.querySelectorAll('[data-modal-video-index]'));
        
        const previewVideos = @json($previewVideos);
        let primaryIdx = 0;
        previewVideos.forEach((v, idx) => {
            if (v.primary === '1' || v.primary === 1) {
                primaryIdx = idx;
            }
        });

        const getEmbedUrl = (url) => {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = url.match(regExp);
            if (match && match[2].length === 11) {
                return 'https://www.youtube.com/embed/' + match[2];
            }
            return '';
        };

        const openModal = (modal) => {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            
            if (videoIframe && previewVideos[primaryIdx]) {
                videoIframe.src = getEmbedUrl(previewVideos[primaryIdx].link);
            }
        };

        const closeModal = (modal) => {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            
            if (videoIframe) {
                videoIframe.src = '';
            }
        };

        document.querySelectorAll('[data-open-videos]').forEach((btn) => {
            btn.addEventListener('click', () => {
                openModal(videosModal);
            });
        });

        videosModal.querySelectorAll('[data-close-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                closeModal(videosModal);
            });
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(videosModal);
            }
        });

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const index = Number(thumb.getAttribute('data-modal-video-index'));
                if (Number.isFinite(index) && previewVideos[index]) {
                    thumbs.forEach((t, i) => t.classList.toggle('active', i === index));
                    videoIframe.src = getEmbedUrl(previewVideos[index].link);
                }
            });
        });
    })();
</script>
@endif

@endsection
