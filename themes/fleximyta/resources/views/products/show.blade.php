@extends(($product->layout ?? 'default') === 'landing' ? 'layouts.landing' : 'layouts.app')

@section('title', $product->name ?? 'Product')

@section('description', $product->meta_description ?? $product->short_description ?? '')

@section('content')
@if(($product->layout ?? 'default') === 'landing')
    {!! render_dynamic_blocks($product->description_html) !!}
@else
<section class="page-header-section">
    <div class="container" style="max-width: {{ ($product->layout ?? 'default') === 'fullwidth' ? '1600px' : '1200px' }};">
        @php
            $productArchiveUrl = theme_permalink_url('products', '', 'archive');
            $breadcrumbs = [
                ['label' => __('Home'), 'url' => url('/')],
                ['label' => __('Products'), 'url' => $productArchiveUrl],
                ['label' => $product->name, 'url' => null],
            ];
            $breadcrumbs = \App\Facades\Hook::applyFilters('theme.fleximyta.product.breadcrumbs', $breadcrumbs, $product);
        @endphp
        <div class="product-market-header">
            <div class="product-market-breadcrumb">
                @foreach($breadcrumbs as $crumb)
                    @if(!$loop->first)
                        <span>›</span>
                    @endif
                    @if(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] ?? '' }}</a>
                    @else
                        <span>{{ $crumb['label'] ?? '' }}</span>
                    @endif
                @endforeach
            </div>
            <h1 class="page-header-title product-market-title">
                {{ $product->name }}
            </h1>
            <div class="product-market-meta">
                @php
                    $authorName = $product->user->name ?? $product->user->username ?? $product->author_name ?? null;
                    $salesCount = isset($productSalesCount) ? (int) $productSalesCount : 0;
                @endphp
                @if(!empty($authorName))
                    <span><i class="fas fa-user"></i> {{ __('By') }} <strong>{{ $authorName }}</strong></span>
                @endif
                <span><i class="fas fa-shopping-cart"></i> <strong>{{ $salesCount }}</strong> {{ __('sales') }}</span>
                @if(!empty($productVersion))
                    <span><i class="fas fa-tag"></i> {{ __('Version') }} {{ $productVersion }}</span>
                @endif
            </div>
            @if(!empty($product->short_description))
                <p class="page-header-excerpt">
                    {{ $product->short_description }}
                </p>
            @endif
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container" style="max-width: {{ in_array($product->layout ?? 'default', ['fullwidth', 'single-column']) ? '1200px' : '1200px' }};">
        @php
            $demoUrl = trim((string) data_get($product->settings, 'demo_url', ''));
            $previewUrl = trim((string) data_get($product->settings, 'preview_url', ''));
            if ($previewUrl === '') {
                $previewUrl = $demoUrl;
            }
            $isVariable = ($product->type ?? 'product') === 'variable';
            $activeVariants = $isVariable && $product->relationLoaded('activeVariants') ? $product->activeVariants : collect();
            $hasVariants = $isVariable && $activeVariants->isNotEmpty();

            $mergedMedia = collect($product->media ?? []);
            if ($hasVariants) {
                foreach ($activeVariants as $v) {
                    if ($v->image_id && $v->image && !$mergedMedia->contains('id', $v->image_id)) {
                        $mergedMedia->push($v->image);
                    }
                }
            }
            
            \Illuminate\Support\Facades\Log::info('Show Blade Debug:', [
                'isVariable' => $isVariable,
                'hasVariants' => $hasVariants,
                'relationLoaded' => $product->relationLoaded('activeVariants'),
                'activeVariantsCount' => $activeVariants->count(),
                'mediaCount' => $product->media ? $product->media->count() : 0,
                'mergedMediaCount' => $mergedMedia->count(),
                'galleriesObj' => $mergedMedia->pluck('id')->toArray(),
            ]);
        @endphp

        @if(($product->layout ?? 'default') === 'single-column')
            @php
                $showGallery = ($product->settings['show_gallery'] ?? true) !== false;
            @endphp
            @if($showGallery)
                <div class="product-gallery-top" style="margin-bottom: 50px; max-width: 1000px; margin-left: auto; margin-right: auto;">
                    @include('products.partials.gallery', ['product' => $product, 'galleries' => $mergedMedia])
                    <div class="product-gallery-actions">
                        <button type="button" class="gallery-action-btn gallery-action-btn--primary" data-open-live-preview @if(empty($demoUrl)) disabled @endif>
                            <i class="fas fa-external-link-alt" style="margin-right: 8px;"></i>{{ __('Live Preview') }}
                        </button>
                        <button type="button" class="gallery-action-btn" data-open-screenshots>
                            <i class="far fa-images" style="margin-right: 8px;"></i>{{ __('Screenshots') }}
                        </button>
                        <a class="gallery-action-btn" href="{{ $previewUrl ?: '#' }}" @if(!empty($previewUrl)) target="_blank" rel="noopener" @else aria-disabled="true" @endif>
                            <i class="far fa-play-circle" style="margin-right: 8px;"></i>{{ __('View Preview') }}
                        </a>
                    </div>
                </div>
            @endif
        @endif

        <div class="product-detail-layout {{ ($product->layout ?? 'default') === 'single-column' ? 'is-single-column' : '' }}">
            @if(($product->layout ?? 'default') !== 'single-column')
                {{-- Product Images --}}
                <div class="product-media-column">
                    <div class="product-hero-media">
                    @include('products.partials.gallery', ['product' => $product, 'galleries' => $mergedMedia])
                    </div>
                    <div class="product-gallery-actions">
                        <button type="button" class="gallery-action-btn gallery-action-btn--primary" data-open-live-preview @if(empty($demoUrl)) disabled @endif>
                            <i class="fas fa-external-link-alt" style="margin-right: 8px;"></i>{{ __('Live Preview') }}
                        </button>
                        <button type="button" class="gallery-action-btn" data-open-screenshots>
                            <i class="far fa-images" style="margin-right: 8px;"></i>{{ __('Screenshots') }}
                        </button>
                        <a class="gallery-action-btn" href="{{ $previewUrl ?: '#' }}" @if(!empty($previewUrl)) target="_blank" rel="noopener" @else aria-disabled="true" @endif>
                            <i class="far fa-play-circle" style="margin-right: 8px;"></i>{{ __('View Preview') }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Product Details --}}
            <div class="product-details">
                @php
                    $defaultPrice = ($product->sale_price && $product->sale_price < $product->price) ? $product->sale_price : $product->price;
                    $isDigitalProduct = ($product->type ?? 'product') === 'digital';
                    $hasPackages = $isDigitalProduct && $product->services && $product->services->count() > 0;
                    
                    $variantPickers = collect();
                    
                    if ($hasVariants && isset($specAttributes)) {
                        $usedKeys = [];
                        foreach ($activeVariants as $v) {
                            if (is_array($v->attribute_values)) {
                                foreach (array_keys($v->attribute_values) as $key) {
                                    $usedKeys[$key] = true;
                                }
                            }
                        }
                        $variantPickers = collect($product->variantAttributes)->filter(function($attr) use ($usedKeys) {
                            return isset($usedKeys[$attr->slug]) && !empty($attr->pivot->selected_value_ids);
                        })->values();
                    }
                    
                    $defaultCartItem = [
                        'product_id' => $product->id,
                        'service_id' => null,
                        'variant_id' => null,
                        'name' => $product->name,
                        'price' => (float) $defaultPrice,
                        'quantity' => 1,
                        'billing_cycle' => 'month',
                        'image_url' => $product->primaryImage()?->url,
                        'slug' => $product->slug,
                        'permalink' => method_exists($product, 'getFrontendUrlAttribute') ? $product->frontend_url : route('products.show', ['slug' => $product->slug])
                    ];
                @endphp
                <div class="product-pricing-card">
                    @if($hasPackages)
                        @php
                            $firstService = $product->services->first();
                            $firstServicePrice = $firstService ? (float) ($firstService->price ?? $product->price) : (float) $defaultPrice;
                            $firstServiceDisplay = $firstService ? format_currency($firstServicePrice) : format_currency($defaultPrice);
                            $packagePayloads = [];
                            foreach ($product->services as $service) {
                                $servicePrice = (float) ($service->price ?? $product->price);
                                $packagePayloads[$service->id] = [
                                    'product_id' => $product->id,
                                    'service_id' => $service->id,
                                    'name' => $product->name . ' - ' . $service->name,
                                    'price' => $servicePrice,
                                    'quantity' => 1,
                                    'billing_cycle' => $service->duration_unit ?? 'month',
                                    'image_url' => $product->primaryImage()?->url,
                                    'slug' => $product->slug,
                                    'permalink' => method_exists($product, 'getFrontendUrlAttribute') ? $product->frontend_url : route('products.show', ['slug' => $product->slug]),
                                    'display_price' => format_currency($servicePrice),
                                ];
                            }
                        @endphp
                        <div class="product-pricing-head">{{ __('License Type') }}</div>
                        <select
                            id="product-package-select-{{ $product->id }}"
                            class="product-package-select"
                            data-product-id="{{ $product->id }}"
                        >
                            @foreach($product->services as $service)
                                @php $servicePrice = (float) ($service->price ?? $product->price); @endphp
                                <option value="{{ $service->id }}" data-display-price="{{ format_currency($servicePrice) }}">
                                    {{ $service->name }} - {{ format_currency($servicePrice) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="product-price-row">
                            <div class="product-price-large" id="product-package-price-{{ $product->id }}">{{ $firstServiceDisplay }}</div>
                        </div>
                    @else
                        <div class="product-pricing-head">{{ __('Price') }}</div>
                        <div class="product-price-row" id="product-price-display">
                            @if($product->getPriceRangeAttribute() && $hasVariants)
                                <div class="product-price-large">
                                    {{ format_currency($product->getPriceRangeAttribute()['min']) }} - {{ format_currency($product->getPriceRangeAttribute()['max']) }}
                                </div>
                            @elseif($product->sale_price && $product->sale_price < $product->price)
                                <div class="product-price-large">
                                    {{ format_currency($product->sale_price) }}
                                </div>
                                <div class="product-sale-price">
                                    {{ format_currency($product->price) }}
                                </div>
                            @else
                                <div class="product-price-large">
                                    {{ format_currency($product->price) }}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($hasVariants && $variantPickers->isNotEmpty())
                        <div class="product-variants-container" style="margin-top: 15px;">
                            @foreach($variantPickers as $attr)
                                <div class="variant-picker-group" data-attr-slug="{{ $attr->slug }}" style="margin-bottom: 12px;">
                                    <div class="variant-picker-label" style="font-size: 0.9rem; font-weight: 700; color: #475569; margin-bottom: 6px;">
                                        {{ $attr->name }}: <span class="selected-value" style="color: var(--primary);"></span>
                                    </div>
                                    <div class="variant-picker-options" style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        @php
                                            $rawPivotIds = $attr->pivot->selected_value_ids ?? [];
                                            $selectedIds = is_string($rawPivotIds) ? json_decode($rawPivotIds, true) : $rawPivotIds;
                                            if (!is_array($selectedIds)) $selectedIds = [];
                                        @endphp
                                        @foreach($attr->values as $val)
                                            @if(in_array($val->id, $selectedIds))
                                                <button type="button" class="variant-option-btn {{ $attr->display_type === 'color_swatch' ? 'is-color' : '' }}" data-value="{{ $val->value }}" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                                                    {{ $val->value }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="product-benefits">
                        <div class="product-benefit-item"><i class="fas fa-check"></i> {{ __('Quality checked') }}</div>
                        <div class="product-benefit-item"><i class="fas fa-check"></i> {{ __('Future updates') }}</div>
                        <div class="product-benefit-item"><i class="fas fa-check"></i> {{ __('Support included') }}</div>
                    </div>
                    
                    <div id="product-stock-display">
                        @if($product->stock_status === 'in_stock')
                            <span class="stock-badge in-stock">
                                <i class="fas fa-check-circle" style="margin-right: 8px;"></i>{{ __('In Stock') }}
                            </span>
                        @elseif($product->stock_status === 'out_of_stock')
                            <span class="stock-badge out-of-stock">
                                <i class="fas fa-times-circle" style="margin-right: 8px;"></i>{{ __('Out of Stock') }}
                            </span>
                        @endif
                    </div>

                    <div class="product-purchase-actions">
                        @if($hasPackages)
                            <button
                                type="button"
                                id="btn-add-to-cart-action"
                                class="primary-btn product-action-btn"
                                onclick="addSelectedPackageToCart(event, {{ $product->id }});"
                            >
                                <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i><span class="btn-text">{{ __('Add to Cart') }}</span>
                            </button>
                        @else
                            <button
                                type="button"
                                id="btn-add-to-cart-action"
                                class="primary-btn product-action-btn"
                                onclick="addToCart({{ json_encode($defaultCartItem) }});"
                                {{ $product->stock_status === 'out_of_stock' ? 'disabled' : '' }}
                            >
                                <i class="fas fa-shopping-cart" style="margin-right: 8px;"></i><span class="btn-text">{{ __('Add to Cart') }}</span>
                            </button>
                        @endif
                    </div>

                    @if($hasPackages)
                        <a href="#product-packages-panel" class="view-all-packages-link" data-tab-target="packages">{{ __('View all Packages') }}</a>
                    @endif
                </div>

                @if($product->categories && $product->categories->count() > 0)
                    <div class="product-category-box">
                        <span style="font-weight: 700; font-size: 16px; margin-right: 10px;">
                            <i class="fas fa-tags" style="margin-right: 8px; color: var(--primary);"></i>{{ __('Categories') }}:
                        </span>
                        @foreach($product->categories as $category)
                            <a href="{{ route('categories.show', ['slug' => $category->slug]) }}" class="product-category-tag">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($hasPackages)
                    <div id="product-packages-panel" class="product-tab-panel product-packages-panel" style="margin-bottom: 50px;">
                        <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 25px; display: flex; align-items: center; letter-spacing: -0.5px;">
                            <i class="fas fa-box-open" style="margin-right: 12px; color: var(--primary);"></i>
                            {{ __('Available Packages') }}
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            @foreach($product->services as $service)
                                <div class="package-card">
                                    <div style="flex: 1; padding-right: 30px;">
                                        <div style="font-weight: 800; font-size: 1.25rem; margin-bottom: 10px;">{{ $service->name }}</div>
                                        
                                        @if($service->capabilities && count($service->capabilities) > 0)
                                            <ul style="list-style: none; padding: 0; margin: 15px 0; display: flex; flex-direction: column; gap: 8px;">
                                                @foreach($service->capabilities as $capKey => $capValue)
                                                    <li style="font-size: 0.95rem; color: #64748b; display: flex; align-items: start;">
                                                        <i class="fas fa-check-circle" style="color: #10b981; margin-right: 10px; margin-top: 4px; font-size: 1rem;"></i>
                                                        <span><strong>{{ $capKey }}:</strong> {{ $capValue }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        <div class="package-info">
                                            <i class="far fa-clock" style="margin-right: 8px;"></i>
                                            @if($service->access_type === 'permanent')
                                                {{ __('One-time purchase') }}
                                            @else
                                                {{ $service->duration_value }} {{ __(Str::plural($service->duration_unit, $service->duration_value)) }} {{ __('access') }}
                                                @if($service->is_recurring) ({{ __('Recurring') }}) @endif
                                            @endif
                                            @if($service->access_type !== 'permanent' && $service->trial_period_days > 0)
                                                • {{ $service->trial_period_days }} {{ __('days trial') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div style="text-align: right; min-width: 140px;">
                                        <div style="font-weight: 900; font-size: 1.5rem; color: var(--primary); margin-bottom: 15px;">
                                            {{ format_currency($service->price ?? $product->price) }}
                                        </div>
                                        <div class="package-select-note">{{ __('Select this package in the purchase panel') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div id="product-details-panel" class="product-description-wrap">
            @php
                $hasFaqTab = !empty($hasProductFaqTab);
                $faqItems = collect($productFaqItems ?? [])->values();
                $customTabs = collect($productCustomTabs ?? [])->values();
                $hasCustomTabs = !empty($hasProductCustomTabs) && $customTabs->isNotEmpty();
                $defaultCustomTabId = !empty($defaultProductCustomTabId) ? (string) $defaultProductCustomTabId : null;

                $specAttributes = collect();
                if (isset($product) && $product->relationLoaded('variantAttributes')) {
                    $specAttributes = $product->variantAttributes->filter(function($attr) {
                        return !empty($attr->pivot->selected_value_ids);
                    })->groupBy(function($attr) {
                        return $attr->group ? $attr->group->name : __('General');
                    });
                }
                $hasSpecifications = $specAttributes->isNotEmpty();
            @endphp
            <div class="product-detail-tabs" data-default-tab="{{ $defaultCustomTabId ? ('product-custom-tab-' . $defaultCustomTabId) : '' }}">
                <a href="#product-item-details-panel" class="active">{{ __('Item Details') }}</a>
                @if($hasSpecifications)
                    <a href="#product-specifications-panel">{{ __('Specifications') }}</a>
                @endif
                @if($hasPackages)
                    <a href="#product-packages-panel" data-tab-target="packages">{{ __('Packages') }}</a>
                @endif
                @if($hasFaqTab)
                    <a href="#product-faq-panel">{{ __("FAQ's") }}</a>
                @endif
                @if($hasCustomTabs)
                    @foreach($customTabs as $tab)
                        <a href="#product-custom-tab-{{ $tab['id'] ?? $loop->index }}">{{ $tab['title'] ?? '' }}</a>
                    @endforeach
                @endif
            </div>
            <div id="product-item-details-panel" class="product-tab-panel product-description-box is-active">
                <div class="prose" style="font-size: 1.05rem; line-height: 1.85;">
                {!! render_dynamic_blocks($product->description_html ?? '') !!}
                </div>
            </div>
            @if($hasSpecifications)
                <div id="product-specifications-panel" class="product-tab-panel product-specifications-box">
                    <div class="specifications-container" style="display: flex; flex-direction: column; gap: 30px;">
                        @foreach($specAttributes as $groupName => $attributes)
                            <div class="specification-group">
                                <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 12px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">{{ $groupName }}</h4>
                                <div class="specification-table" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                    @foreach($attributes as $attr)
                                        @php
                                            $rawPivotIds = $attr->pivot->selected_value_ids ?? [];
                                            $selectedIds = is_string($rawPivotIds) ? json_decode($rawPivotIds, true) : $rawPivotIds;
                                            if (!is_array($selectedIds)) $selectedIds = [];
                                            $selectedValues = collect($attr->values ?? [])->filter(fn($v) => in_array($v->id, $selectedIds))->pluck('value')->implode(', ');
                                        @endphp
                                        <div class="specification-row" style="display: flex; border-bottom: 1px solid #f1f5f9; padding: 12px 16px;">
                                            <div class="specification-label" style="width: 40%; font-weight: 600; color: #64748b;">{{ $attr->name }}</div>
                                            <div class="specification-value" style="width: 60%; color: #334155;">{{ $selectedValues }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($hasFaqTab)
                @php
                    $faqAccordionItems = $faqItems->map(function ($faq) {
                        return [
                            'title' => $faq['question'] ?? '',
                            'content' => $faq['answer'] ?? '',
                            'open' => !empty($faq['open']),
                            'is_html' => true,
                        ];
                    })->all();
                @endphp
                <div id="product-faq-panel" class="product-faq-panel product-tab-panel">
                    <x-accordion :items="$faqAccordionItems" style="standard" />
                </div>
            @endif
            @if($hasCustomTabs)
                @foreach($customTabs as $tab)
                    @php
                        $tabId = 'product-custom-tab-' . ($tab['id'] ?? $loop->index);
                    @endphp
                    <div id="{{ $tabId }}" class="product-custom-tab-panel product-tab-panel">
                        <div class="prose">
                            {!! $tab['content'] ?? '' !!}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

@php
    $galleryItems = collect($mergedMedia ?? $product->media ?? [])->map(function ($media) use ($product) {
        return [
            'url' => $media->url ?? '',
            'alt' => $product->name,
        ];
    })->filter(fn ($item) => !empty($item['url']))->values();
@endphp

<div class="product-preview-modal" id="live-preview-modal" aria-hidden="true">
    <div class="product-preview-modal__overlay" data-close-modal></div>
    <div class="product-preview-modal__dialog">
        <div class="product-preview-modal__topbar">
            <div class="product-preview-modal__title">{{ $product->name }}</div>
            <div class="product-preview-modal__actions">
                <button type="button" class="gallery-action-btn gallery-action-btn--mini gallery-action-btn--primary" data-modal-add-to-cart>
                    <i class="fas fa-shopping-cart" style="margin-right: 6px;"></i>{{ __('Add to Cart') }}
                </button>
                <button type="button" class="gallery-action-btn gallery-action-btn--mini" data-close-modal>
                    <i class="fas fa-arrow-left" style="margin-right: 6px;"></i>{{ __('Back to Project') }}
                </button>
            </div>
        </div>
        <div class="product-preview-modal__content">
            @if(!empty($demoUrl))
                <iframe id="live-preview-iframe" data-src="{{ $demoUrl }}" src="about:blank" class="product-preview-modal__iframe" loading="lazy"></iframe>
            @else
                <div class="product-preview-modal__empty">{{ __('Live preview URL is not configured for this product.') }}</div>
            @endif
        </div>
    </div>
</div>

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
                <img id="modal-screenshot-image" src="{{ $galleryItems->first()['url'] ?? '' }}" alt="{{ $product->name }}">
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
                            <img src="{{ $item['url'] }}" alt="{{ $item['alt'] }}">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endif

@push('scripts')
@if(!empty($hasPackages))
    <script>
        window.productPackagePayloads = window.productPackagePayloads || {};
        window.productPackagePayloads['{{ $product->id }}'] = @json($packagePayloads ?? []);

        (function initProductPackageSelector(productId) {
            const selectEl = document.getElementById(`product-package-select-${productId}`);
            const priceEl = document.getElementById(`product-package-price-${productId}`);

            if (!selectEl || !priceEl) return;

            const syncPrice = () => {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const displayPrice = selectedOption?.dataset?.displayPrice || '';
                if (displayPrice) {
                    priceEl.textContent = displayPrice;
                }
            };

            selectEl.addEventListener('change', syncPrice);
            syncPrice();
        })('{{ $product->id }}');

        window.addSelectedPackageToCart = function addSelectedPackageToCart(event, productId) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            const selectEl = document.getElementById(`product-package-select-${productId}`);
            const selectedServiceId = selectEl ? selectEl.value : null;
            const payload = window.productPackagePayloads?.[productId]?.[selectedServiceId];

            if (!payload || typeof window.addToCart !== 'function') {
                return;
            }

            window.addToCart(payload);
        };
    </script>
@endif

@if($hasVariants)
    @php
        $productVariantsConfigPayload = $activeVariants->map(function($v) use ($defaultCartItem) {
            $baseCartItem = $defaultCartItem;
            $baseCartItem['variant_id'] = $v->id;
            $baseCartItem['price'] = (float) $v->effective_price;
            if ($v->sku) {
                $baseCartItem['sku'] = $v->sku;
            }
            if ($v->image_id && $v->image) {
                $baseCartItem['image_url'] = $v->image->url;
            }
            return [
                'id' => $v->id,
                'attribute_values' => $v->attribute_values,
                'price' => (float) $v->effective_price,
                'stock_status' => $v->stock_status,
                'stock_quantity' => $v->stock_quantity,
                'image_url' => $v->image ? $v->image->url : null,
                'cart_payload' => $baseCartItem,
            ];
        })->values();
    @endphp
    <script>
        window.productVariantsConfig = {!! json_encode($productVariantsConfigPayload) !!};

        (function initProductVariantSelectors() {
            const container = document.querySelector('.product-variants-container');
            if (!container) return;

            const formatCurrency = (amount) => {
                // VERY basic fallback, use the actual format_currency macro logic from global window if possible
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount).replace('$', '$ ');
            };

            const formatServerCurrency = (amount) => {
                // If the app has window.PolyCMS.formatCurrency, use it
                if (window.PolyCMS && typeof window.PolyCMS.formatCurrency === 'function') {
                    return window.PolyCMS.formatCurrency(amount);
                }
                return "$" + Number(amount).toFixed(2);
            };

            const groups = Array.from(container.querySelectorAll('.variant-picker-group'));
            const selectedOptions = {};

            const updateUIState = () => {
                // 1. Update active states on buttons
                groups.forEach(group => {
                    const attrSlug = group.dataset.attrSlug;
                    const currentValue = selectedOptions[attrSlug];
                    
                    const labelSpan = group.querySelector('.selected-value');
                    if (labelSpan) labelSpan.textContent = currentValue || '';

                    const buttons = group.querySelectorAll('.variant-option-btn');
                    buttons.forEach(btn => {
                        if (btn.dataset.value === currentValue) {
                            btn.style.borderColor = 'var(--primary, #4f46e5)';
                            btn.style.background = 'var(--primary-light, #eefaec)'; // Or similar active highlight
                            btn.style.color = 'var(--primary, #4f46e5)';
                        } else {
                            btn.style.borderColor = '#cbd5e1';
                            btn.style.background = '#fff';
                            btn.style.color = 'inherit';
                        }
                    });
                });

                // 2. See if we have a match
                const isFullySelected = groups.every(g => selectedOptions[g.dataset.attrSlug]);
                const match = window.productVariantsConfig.find(variant => {
                    // check if this variant's attribute_values matches the selectedOptions exactly
                    for (const [key, value] of Object.entries(variant.attribute_values)) {
                        if (selectedOptions[key] !== value) return false;
                    }
                    return true;
                });

                const priceDisplay = document.querySelector('#product-price-display');
                const stockDisplay = document.querySelector('#product-stock-display');
                const addToCartBtn = document.querySelector('#btn-add-to-cart-action');
                
                if (isFullySelected && match) {
                    // Update Price
                    if (priceDisplay) {
                        priceDisplay.innerHTML = `<div class="product-price-large">${formatServerCurrency(match.price)}</div>`;
                    }
                    
                    // Update Stock Status
                    const isOutOfStock = match.stock_status === 'out_of_stock' || (match.stock_quantity <= 0 && match.stock_status !== 'in_stock');
                    
                    if (stockDisplay) {
                        if (isOutOfStock) {
                            stockDisplay.innerHTML = `<span class="stock-badge out-of-stock"><i class="fas fa-times-circle" style="margin-right: 8px;"></i>Out of Stock</span>`;
                        } else {
                            stockDisplay.innerHTML = `<span class="stock-badge in-stock"><i class="fas fa-check-circle" style="margin-right: 8px;"></i>In Stock</span>`;
                        }
                    }

                    // Update Add To Cart Button
                    if (addToCartBtn) {
                        if (isOutOfStock) {
                            addToCartBtn.disabled = true;
                            addToCartBtn.querySelector('.btn-text').textContent = 'Out of Stock';
                            addToCartBtn.onclick = null;
                        } else {
                            addToCartBtn.disabled = false;
                            addToCartBtn.querySelector('.btn-text').textContent = 'Add to Cart';
                            addToCartBtn.onclick = () => {
                                if (typeof window.addToCart === 'function') {
                                    window.addToCart(match.cart_payload);
                                }
                            };
                        }
                    }

                    // Update Image if variant specifies one
                    if (match.image_url) {
                        const thumbs = document.querySelectorAll('.product-gallery-thumb img');
                        for (const thumbImg of thumbs) {
                            if (thumbImg.src.includes(match.image_url) || match.image_url.includes(thumbImg.getAttribute('src'))) {
                                const btn = thumbImg.closest('.product-gallery-thumb');
                                if (btn && !btn.classList.contains('active')) {
                                    btn.click();
                                }
                                break;
                            }
                        }
                    } else {
                        const firstThumbBtn = document.querySelector('.product-gallery-thumb');
                        if (firstThumbBtn && !firstThumbBtn.classList.contains('active')) {
                            firstThumbBtn.click();
                        }
                    }
                    
                } else if (isFullySelected && !match) {
                    // Combination selected but doesn't exist in config -> Unavailable
                    if (stockDisplay) {
                        stockDisplay.innerHTML = `<span class="stock-badge out-of-stock"><i class="fas fa-times-circle" style="margin-right: 8px;"></i>Unavailable</span>`;
                    }
                    if (addToCartBtn) {
                        addToCartBtn.disabled = true;
                        addToCartBtn.querySelector('.btn-text').textContent = 'Unavailable';
                        addToCartBtn.onclick = null;
                    }
                }
            };

            // Bind click events
            groups.forEach(group => {
                const attrSlug = group.dataset.attrSlug;
                const buttons = group.querySelectorAll('.variant-option-btn');
                buttons.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const val = btn.dataset.value;
                        if (selectedOptions[attrSlug] === val) {
                            // deselect if clicked again
                            delete selectedOptions[attrSlug];
                        } else {
                            selectedOptions[attrSlug] = val;
                        }
                        updateUIState();
                    });
                });
            });
            
            // Auto-select first variant on load to avoid unselected states if desired (optional)
            if (window.productVariantsConfig.length > 0) {
                const first = window.productVariantsConfig[0];
                for (const [key, val] of Object.entries(first.attribute_values)) {
                    selectedOptions[key] = val;
                }
                updateUIState();
            }

            // Reverse sync variant from gallery thumbnail click
            let isUpdatingFromGallery = false;
            const galleryThumbs = document.querySelectorAll('.product-gallery-thumb');
            galleryThumbs.forEach(thumbBtn => {
                thumbBtn.addEventListener('click', (e) => {
                    if (isUpdatingFromGallery) return;
                    
                    const img = thumbBtn.querySelector('img');
                    if (!img) return;
                    const src = img.getAttribute('src');
                    
                    let matchingVariant = null;
                    for (const variant of window.productVariantsConfig) {
                        if (variant.image_url && (src.includes(variant.image_url) || variant.image_url.includes(src))) {
                            matchingVariant = variant;
                            break;
                        }
                    }
                    
                    if (matchingVariant) {
                        isUpdatingFromGallery = true;
                        for (const [key, val] of Object.entries(matchingVariant.attribute_values)) {
                            selectedOptions[key] = val;
                        }
                        updateUIState();
                        setTimeout(() => {
                            isUpdatingFromGallery = false;
                        }, 50);
                    }
                });
            });

        })();
    </script>
@endif

    <script>
        (function initProductDetailTabs() {
            const tabLinks = Array.from(document.querySelectorAll('.product-detail-tabs a'));
            if (!tabLinks.length) return;
            const tabsContainer = document.querySelector('.product-detail-tabs');

            const tabPanels = Array.from(document.querySelectorAll('.product-tab-panel'));

            const activateTab = (targetId, options = {}) => {
                const { scroll = true } = options;
                tabLinks.forEach((link) => {
                    const href = link.getAttribute('href') || '';
                    const linkTargetId = href.startsWith('#') ? href.slice(1) : '';
                    link.classList.toggle('active', linkTargetId === targetId);
                });

                tabPanels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.id === targetId);
                });

                if (!scroll) return;
                if (tabsContainer) {
                    const visualGap = 16;
                    const globalOffset = (
                        window.PolyCMS &&
                        typeof window.PolyCMS.refreshFixedTopOffset === 'function'
                    )
                        ? window.PolyCMS.refreshFixedTopOffset(visualGap)
                        : (() => {
                            const adminBar = document.getElementById('wpadminbar');
                            const topbar = document.getElementById('polycms-topbar');
                            const mainHeader = document.getElementById('main-header');
                            const adminOffset = adminBar ? adminBar.offsetHeight : 0;
                            const topbarOffset = topbar ? topbar.offsetHeight : 0;
                            const headerOffset = mainHeader ? mainHeader.offsetHeight : 0;
                            return adminOffset + topbarOffset + headerOffset + visualGap;
                        })();
                    const topCoverOffset = ['#wpadminbar', '#polycms-topbar', '#main-header']
                        .map((selector) => document.querySelector(selector))
                        .filter(Boolean)
                        .reduce((maxBottom, el) => {
                            const style = window.getComputedStyle(el);
                            if (style.display === 'none' || style.visibility === 'hidden') return maxBottom;
                            if (style.position !== 'fixed' && style.position !== 'sticky') return maxBottom;
                            const rect = el.getBoundingClientRect();
                            if (rect.bottom <= 0) return maxBottom;
                            return Math.max(maxBottom, rect.bottom);
                        }, 0);
                    const offset = Math.max(globalOffset, Math.ceil(topCoverOffset) + visualGap);
                    const top = tabsContainer.getBoundingClientRect().top + window.scrollY - offset;
                    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
                    return;
                }
                const target = document.getElementById(targetId);
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            tabLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    const href = link.getAttribute('href') || '';
                    if (!href.startsWith('#')) return;
                    event.preventDefault();
                    const targetId = href.slice(1);
                    const target = document.getElementById(targetId);
                    if (!target) return;
                    activateTab(targetId);
                });
            });

            document.querySelectorAll('[data-tab-target="packages"]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    const targetId = 'product-packages-panel';
                    const target = document.getElementById(targetId);
                    if (!target) return;
                    activateTab(targetId);
                });
            });

            const hashTargetId = (window.location.hash || '').replace('#', '');
            if (hashTargetId) {
                const target = document.getElementById(hashTargetId);
                if (target) {
                    activateTab(hashTargetId, { scroll: false });
                }
            } else if (tabsContainer) {
                const defaultTabId = tabsContainer.getAttribute('data-default-tab') || '';
                if (defaultTabId) {
                    const target = document.getElementById(defaultTabId);
                    if (target) {
                        activateTab(defaultTabId, { scroll: false });
                    }
                } else {
                    const firstLinkHref = tabLinks[0]?.getAttribute('href') || '';
                    const firstId = firstLinkHref.startsWith('#') ? firstLinkHref.slice(1) : '';
                    if (firstId) {
                        activateTab(firstId, { scroll: false });
                    }
                }
            }
        })();
    </script>
    <script>
        (function initProductPreviewModals() {
            const liveModal = document.getElementById('live-preview-modal');
            const screenshotsModal = document.getElementById('screenshots-modal');
            const liveIframe = document.getElementById('live-preview-iframe');

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

            document.querySelectorAll('[data-open-live-preview]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (!liveModal) return;
                    if (liveIframe && liveIframe.dataset.src && liveIframe.src === 'about:blank') {
                        liveIframe.src = liveIframe.dataset.src;
                    }
                    openModal(liveModal);
                });
            });

            document.querySelectorAll('[data-open-screenshots]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (!screenshotsModal) return;
                    openModal(screenshotsModal);
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const target = btn.closest('.product-preview-modal');
                    closeModal(target);
                });
            });

            window.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                closeModal(liveModal);
                closeModal(screenshotsModal);
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

            // Add to cart button inside modal topbar
            document.querySelectorAll('[data-modal-add-to-cart]').forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    @if($hasPackages)
                        window.addSelectedPackageToCart?.(event, {{ $product->id }});
                    @else
                        window.addToCart?.(@json($defaultCartItem));
                    @endif
                });
            });
        })();
    </script>
@endpush

@push('styles')
<style>
.product-detail-layout {
    display: grid;
    grid-template-columns: 1.08fr 0.92fr;
    gap: 48px;
    align-items: start;
}

.product-detail-layout.is-single-column {
    grid-template-columns: 1fr;
    max-width: 920px;
    margin: 0 auto;
}

.product-details {
    display: flex;
    flex-direction: column;
    gap: 20px;
    position: sticky;
    top: 95px;
}

.product-market-header {
    max-width: 980px;
}

.product-market-breadcrumb {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
    margin-bottom: 14px;
    color: #64748b;
}

.product-market-title {
    font-size: clamp(2.1rem, 4vw, 3.2rem);
    line-height: 1.08;
    letter-spacing: -0.02em;
    font-weight: 800;
    color: #1e2f47;
    margin-bottom: 16px;
}

.product-market-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    color: #58667e;
    font-size: 1.04rem;
    line-height: 1.2;
}

.product-market-meta i {
    margin-right: 6px;
    color: #2e9cf1;
}

.product-market-meta strong {
    color: #1f334d;
    font-weight: 700;
}

.product-hero-media {
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 18px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}

.product-media-column {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.product-gallery-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
}

.gallery-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 22px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.45);
    background: #ffffff;
    color: #334155;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: var(--transition);
}

.gallery-action-btn:hover {
    border-color: #3b82f6;
    color: #1d4ed8;
}

.gallery-action-btn[disabled],
.gallery-action-btn[aria-disabled="true"] {
    opacity: 0.5;
    pointer-events: none;
}

.gallery-action-btn--primary {
    background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
    color: #fff;
    border-color: transparent;
}

.gallery-action-btn--primary:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
}

.gallery-action-btn--mini {
    padding: 9px 16px;
    font-size: 0.9rem;
}

.product-pricing-card {
    background: #ffffff;
    border: 1px solid rgba(148, 163, 184, 0.3);
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
    border-radius: 18px;
    padding: 24px 26px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: #0f172a;
}

.product-pricing-head {
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #475569;
}

.product-package-select {
    appearance: none;
    width: 100%;
    border: 1px solid rgba(148, 163, 184, 0.4);
    border-radius: 12px;
    padding: 12px 42px 12px 14px;
    background: #f8fafc;
    color: #0f172a;
    font-size: 0.98rem;
    font-weight: 600;
    line-height: 1.35;
    outline: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7.5L10 12.5L15 7.5' stroke='%23cbd5e1' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

.product-package-select:focus {
    border-color: rgba(129, 140, 248, 0.85);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.product-price-row {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 10px;
    justify-content: center;
    text-align: center;
    margin-top: 2px;
    margin-bottom: 4px;
}

.product-sale-price {
    text-decoration: line-through;
    color: #94a3b8;
}

.product-price-large {
    font-size: clamp(2.8rem, 6vw, 4.4rem);
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.03em;
    color: #1e334d;
}

.product-benefits {
    border-top: 1px solid rgba(148, 163, 184, 0.24);
    border-bottom: 1px solid rgba(148, 163, 184, 0.24);
    padding: 10px 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.product-benefit-item {
    color: #334155;
    font-size: 1rem;
}

.product-benefit-item i {
    color: #22c55e;
    margin-right: 8px;
}

.product-purchase-actions {
    margin-top: 6px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.product-action-btn {
    width: 100%;
    text-align: center;
    padding: 12px 18px;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
}

.product-description-box {
    border: none;
    padding: 0;
    background: transparent;
    box-shadow: none;
    border-radius: 0;
}

.product-description-wrap {
    margin-top: 34px;
    scroll-margin-top: 98px;
}

#product-packages-panel {
    scroll-margin-top: 98px;
}

#product-faq-panel {
    margin-top: 26px;
    scroll-margin-top: 98px;
}

.product-faq-panel .faq-item {
    border: 1px solid rgba(148, 163, 184, 0.28);
    border-radius: 12px;
    margin-bottom: 12px;
    background: #ffffff;
}

.product-faq-panel .faq-question {
    font-weight: 700;
    color: #1f2937;
}

.product-faq-panel .faq-answer {
    color: #4b5563;
}

.product-custom-tab-panel {
    margin-top: 26px;
    border: none;
    border-radius: 0;
    background: transparent;
    padding: 0;
    box-shadow: none;
    scroll-margin-top: 98px;
}

.product-tab-panel {
    display: none;
}

.product-tab-panel.is-active {
    display: block;
}

.product-tab-panel .prose {
    margin-top: 0;
    margin-bottom: 0;
    color: #475569;
}

.product-tab-panel .prose strong,
.product-tab-panel .prose b,
.product-tab-panel .prose h1,
.product-tab-panel .prose h2,
.product-tab-panel .prose h3,
.product-tab-panel .prose h4,
.product-tab-panel .prose h5,
.product-tab-panel .prose h6 {
    color: #1f2937;
}

.product-tab-panel .prose a {
    color: #2563eb;
}

.product-faq-panel {
    margin-top: 18px;
}

.product-faq-panel .faq-container {
    margin: 0;
    padding: 0;
    gap: 0;
}

.product-faq-panel .faq-item {
    background: transparent;
    border: 0;
    border-bottom: 1px solid rgba(129, 140, 248, 0.26);
    border-radius: 0;
    box-shadow: none;
    margin: 0;
}

.product-faq-panel .faq-question {
    padding: 1rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #dbe6ff;
    gap: 14px;
}

.product-faq-panel .faq-question i {
    margin-right: 0;
    color: #a5b4fc;
    font-size: 1rem;
}

.product-faq-panel .faq-answer {
    padding: 0;
    color: #c7d2fe;
    opacity: 0;
}

.product-faq-panel .faq-answer.active {
    padding: 0 0 1rem;
    opacity: 1;
}

.product-faq-panel .faq-answer .prose {
    color: #cbd5e1;
    max-width: none;
}

.product-detail-tabs {
    display: flex;
    align-items: center;
    gap: 24px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    margin-bottom: 20px;
}

.product-detail-tabs a {
    padding: 10px 4px;
    font-weight: 600;
    color: #475569;
    border-bottom: 2px solid transparent;
}

.product-detail-tabs a.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}

.view-all-packages-link {
    display: inline-flex;
    align-self: center;
    margin-top: 4px;
    font-weight: 700;
    font-size: 1rem;
    color: #1d4ed8;
}

.product-preview-modal {
    position: fixed;
    inset: 0;
    z-index: 2147483640;
    display: none;
}

.product-preview-modal.is-open {
    display: block;
}

.product-preview-modal__overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
}

.product-preview-modal__dialog {
    position: relative;
    margin: 26px auto;
    width: min(96vw, 1480px);
    max-height: calc(100vh - 52px);
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px rgba(2, 6, 23, 0.35);
}

.product-preview-modal__topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 18px;
    background: #2d435c;
    color: #fff;
}

.product-preview-modal__title {
    font-size: 1.3rem;
    font-weight: 700;
}

.product-preview-modal__actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.product-preview-modal__content {
    padding: 14px;
    overflow: auto;
}

.product-preview-modal__iframe {
    width: 100%;
    min-height: 78vh;
    border: 0;
    border-radius: 10px;
    background: #fff;
}

.product-preview-modal__empty {
    padding: 30px;
    text-align: center;
    color: #64748b;
}

.screenshots-viewer {
    position: relative;
    border: 1px solid rgba(148, 163, 184, 0.3);
    border-radius: 12px;
    overflow: hidden;
    background: #f8fafc;
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.screenshots-viewer img {
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.screenshots-thumbs {
    margin-top: 14px;
    display: flex;
    justify-content: center;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 4px;
    flex: 0 0 auto;
}

#screenshots-modal .product-preview-modal__dialog {
    margin: 0 auto;
    width: 100vw;
    max-width: 100vw;
    height: 100vh;
    max-height: 100vh;
    background: rgba(15, 23, 42, 0.92);
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 0;
}

#screenshots-modal .product-preview-modal__content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 0;
    overflow: hidden;
    padding: 16px;
}

#screenshots-modal .product-preview-modal__overlay {
    background: rgba(2, 6, 23, 0.86);
    backdrop-filter: blur(2px);
}

#screenshots-modal .screenshots-viewer {
    background: rgba(15, 23, 42, 0.45);
    border-color: rgba(148, 163, 184, 0.25);
    border-radius: 14px;
    min-height: 420px;
}

#screenshots-modal .screenshots-viewer img {
    width: auto;
    height: auto;
    max-width: calc(99vw - 80px);
    max-height: calc(97vh - 190px);
    object-fit: contain;
}

#screenshots-modal .screenshots-thumbs {
    margin-top: 12px;
    padding: 8px 6px 6px;
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.58);
}

#screenshots-modal .product-gallery-thumb {
    background: #0f172a;
}

#screenshots-modal .product-preview-modal__dialog.no-thumbs .product-preview-modal__content {
    padding-bottom: 16px;
}

#screenshots-modal .product-preview-modal__dialog.no-thumbs .screenshots-viewer {
    min-height: calc(97vh - 72px);
}

#screenshots-modal .product-preview-modal__dialog.no-thumbs .screenshots-viewer img {
    max-height: calc(97vh - 96px);
}

.product-lightbox-close {
    position: absolute;
    top: 12px;
    right: 12px;
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
    z-index: 5;
    transition: var(--transition);
}

.product-lightbox-close:hover {
    background: rgba(30, 41, 59, 0.96);
    border-color: rgba(99, 102, 241, 0.6);
    color: #ffffff;
}

.dark .product-pricing-card {
    background: #111827;
    border-color: rgba(129, 140, 248, 0.28);
    color: #e2e8f0;
}

.dark .product-description-box {
    border: none;
}

.dark .product-benefit-item {
    color: #cbd5e1;
}

.dark .product-price-large {
    color: #f8fafc;
}

.dark .product-market-title {
    color: #f8fafc;
}

.dark .product-package-select {
    background: #1f2937;
    color: #e5e7eb;
    border-color: rgba(129, 140, 248, 0.35);
}

.dark .product-pricing-head,
.dark .product-market-meta,
.dark .product-market-breadcrumb {
    color: #94a3b8;
}

.dark .product-hero-media {
    background: #0f172a;
    border-color: rgba(129, 140, 248, 0.22);
}

.dark .product-detail-tabs {
    border-bottom-color: rgba(129, 140, 248, 0.2);
}

.dark .product-detail-tabs a {
    color: #94a3b8;
}

.dark .product-detail-tabs a.active {
    color: #818cf8;
    border-bottom-color: #818cf8;
}

.dark .product-faq-panel .faq-item {
    background: transparent;
    border-color: rgba(129, 140, 248, 0.26);
}

.dark .product-custom-tab-panel {
    background: transparent;
    border: none;
}

.dark .product-tab-panel .prose {
    color: #cbd5e1;
}

.dark .product-tab-panel .prose strong,
.dark .product-tab-panel .prose b,
.dark .product-tab-panel .prose h1,
.dark .product-tab-panel .prose h2,
.dark .product-tab-panel .prose h3,
.dark .product-tab-panel .prose h4,
.dark .product-tab-panel .prose h5,
.dark .product-tab-panel .prose h6 {
    color: #f1f5f9;
}

.dark .product-tab-panel .prose li::marker {
    color: #94a3b8;
}

.dark .product-tab-panel .prose a {
    color: #93c5fd;
}

.dark #screenshots-modal .product-preview-modal__dialog {
    background: rgba(2, 6, 23, 0.95);
    border-color: rgba(129, 140, 248, 0.28);
}

.dark #screenshots-modal .screenshots-viewer {
    background: rgba(2, 6, 23, 0.62);
    border-color: rgba(129, 140, 248, 0.3);
}

.dark #screenshots-modal .screenshots-thumbs {
    background: rgba(2, 6, 23, 0.72);
}

.package-select-note {
    font-size: 0.9rem;
    color: #94a3b8;
    font-style: italic;
    margin-top: 8px;
}

@media (max-width: 1100px) {
    .product-detail-layout {
        grid-template-columns: 1fr;
        gap: 32px;
    }

    .product-details {
        position: static;
        top: auto;
    }
}

@media (max-width: 768px) {
    .product-pricing-card {
        padding: 20px;
        border-radius: 14px;
    }

    .product-purchase-actions {
        grid-template-columns: 1fr;
    }

    .product-description-box {
        padding-left: 14px;
    }
}
</style>
@endpush
@endsection
