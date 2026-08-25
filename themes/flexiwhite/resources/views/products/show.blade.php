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
@php
    $showSidebar = theme_get_option('flexiwhite_product_show_sidebar', 'show') === 'show';
@endphp
<div class="container section">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 12px;">
        @include('partials.admin-sidebar-toggle', [
            'settingKey' => 'flexiwhite_product_show_sidebar',
            'showSidebar' => $showSidebar
        ])
    </div>
    <div class="grid-sidebar {{ !$showSidebar ? 'no-sidebar' : '' }}" {!! !$showSidebar ? 'style="grid-template-columns: 1fr;"' : '' !!}>
        
        <!-- Main Content Column -->
        <div>

            <!-- Product Grid Layout (Image left, Summary right) -->
            <div class="single-product-grid">
                    <!-- Product Image Gallery -->
                    <div>
                        @php
                            $displayImage = $product->featured_image_url;
                            $hasGallery = $product->media && $product->media->count() > 0;
                        @endphp

                        @if(!empty($displayImage))
                            <div class="single-product-image-wrap" style="position: relative; {{ $hasGallery ? 'cursor: pointer;' : '' }}" {!! $hasGallery ? 'data-open-screenshots' : '' !!} title="{{ $hasGallery ? _l('Click to view full gallery') : $product->name }}">
                                <img id="main-product-image" src="{{ $displayImage }}" alt="{{ $product->name }}" class="single-product-image" {!! media_lazy_attr() !!}>
                                @php
                                    $quickEditOptions = [
                                        [
                                            'id' => 'edit-product',
                                            'label' => _l('Edit Product'),
                                            'url' => url('/admin/products/' . $product->id . '/edit'),
                                            'icon' => 'fas fa-pencil-alt',
                                        ]
                                    ];
                                    $quickEditOptions = \App\Facades\Hook::applyFilters('product.quick_edit_options', $quickEditOptions, $product);
                                @endphp

                                @if(!empty($quickEditOptions))
                                    @if(count($quickEditOptions) === 1)
                                        <a href="{{ $quickEditOptions[0]['url'] }}" target="_blank" class="admin-quick-edit-btn" title="{{ $quickEditOptions[0]['label'] }}" style="top: 12px; right: 12px; width: 34px; height: 34px;" onclick="event.stopPropagation();">
                                            <i class="{{ $quickEditOptions[0]['icon'] ?? 'fas fa-pencil-alt' }}" style="font-size: 0.85rem;"></i>
                                        </a>
                                    @else
                                        <div class="admin-quick-edit-dropdown" style="position: absolute; top: 12px; right: 12px; z-index: 100;" onclick="event.stopPropagation();">
                                            <button type="button" class="admin-quick-edit-btn" title="{{ _l('Edit Options') }}" style="width: 34px; height: 34px; border: none; cursor: pointer;" onclick="event.stopPropagation(); const m = this.nextElementSibling; m.style.display = m.style.display === 'none' ? 'block' : 'none';">
                                                <i class="fas fa-pencil-alt" style="font-size: 0.85rem;"></i>
                                            </button>
                                            <div class="admin-quick-edit-menu" style="display: none; position: absolute; right: 0; top: 40px; background: #fff; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); min-width: 170px; padding: 6px 0; border: 1px solid #e2e8f0; z-index: 110;">
                                                @foreach($quickEditOptions as $opt)
                                                    <a href="{{ $opt['url'] }}" target="_blank" style="display: flex; align-items: center; gap: 8px; padding: 8px 14px; font-size: 0.85rem; color: #334155; text-decoration: none; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                                        <i class="{{ $opt['icon'] ?? 'fas fa-pencil-alt' }}" style="font-size: 0.8rem; color: #64748b;"></i>
                                                        <span>{{ $opt['label'] }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            
                            @if($product->media && $product->media->count() > 1)
                                <div class="single-product-thumbnails-wrap">
                                    <button type="button" class="single-product-thumb-nav prev" onclick="scrollProductThumbs(-1)" aria-label="{{ _l('Previous image') }}">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <div class="single-product-thumbnails" id="product-thumbnails-scroll">
                                        @foreach($product->media as $index => $media)
                                            <button type="button" 
                                                    onclick="switchMainProductImage('{{ $media->url ?? '' }}', this, {{ $index }})" 
                                                    class="single-product-thumbnail-btn {{ $index === 0 ? 'active' : '' }}" 
                                                    data-media-index="{{ $index }}"
                                                    aria-label="{{ _l('View image') }} {{ $index + 1 }}">
                                                <img src="{{ $media->thumbnail_url ?? $media->url ?? '' }}" alt="{{ $product->name }} thumbnail" class="single-product-thumbnail-img" {!! media_lazy_attr() !!}>
                                            </button>
                                        @endforeach
                                    </div>
                                    <button type="button" class="single-product-thumb-nav next" onclick="scrollProductThumbs(1)" aria-label="{{ _l('Next image') }}">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <script>
                                    window.currentProductMediaIndex = 0;
                                    function switchMainProductImage(url, btn, index) {
                                        const mainImg = document.getElementById('main-product-image');
                                        if (mainImg && url) {
                                            mainImg.style.opacity = '0.7';
                                            mainImg.src = url;
                                            setTimeout(() => { mainImg.style.opacity = '1'; }, 100);
                                        }
                                        const wrap = btn.closest('.single-product-thumbnails-wrap');
                                        if (wrap) {
                                            wrap.querySelectorAll('.single-product-thumbnail-btn').forEach(b => b.classList.remove('active'));
                                            btn.classList.add('active');
                                            
                                            // Smoothly scroll clicked thumbnail into center view
                                            if (typeof btn.scrollIntoView === 'function') {
                                                btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                                            }
                                        }
                                        if (typeof index === 'number') {
                                            window.currentProductMediaIndex = index;
                                        }
                                    }
                                    function scrollProductThumbs(direction) {
                                        const scrollContainer = document.getElementById('product-thumbnails-scroll');
                                        if (!scrollContainer) return;

                                        const activeBtn = scrollContainer.querySelector('.single-product-thumbnail-btn.active');
                                        const allBtns = Array.from(scrollContainer.querySelectorAll('.single-product-thumbnail-btn'));

                                        if (activeBtn && allBtns.length > 0) {
                                            const currentIndex = allBtns.indexOf(activeBtn);
                                            let nextIndex = currentIndex + (direction * 4); // Jump 4 items
                                            if (nextIndex < 0) nextIndex = 0;
                                            if (nextIndex >= allBtns.length) nextIndex = allBtns.length - 1;

                                            if (allBtns[nextIndex]) {
                                                allBtns[nextIndex].click();
                                                return;
                                            }
                                        }

                                        const scrollAmount = 320;
                                        scrollContainer.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
                                    }
                                </script>
                            @endif
                        @else
                            <div class="single-product-no-image">
                                <span>{{ _l('No Image') }}</span>
                            </div>
                        @endif

                        <!-- Product Media Actions (Live Preview, Gallery, Video) -->
                        @php
                            $demoUrl = trim((string) data_get($product->settings, 'demo_url', ''));
                            $demoLabel = trim((string) (data_get($product->settings, 'demo_label', '') ?: data_get($product->settings, 'preview_label', '')));
                            $demoButtonText = !empty($demoLabel) ? _l($demoLabel) : _l('Preview');
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
                                <a href="{{ route('products.preview', ['slug' => $product->slug]) }}" target="_blank" class="media-action-btn media-action-btn--primary">
                                    <i class="fas fa-external-link-alt"></i>
                                    <span>{{ $demoButtonText }}</span>
                                </a>
                            @endif
                            @if($galleryItems->isNotEmpty())
                                <button type="button" data-open-screenshots class="media-action-btn">
                                    <i class="far fa-images"></i>
                                    <span>{{ _l('Gallery') }}</span>
                                </button>
                            @endif
                            @if($previewVideos->isNotEmpty())
                                <button type="button" data-open-videos class="media-action-btn">
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
                            $projectHubProject = null;
                            $projectHubLatestRelease = null;
                            $projectHubFreeRelease = null;
                            $freeDownloadRequiresAuth = true;
                            $freeDownloadUrl = null;
                            $hasWindowsInstaller = false;
                            $hasMacosInstaller = false;
                            $isSalePaused = ($product->isSalesPaused() || $product->stock_status === 'disabled_add_to_cart');
                            $isFreeDownloadDisabled = false;

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

                                $mainProjectHubProject = \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')->whereHas('products', function ($q) use ($productIds) {
                                    $q->withoutGlobalScope('locale')->whereIn('products.id', $productIds);
                                })->where('status', 'published')->first();
                                
                                if ($mainProjectHubProject) {
                                    $projectHubProject = $mainProjectHubProject;
                                    $currentLocale = \Illuminate\Support\Facades\App::getLocale() ?: 'en';
                                    if ($mainProjectHubProject->locale !== $currentLocale && !empty($mainProjectHubProject->translation_group_id)) {
                                        $translatedProject = \Modules\Polyx\ProjectHub\Models\Project::withoutGlobalScope('locale')
                                            ->where('translation_group_id', $mainProjectHubProject->translation_group_id)
                                            ->where('locale', $currentLocale)
                                            ->where('status', 'published')
                                            ->first();
                                        if ($translatedProject) {
                                            $projectHubProject = $translatedProject;
                                        }
                                    }

                                    $projectHubLatestRelease = $projectHubProject->releases()
                                        ->where('status', 'published')
                                        ->orderByDesc('released_at')
                                        ->orderByDesc('id')
                                        ->first();

                                    if (!$projectHubLatestRelease && $mainProjectHubProject->id !== $projectHubProject->id) {
                                        $projectHubLatestRelease = $mainProjectHubProject->releases()
                                            ->where('status', 'published')
                                            ->orderByDesc('released_at')
                                            ->orderByDesc('id')
                                            ->first();
                                    }

                                    $projectHubFreeRelease = $projectHubProject->releases()
                                        ->where('status', 'published')
                                        ->whereNotNull('free_download_url')
                                        ->where('free_download_url', '!=', '')
                                        ->orderByDesc('released_at')
                                        ->orderByDesc('id')
                                        ->first();

                                    if (!$projectHubFreeRelease && $mainProjectHubProject->id !== $projectHubProject->id) {
                                        $projectHubFreeRelease = $mainProjectHubProject->releases()
                                            ->where('status', 'published')
                                            ->whereNotNull('free_download_url')
                                            ->where('free_download_url', '!=', '')
                                            ->orderByDesc('released_at')
                                            ->orderByDesc('id')
                                            ->first();
                                    }
                                        
                                    $freeDownloadRequiresAuth = (data_get($projectHubProject->settings, 'free_download_requires_auth', true) !== false);
                                    $disableFreeOnPaused = (data_get($projectHubProject->settings, 'disable_free_download_on_sale_paused', true) !== false);
                                    $isFreeDownloadDisabled = ($isSalePaused && $disableFreeOnPaused);
                                    
                                    if ($projectHubFreeRelease) {
                                        $freeDownloadUrl = $projectHubFreeRelease->free_download_url;
                                        if ($freeDownloadUrl && (str_starts_with($freeDownloadUrl, 'http://') || str_starts_with($freeDownloadUrl, 'https://'))) {
                                            $parsedUrl = parse_url($freeDownloadUrl);
                                            if (isset($parsedUrl['path']) && str_starts_with($parsedUrl['path'], '/storage/')) {
                                                $freeDownloadUrl = $parsedUrl['path'];
                                            }
                                        }
                                    }

                                    $hasWindowsInstaller = !empty($projectHubLatestRelease?->installer_windows_url);
                                    $hasMacosInstaller = !empty($projectHubLatestRelease?->installer_macos_url);
                                }
                            }

                            // Query Available Coupons for this product
                            $availableCoupons = collect();
                            if (class_exists('\App\Models\Ecommerce\ProductCoupon')) {
                                try {
                                    $now = \Carbon\Carbon::now();
                                    $productCategoryIds = $product->categories ? $product->categories->pluck('id')->toArray() : [];
                                    
                                    $availableCoupons = \App\Models\Ecommerce\ProductCoupon::where('is_active', true)
                                        ->where(function ($q) use ($now) {
                                            $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                                        })
                                        ->where(function ($q) use ($now) {
                                            $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
                                        })
                                        ->where(function ($q) {
                                            $q->whereNull('usage_limit')->orWhereRaw('usage_count < usage_limit');
                                        })
                                        ->get()
                                        ->filter(function ($coupon) use ($product, $productCategoryIds) {
                                            $scope = $coupon->scope_config ?? [];
                                            
                                            // Excluded products check
                                            if (!empty($scope['excluded_product_ids']) && is_array($scope['excluded_product_ids'])) {
                                                if (in_array((int)$product->id, array_map('intval', $scope['excluded_product_ids']), true)) {
                                                    return false;
                                                }
                                            }
                                            
                                            $allowedProductIds = !empty($scope['product_ids']) && is_array($scope['product_ids']) ? array_map('intval', $scope['product_ids']) : [];
                                            $allowedCategoryIds = !empty($scope['category_ids']) && is_array($scope['category_ids']) ? array_map('intval', $scope['category_ids']) : [];
                                            
                                            // If both are empty, coupon applies storewide
                                            if (empty($allowedProductIds) && empty($allowedCategoryIds)) {
                                                return true;
                                            }
                                            
                                            // If specific products defined
                                            if (!empty($allowedProductIds) && in_array((int)$product->id, $allowedProductIds, true)) {
                                                return true;
                                            }
                                            
                                            // If specific categories defined
                                            if (!empty($allowedCategoryIds) && count(array_intersect($productCategoryIds, $allowedCategoryIds)) > 0) {
                                                return true;
                                            }
                                            
                                            return false;
                                        })->values();
                                } catch (\Throwable $e) {}
                            }

                            $bestDiscountText = '';
                            $maxPercent = 0;
                            $maxFixed = 0;
                            foreach ($availableCoupons as $c) {
                                if ($c->type === 'percent' && (float)$c->value > $maxPercent) {
                                    $maxPercent = (float)$c->value;
                                } elseif ($c->type === 'fixed_amount' && (float)$c->value > $maxFixed) {
                                    $maxFixed = (float)$c->value;
                                }
                            }
                            if ($maxPercent > 0) {
                                $bestDiscountText = $maxPercent >= 100 ? _l('100% OFF') : _l('Save up to :percent%', ['percent' => (int)$maxPercent]);
                            } elseif ($maxFixed > 0) {
                                $bestDiscountText = _l('Save up to :amount', ['amount' => format_currency($maxFixed)]);
                            }

                            $mainProductPrice = (float) ($product->price ?? 0);
                            $isCommerceOffersActive = class_exists(\App\Services\ModuleManager::class) 
                                && app(\App\Services\ModuleManager::class)->isModuleEnabled('Polyx.CommerceOffers');

                            $mainOfferPrice = $mainProductPrice;
                            if ($isCommerceOffersActive && class_exists(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class)) {
                                try {
                                    $offersService = app(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class);
                                    $mainOfferPrice = $offersService->calculateEffectivePrice($product, $mainProductPrice);
                                } catch (\Throwable $e) {}
                            }

                            $offerRatio = ($mainProductPrice > 0 && $mainOfferPrice > 0 && $mainOfferPrice < $mainProductPrice)
                                ? ($mainOfferPrice / $mainProductPrice)
                                : 1.0;
                            
                            if ($product->services && $product->services->isNotEmpty()) {
                                $firstService = $product->services->first();
                                $rawFirstPrice = (float) ($firstService->price ?? $mainProductPrice);
                                if ($offerRatio < 1.0) {
                                    $currentPrice = round($rawFirstPrice * $offerRatio, 2);
                                    $hasSale = true;
                                    $strikePrice = $rawFirstPrice;
                                } else {
                                    $currentPrice = $rawFirstPrice;
                                    $hasSale = false;
                                    $strikePrice = null;
                                }
                            } else {
                                $salePrice = (float) ($product->sale_price ?? 0);
                                $effectivePrice = (float) $product->effective_price;
                                $hasSale = ($salePrice > 0 && $salePrice < $mainProductPrice) || ($effectivePrice > 0 && $effectivePrice < $mainProductPrice);
                                $currentPrice = ($effectivePrice > 0 && $effectivePrice < $mainProductPrice) ? $effectivePrice : (($salePrice > 0 && $salePrice < $mainProductPrice) ? $salePrice : $mainProductPrice);
                                $strikePrice = $hasSale ? $mainProductPrice : null;
                            }
                        @endphp
                        <div class="single-product-price-row">
                            @if($hasSale)
                                <span class="product-price single-product-price">{{ format_currency($currentPrice) }}</span>
                                <span class="product-price-strike single-product-price-strike">{{ format_currency($strikePrice) }}</span>
                                @if($mainOfferPrice > 0 && $mainOfferPrice < $mainProductPrice)
                                    <span class="badge" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; font-weight: 700;">{{ _l('Offer Deal') }}</span>
                                @else
                                    <span class="badge">{{ _l('Sale') }}</span>
                                @endif
                            @else
                                <span class="product-price single-product-price">{{ format_currency($currentPrice) }}</span>
                            @endif
                        </div>

                        @if($availableCoupons->isNotEmpty())
                            <div class="product-coupons-trigger" onclick="openProductCouponsModal()" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 8px; width: 100%; max-width: 360px; margin-top: 8px; margin-bottom: 10px; padding: 8px 12px; background: rgba(248, 250, 252, 0.9); border: 1px solid rgba(226, 232, 240, 0.95); border-radius: 8px; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); box-sizing: border-box; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background: #0f172a; color: #fff; font-size: 11px; flex-shrink: 0;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    </span>
                                    <div style="line-height: 1.25; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <span style="font-weight: 700; font-size: 0.8125rem; color: #0f172a;" class="coupons-trigger-title">{{ _l('Verified Coupons & Special Deals') }}</span>
                                        @if($bestDiscountText)
                                            <span style="display: inline-block; margin-left: 4px; font-size: 0.6875rem; font-weight: 700; background: #0f172a; color: #fff; padding: 1px 6px; border-radius: 4px; font-family: ui-monospace, monospace;">{{ $bestDiscountText }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 4px; font-size: 0.75rem; font-weight: 600; color: #64748b; white-space: nowrap; flex-shrink: 0;">
                                    <span>{{ $availableCoupons->count() }} {{ _l('deals') }}</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        @endif

                        {!! \App\Facades\Hook::doAction('theme.product.single.after_price', $product) !!}

                        @if(($hasWindowsInstaller || $hasMacosInstaller) && !$isSalePaused)
                            <div class="desktop-installers-wrapper" style="width: 100%; max-width: 360px; margin-top: 10px; margin-bottom: 12px; display: grid; grid-template-columns: {{ ($hasWindowsInstaller && $hasMacosInstaller) ? 'repeat(2, minmax(0, 1fr))' : '1fr' }}; gap: 8px;">
                                @if($hasWindowsInstaller)
                                    <a href="{{ route('projects.download-installer', ['release' => $projectHubLatestRelease->id, 'platform' => 'windows']) }}" 
                                       class="btn btn-download-windows" 
                                       style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 10px 8px; font-weight: 600; font-size: 0.8125rem; border-radius: 6px; background-color: #0284c7; color: #fff; transition: all 0.2s ease; border: 1px solid #0284c7; text-decoration: none; white-space: nowrap;"
                                       onmouseover="this.style.backgroundColor='#0369a1'; this.style.borderColor='#0369a1'"
                                       onmouseout="this.style.backgroundColor='#0284c7'; this.style.borderColor='#0284c7'">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 6px; display: inline-block; vertical-align: middle; flex-shrink: 0;">
                                            <path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.901-1.802"/>
                                        </svg>
                                        {{ _l('Download Windows') }}
                                    </a>
                                @endif

                                @if($hasMacosInstaller)
                                    <a href="{{ route('projects.download-installer', ['release' => $projectHubLatestRelease->id, 'platform' => 'macos']) }}" 
                                       class="btn btn-download-macos" 
                                       style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 10px 8px; font-weight: 600; font-size: 0.8125rem; border-radius: 6px; background-color: #334155; color: #fff; transition: all 0.2s ease; border: 1px solid #334155; text-decoration: none; white-space: nowrap;"
                                       onmouseover="this.style.backgroundColor='#1e293b'; this.style.borderColor='#1e293b'"
                                       onmouseout="this.style.backgroundColor='#334155'; this.style.borderColor='#334155'">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 6px; display: inline-block; vertical-align: middle; flex-shrink: 0;">
                                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.34-.55.63-.99 1.68-.87 2.7.99.08 2.01-.5 2.56-1.19z"/>
                                        </svg>
                                        {{ _l('Download macOS') }}
                                    </a>
                                @endif
                            </div>
                        @elseif($projectHubFreeRelease && !$isFreeDownloadDisabled)
                            <!-- Free Download Button (Visible when logged in OR when auth is not required) -->
                            <div id="free-download-button-wrapper" style="width: 100%; max-width: 360px; margin-top: 10px; margin-bottom: 12px; box-sizing: border-box; display: {{ (!$freeDownloadRequiresAuth || auth()->check()) ? 'block' : 'none' }};">
                                <a id="free-download-link"
                                   href="{{ route('projects.download-free', $projectHubFreeRelease->id) }}" 
                                   class="btn" 
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 10px 8px; font-weight: 600; font-size: 0.8125rem; border-radius: 6px; background-color: #10b981; color: #fff; transition: all 0.2s ease; border: 1px solid #10b981; text-decoration: none;"
                                   onmouseover="this.style.backgroundColor='#059669'; this.style.borderColor='#059669'"
                                   onmouseout="this.style.backgroundColor='#10b981'; this.style.borderColor='#10b981'">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px; display: inline-block; vertical-align: middle;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    {{ _l('Download Free Version') }}
                                </a>
                            </div>

                            <!-- Register/Login Button (Visible only when auth is required AND user is guest) -->
                            @if($freeDownloadRequiresAuth && !auth()->check())
                                <div id="free-login-button-wrapper" style="width: 100%; max-width: 360px; margin-top: 10px; margin-bottom: 12px; box-sizing: border-box; display: block;">
                                    <a href="{{ route('register') }}?redirect={{ urlencode(request()->fullUrl()) }}" 
                                       class="btn btn-secondary" 
                                       style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 10px 8px; font-weight: 600; font-size: 0.8125rem; border-radius: 6px; text-decoration: none; border-style: solid; border-width: 1px;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px; display: inline-block; vertical-align: middle;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                        {{ _l('Register / Login to Download Free') }}
                                    </a>
                                </div>
                            @endif
                        @endif

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

                                $mainOfferPrice = $baseRefPrice;
                                if ($isCommerceOffersActive && class_exists(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class)) {
                                    try {
                                        $offersService = app(\Modules\Polyx\CommerceOffers\Services\CommerceOffersService::class);
                                        $mainOfferPrice = $offersService->calculateEffectivePrice($product, $baseRefPrice);
                                    } catch (\Throwable $e) {}
                                }

                                $offerRatio = ($baseRefPrice > 0 && $mainOfferPrice > 0 && $mainOfferPrice < $baseRefPrice)
                                    ? ($mainOfferPrice / $baseRefPrice)
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

                                $getActivationCount = function($svc) {
                                    if (!$svc) return 1;
                                    $policy = $svc->license_policy;
                                    if (is_array($policy)) {
                                        if (!empty($policy['max_activations'])) return (int) $policy['max_activations'];
                                        if (!empty($policy['limit'])) return (int) $policy['limit'];
                                        if (!empty($policy['seats'])) return (int) $policy['seats'];
                                    }
                                    if (!empty($svc->capabilities) && is_array($svc->capabilities)) {
                                        foreach ($svc->capabilities as $cap) {
                                            if (preg_match('/(\d+)\s*(site|domain|app|key|license)/i', (string) $cap, $m)) {
                                                return (int) $m[1];
                                            }
                                        }
                                    }
                                    return 1;
                                };

                                $monthlyService = $product->services->first(function($s) {
                                    return ($s->access_type === 'subscription' && strtolower($s->duration_unit ?? '') === 'month')
                                        || str_contains(strtolower($s->code ?? ''), 'month')
                                        || str_contains(strtolower($s->name ?? ''), 'month');
                                });

                                $yearlyService = $product->services->first(function($s) {
                                    return ($s->access_type === 'subscription' && (in_array(strtolower($s->duration_unit ?? ''), ['year', 'annual']) || empty($s->duration_unit)))
                                        || str_contains(strtolower($s->code ?? ''), 'year')
                                        || str_contains(strtolower($s->name ?? ''), 'year')
                                        || ($s->access_type === 'subscription' && strtolower($s->duration_unit ?? '') !== 'month');
                                });

                                $lifetimeService = $product->services->first(function($s) {
                                    return in_array($s->access_type, ['lifetime', 'permanent'])
                                        || str_contains(strtolower($s->code ?? ''), 'lifetime')
                                        || str_contains(strtolower($s->name ?? ''), 'lifetime')
                                        || strtolower($s->duration_unit ?? '') === 'lifetime';
                                });

                                $yearlyPrice = $yearlyService ? (float) ($yearlyService->price ?? 0) : 0;
                                $lifetimePrice = $lifetimeService ? (float) ($lifetimeService->price ?? 0) : 0;
                                $monthlyPrice = $monthlyService ? (float) ($monthlyService->price ?? 0) : 0;

                                $yearlyKeys = $getActivationCount($yearlyService);
                                $lifetimeKeys = $getActivationCount($lifetimeService);

                                $settingsService = app(\App\Services\SettingsService::class);
                                $savingsText = null;

                                if ($lifetimeService && $yearlyService && $yearlyPrice > 0 && $lifetimePrice > 0) {
                                    if ($lifetimeKeys > $yearlyKeys) {
                                        $years = 2;
                                        $cost = $yearlyPrice * $lifetimeKeys * $years;
                                        $savings = max(0, $cost - $lifetimePrice);
                                        if ($savings > 0) {
                                            $savingsFormatted = format_currency($savings);
                                            $customCta = $settingsService->get('ecommerce_savings_cta_lifetime');
                                            if ($customCta && !str_contains($customCta, 'monthly')) {
                                                $savingsText = str_replace(
                                                    [':amount', ':years', ':keys'],
                                                    [$savingsFormatted, $years, $lifetimeKeys],
                                                    $customCta
                                                );
                                            } else {
                                                $savingsText = _l('Save :amount+ over :years yrs across :keys keys vs yearly renewal', [
                                                    'amount' => $savingsFormatted,
                                                    'years' => $years,
                                                    'keys' => $lifetimeKeys,
                                                ]);
                                            }
                                        }
                                    } else {
                                        $years = 3;
                                        $cost = $yearlyPrice * $years;
                                        $savings = max(0, $cost - $lifetimePrice);
                                        if ($savings > 0) {
                                            $savingsFormatted = format_currency($savings);
                                            $customCta = $settingsService->get('ecommerce_savings_cta_lifetime_single') ?: $settingsService->get('ecommerce_savings_cta_lifetime');
                                            if ($customCta && !str_contains($customCta, 'monthly') && str_contains($customCta, ':years')) {
                                                $savingsText = str_replace(
                                                    [':amount', ':years', ':keys'],
                                                    [$savingsFormatted, $years, $lifetimeKeys],
                                                    $customCta
                                                );
                                            } else {
                                                $savingsText = _l('Save :amount+ over :years yrs vs yearly renewal • Pay once, use forever', [
                                                    'amount' => $savingsFormatted,
                                                    'years' => $years,
                                                ]);
                                            }
                                        }
                                    }
                                } elseif ($lifetimeService && $monthlyService && $monthlyPrice > 0 && $lifetimePrice > 0) {
                                    $years = 2;
                                    $cost = $monthlyPrice * 12 * $years;
                                    $savings = max(0, $cost - $lifetimePrice);
                                    if ($savings > 0) {
                                        $savingsFormatted = format_currency($savings);
                                        $savingsText = _l('Save :amount+ over :years yrs vs monthly renewal • Pay once, use forever', [
                                            'amount' => $savingsFormatted,
                                            'years' => $years,
                                        ]);
                                    }
                                }

                                // Apply Hook Filter so CommerceOffers module can override/optimize the savings text
                                $savingsText = \App\Facades\Hook::applyFilters('commerceoffers.package_savings_cta', $savingsText, [
                                    'product' => $product,
                                    'yearly_service' => $yearlyService,
                                    'lifetime_service' => $lifetimeService,
                                    'monthly_service' => $monthlyService,
                                    'yearly_price' => $yearlyPrice,
                                    'lifetime_price' => $lifetimePrice,
                                    'monthly_price' => $monthlyPrice,
                                    'yearly_keys' => $yearlyKeys,
                                    'lifetime_keys' => $lifetimeKeys,
                                ]);
                            @endphp

                                <div class="product-packages-selector mb-3" style="margin-top: 6px; margin-bottom: 12px; width: 100%;">
                                    <label class="block text-xs font-bold text-gray-700 dark:text-zinc-300 mb-1.5" style="font-weight: 700; margin-bottom: 6px; display: block; letter-spacing: 0.02em;">{{ _l('Choose a Package / Plan:') }}</label>
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        @foreach($product->services as $index => $service)
                                            @php 
                                                $rawServicePrice = (float) ($service->price ?? $baseRefPrice);
                                                $serviceOfferPrice = $rawServicePrice;
                                                $hasServiceSale = false;

                                                if ($isCommerceOffersActive && $offerRatio < 1.0) {
                                                    $serviceOfferPrice = round($rawServicePrice * $offerRatio, 2);
                                                    $hasServiceSale = $serviceOfferPrice < $rawServicePrice;
                                                }

                                                $serviceTiers = [];
                                                foreach ($tierRatios as $ratio) {
                                                    $serviceTiers[] = format_currency(round($rawServicePrice * $ratio, 2));
                                                }

                                                $serviceKeys = $getActivationCount($service);
                                                $isLifetime = in_array($service->access_type, ['lifetime', 'permanent']);
                                                $isSubscription = $service->access_type === 'subscription';
                                            @endphp
                                            <label class="package-option-label {{ $index === 0 ? 'is-selected' : '' }}">
                                                <input type="radio" name="selected_service_id" value="{{ $service->id }}" 
                                                       data-price="{{ $serviceOfferPrice }}" 
                                                       data-price-text="{{ format_currency($serviceOfferPrice) }}"
                                                       data-raw-price="{{ $rawServicePrice }}"
                                                       data-raw-price-text="{{ format_currency($rawServicePrice) }}"
                                                       data-has-sale="{{ $hasServiceSale ? '1' : '0' }}"
                                                       data-tiers="{{ json_encode($serviceTiers) }}"
                                                       data-name="{{ $service->name }}"
                                                       data-billing="{{ $isSubscription ? ($service->duration_value . ' ' . $service->duration_unit . ($service->duration_value > 1 ? 's' : '')) : 'Lifetime' }}"
                                                       {{ $index === 0 ? 'checked' : '' }} 
                                                       style="margin-top: 2px; accent-color: var(--primary, #3b82f6);"
                                                       onchange="updateSelectedPackage(this)">
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 1px;">
                                                        <span class="package-name-text">{{ _l($service->name) }}</span>
                                                        <div style="display: flex; align-items: baseline; gap: 6px; flex-shrink: 0;">
                                                            <span class="package-price-text">{{ format_currency($serviceOfferPrice) }}</span>
                                                            @if($hasServiceSale)
                                                                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.78rem; font-weight: 500;">{{ format_currency($rawServicePrice) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="package-meta-row">
                                                        <span class="package-key-info">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z" />
                                                            </svg>
                                                            <span>
                                                                @if($serviceKeys > 1)
                                                                    {{ _l(':count Keys (:count Sites / Apps)', ['count' => $serviceKeys]) }}
                                                                @else
                                                                    {{ _l('1 License Key (1 Site / App)') }}
                                                                @endif
                                                            </span>
                                                        </span>
                                                        <span class="package-meta-dot">•</span>
                                                        <span class="package-duration-info">
                                                            @if($isSubscription)
                                                                {{ $service->duration_value }} {{ _l(ucfirst($service->duration_unit) . ($service->duration_value > 1 ? 's' : '')) }}
                                                                @if($service->is_recurring)
                                                                    ({{ _l('Auto-renew') }})
                                                                @endif
                                                            @else
                                                                {{ _l('Lifetime / Never Expires') }}
                                                            @endif
                                                            
                                                            @if($service->trial_period_days > 0)
                                                                <span style="margin-left: 4px; color: #10b981; font-weight: 600;">+{{ $service->trial_period_days }}d trial</span>
                                                            @endif
                                                        </span>
                                                    </div>

                                                    @php
                                                        $serviceSavingsText = null;
                                                        if ($isSubscription && $monthlyService && $monthlyPrice > 0 && $rawServicePrice > 0 && $service->id === $yearlyService?->id) {
                                                            $annualMonthlyCost = $monthlyPrice * 12;
                                                            $yearlySavings = max(0, $annualMonthlyCost - $rawServicePrice);
                                                            if ($yearlySavings > 0) {
                                                                $yearlySavingsFormatted = format_currency($yearlySavings);
                                                                $customCta = $settingsService->get('ecommerce_savings_cta_yearly');
                                                                if ($customCta && str_contains($customCta, 'monthly')) {
                                                                    $serviceSavingsText = str_replace(
                                                                        [':amount', ':keys'],
                                                                        [$yearlySavingsFormatted, $serviceKeys],
                                                                        $customCta
                                                                    );
                                                                } else {
                                                                    $serviceSavingsText = _l('Save :amount+ per year vs monthly renewal', [
                                                                        'amount' => $yearlySavingsFormatted,
                                                                    ]);
                                                                }
                                                            }
                                                        } elseif ($isLifetime && !empty($savingsText)) {
                                                            $serviceSavingsText = $savingsText;
                                                        }

                                                        $serviceSavingsText = \App\Facades\Hook::applyFilters('commerceoffers.service_savings_cta', $serviceSavingsText, [
                                                            'service' => $service,
                                                            'product' => $product,
                                                            'monthly_service' => $monthlyService,
                                                            'yearly_service' => $yearlyService,
                                                            'lifetime_service' => $lifetimeService,
                                                        ]);
                                                    @endphp

                                                    @if(!empty($serviceSavingsText))
                                                        <div class="lifetime-savings-nudge">
                                                            <span>{{ $serviceSavingsText }}</span>
                                                        </div>
                                                    @endif

                                                    @if(!empty($service->capabilities))
                                                        <div class="package-capabilities-wrap">
                                                            @foreach($service->capabilities as $capKey => $capVal)
                                                                <div class="package-cap-item">
                                                                    <i class="fa fa-check"></i>
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
                            
                            <!-- Script to update styles and price on radio change -->
                            <script>
                                function updateSelectedPackage(radio) {
                                    // Update all label classes
                                    document.querySelectorAll('.package-option-label').forEach(label => {
                                        label.classList.remove('is-selected');
                                    });
                                    
                                    const activeLabel = radio.closest('.package-option-label');
                                    if (activeLabel) {
                                        activeLabel.classList.add('is-selected');
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
                                
                                // Initial package selection sync
                                document.addEventListener('DOMContentLoaded', () => {
                                    const checkedRadio = document.querySelector('input[name="selected_service_id"]:checked');
                                    if (checkedRadio) {
                                        updateSelectedPackage(checkedRadio);
                                    }
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

                        <div class="single-product-actions product-purchase-grid" style="width: 100%; max-width: 360px; justify-content: flex-start; align-items: stretch; box-sizing: border-box;">
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
                                            <span class="ext-btn-price" style="margin-left: auto; opacity: 0.85; font-size: 0.85rem;">{{ format_currency(data_get($extBtn, 'price')) }}</span>
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

                        @if($projectHubFreeRelease && !$isFreeDownloadDisabled && !($hasWindowsInstaller || $hasMacosInstaller))
                            <!-- Client-side authentication check script (covers both web session & Sanctum localstorage) -->
                            <script>
                                (function() {
                                    function checkClientAuth() {
                                        const isFreeDisabled = {{ ($isFreeDownloadDisabled || $hasWindowsInstaller || $hasMacosInstaller) ? 'true' : 'false' }};
                                        const downloadBtn = document.getElementById('free-download-button-wrapper');
                                        const loginBtn = document.getElementById('free-login-button-wrapper');
                                        
                                        if (isFreeDisabled) {
                                            if (loginBtn) loginBtn.style.display = 'none';
                                            if (downloadBtn) downloadBtn.style.display = 'none';
                                            return;
                                        }

                                        const authToken = localStorage.getItem('auth_token');
                                        const hasAuthToken = !!authToken;
                                        
                                        if (hasAuthToken) {
                                            if (loginBtn) loginBtn.style.display = 'none';
                                            if (downloadBtn) {
                                                downloadBtn.style.display = 'block';
                                                const downloadLink = document.getElementById('free-download-link');
                                                if (downloadLink) {
                                                    let url = '{{ route('projects.download-free', $projectHubFreeRelease->id) }}';
                                                    url += (url.includes('?') ? '&' : '?') + 'auth_token=' + encodeURIComponent(authToken);
                                                    downloadLink.setAttribute('href', url);
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
                if (!empty($hasProductDocumentationTab) && !empty($productDocumentationPosts) && $productDocumentationPosts->isNotEmpty()) {
                    $docTitle = data_get($product->settings, 'documentation.title');
                    if (empty(trim((string)$docTitle))) {
                        $docTitle = _l('Documentation');
                    }
                    $tabs->push(['id' => 'documentation', 'title' => $docTitle, 'type' => 'documentation']);
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
                    } elseif ($configuredDefaultTabId === 'documentation' && $tabs->contains(fn ($tab) => $tab['id'] === 'documentation')) {
                        $defaultTabId = 'documentation';
                    } elseif (!empty($matchedCustom['id']) && $tabs->contains(fn ($tab) => $tab['id'] === $matchedCustom['id'])) {
                        $defaultTabId = $matchedCustom['id'];
                    }
                }
            @endphp
            @if($tabs->isNotEmpty())
                {!! \App\Facades\Hook::doAction('theme.product.single.before_details', $product) !!}
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

                        @if(!empty($hasProductDocumentationTab) && !empty($productDocumentationPosts) && $productDocumentationPosts->isNotEmpty())
                            @php
                                $docLayout = data_get($product->settings, 'documentation.display_layout', 'grid');
                                $docCatName = $productDocumentationCategory->name ?? null;
                                $docCatUrl = $productDocumentationCategory->frontend_url ?? null;
                                $getDocPlainText = function ($p) {
                                    if (!empty($p->content_html) && is_string($p->content_html)) {
                                        return strip_tags($p->content_html);
                                    }
                                    if (!empty($p->content_raw) && is_string($p->content_raw)) {
                                        return strip_tags($p->content_raw);
                                    }
                                    if (!empty($p->excerpt) && is_string($p->excerpt)) {
                                        return strip_tags($p->excerpt);
                                    }
                                    return '';
                                };
                            @endphp
                            <div id="documentation" class="single-product-tab-panel">
                                <div class="product-doc-container">
                                    <div class="product-doc-header">
                                        <div class="product-doc-header__info">
                                            <div class="product-doc-header__badge">
                                                <i class="fas fa-book-open"></i>
                                                <span>{{ $docCatName ?? _l('Guides & Documentation') }}</span>
                                            </div>
                                            <h3 class="product-doc-header__title">{{ data_get($product->settings, 'documentation.title') ?: _l('Documentation & Resources') }}</h3>
                                            <p class="product-doc-header__subtitle">
                                                {{ _l('Explore detailed tutorials, setup guides, and reference articles for :product.', ['product' => $product->name]) }}
                                            </p>
                                        </div>
                                        @if($docCatUrl)
                                            <a href="{{ $docCatUrl }}" class="product-doc-header__viewall" target="_blank">
                                                <span>{{ _l('View All Docs') }}</span>
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        @endif
                                    </div>

                                    @if($docLayout === 'list')
                                        <div class="product-doc-list">
                                            @foreach($productDocumentationPosts as $post)
                                                @php
                                                    $plainText = $getDocPlainText($post);
                                                    $words = str_word_count($plainText);
                                                    $readingTime = max(1, (int) ceil($words / 200));
                                                    $postExcerpt = (!empty($post->excerpt) && is_string($post->excerpt)) ? $post->excerpt : \Illuminate\Support\Str::limit($plainText, 140);
                                                @endphp
                                                <a href="{{ $post->frontend_url }}" class="product-doc-list-item">
                                                    <div class="product-doc-list-item__icon">
                                                        <i class="far fa-file-alt"></i>
                                                    </div>
                                                    <div class="product-doc-list-item__content">
                                                        <h4 class="product-doc-list-item__title">{{ $post->title }}</h4>
                                                        @if($postExcerpt)
                                                            <p class="product-doc-list-item__excerpt">{{ $postExcerpt }}</p>
                                                        @endif
                                                        <div class="product-doc-list-item__meta">
                                                            @if($post->published_at)
                                                                <span><i class="far fa-calendar-alt"></i> {{ $post->published_at->format('M d, Y') }}</span>
                                                            @endif
                                                            <span><i class="far fa-clock"></i> {{ $readingTime }} {{ _l('min read') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="product-doc-list-item__arrow">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif($docLayout === 'accordion')
                                        @php
                                            $docAccordionItems = $productDocumentationPosts->map(function ($post) use ($getDocPlainText) {
                                                $plainText = $getDocPlainText($post);
                                                $excerpt = (!empty($post->excerpt) && is_string($post->excerpt)) ? $post->excerpt : \Illuminate\Support\Str::limit($plainText, 200);
                                                $link = '<div style="margin-top: 12px;"><a href="' . e($post->frontend_url) . '" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">' . e(_l('Read Full Guide')) . ' &rarr;</a></div>';
                                                return [
                                                    'title' => (string) $post->title,
                                                    'description' => '<div class="prose" style="font-size: 0.95rem; line-height: 1.6;">' . e($excerpt) . $link . '</div>',
                                                    'open' => false,
                                                    'is_html' => true,
                                                ];
                                            })->values()->all();
                                        @endphp
                                        <div class="product-doc-accordion">
                                            <x-accordion :items="$docAccordionItems" style="standard" />
                                        </div>
                                    @else
                                        {{-- Default: Grid Cards --}}
                                        <div class="product-doc-grid">
                                            @foreach($productDocumentationPosts as $post)
                                                @php
                                                    $plainText = $getDocPlainText($post);
                                                    $words = str_word_count($plainText);
                                                    $readingTime = max(1, (int) ceil($words / 200));
                                                    $postExcerpt = (!empty($post->excerpt) && is_string($post->excerpt)) ? $post->excerpt : \Illuminate\Support\Str::limit($plainText, 120);
                                                    $thumbnail = $post->featured_image_url;
                                                @endphp
                                                <a href="{{ $post->frontend_url }}" class="product-doc-card">
                                                    @if($thumbnail)
                                                        <div class="product-doc-card__thumb">
                                                            <img src="{{ $thumbnail }}" alt="{{ $post->title }}" {!! media_lazy_attr() !!}>
                                                        </div>
                                                    @endif
                                                    <div class="product-doc-card__body">
                                                        <div class="product-doc-card__top">
                                                            @if($post->categories->isNotEmpty())
                                                                <span class="product-doc-card__tag">{{ $post->categories->first()->name }}</span>
                                                            @endif
                                                            <span class="product-doc-card__reading"><i class="far fa-clock"></i> {{ $readingTime }} {{ _l('min read') }}</span>
                                                        </div>
                                                        <h4 class="product-doc-card__title">{{ $post->title }}</h4>
                                                        @if($postExcerpt)
                                                            <p class="product-doc-card__excerpt">{{ $postExcerpt }}</p>
                                                        @endif
                                                        <div class="product-doc-card__footer">
                                                            <span class="product-doc-card__date">
                                                                @if($post->published_at)
                                                                    {{ $post->published_at->format('M d, Y') }}
                                                                @endif
                                                            </span>
                                                            <span class="product-doc-card__cta">
                                                                {{ _l('Read Guide') }} <i class="fas fa-arrow-right"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
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

        <aside style="{{ !$showSidebar ? 'display: none;' : '' }}">
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

    {!! \App\Facades\Hook::applyFilters('theme.product.single.after_content', '', $product) !!}
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
    .media-action-btn--primary {
        background: #0070f3 !important;
        color: #ffffff !important;
        border-color: transparent !important;
    }
    .media-action-btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .media-action-btn--primary:hover {
        background: #0051cb !important;
        color: #ffffff !important;
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

            // Auto scroll modal active thumbnail into view
            if (thumbs[active] && typeof thumbs[active].scrollIntoView === 'function') {
                thumbs[active].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }
        };

        const openModal = (modal, targetIndex) => {
            if (!modal) return;
            const initialIdx = (typeof targetIndex === 'number') ? targetIndex : (window.currentProductMediaIndex || 0);
            setScreenshot(initialIdx);
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
            btn.addEventListener('click', (e) => {
                if (e.target.closest('.admin-quick-edit-dropdown') || e.target.closest('.admin-quick-edit-btn')) {
                    return;
                }
                openModal(screenshotsModal, window.currentProductMediaIndex || 0);
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

@if(isset($availableCoupons) && $availableCoupons->isNotEmpty())
    <!-- Verified Coupons & Special Deals Popup Modal (Vercel Style) -->
    <div id="product-coupons-modal" style="display: none; position: fixed; inset: 0; z-index: 999999; align-items: center; justify-content: center; padding: 16px; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(6px);">
        <div class="product-coupons-modal-card" style="width: 100%; max-width: 520px; max-height: 88vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #e2e8f0; animation: polyModalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
            
            <!-- Modal Header -->
            <div style="padding: 20px 24px 16px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; background: #ffffff;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 2px 8px; border-radius: 9999px; border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                        {{ _l('Verified Offers') }}
                    </div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">
                        {{ _l('Verified Coupons & Special Deals') }}
                    </h3>
                    <p style="margin: 4px 0 0; font-size: 0.8125rem; color: #64748b; line-height: 1.45;">
                        {{ _l('Apply verified promo codes at checkout to save instantly on') }} <strong>{{ $product->name }}</strong>.
                    </p>
                </div>
                <button type="button" onclick="closeProductCouponsModal()" style="background: none; border: none; font-size: 20px; line-height: 1; color: #94a3b8; cursor: pointer; padding: 6px 8px; border-radius: 8px; transition: all 0.15s ease;" onmouseover="this.style.color='#0f172a'; this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.color='#94a3b8'; this.style.backgroundColor='transparent'">✕</button>
            </div>

            <!-- Modal Body (List of Verified Coupon Cards - Vercel DNA) -->
            <div style="padding: 20px 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; flex: 1; background: #f8fafc;">
                @foreach($availableCoupons as $coupon)
                    @php
                        $valFormatted = $coupon->type === 'percent' 
                            ? ((float)$coupon->value >= 100 ? _l('100% OFF') : ((int)$coupon->value . '% OFF'))
                            : format_currency((float)$coupon->value) . ' ' . _l('OFF');
                        $isSpecific = !empty($coupon->scope_config['product_ids']) && in_array((int)$product->id, array_map('intval', $coupon->scope_config['product_ids']));
                        $minSpendText = (float)$coupon->min_order_value > 0 ? _l('Min spend: :val', ['val' => format_currency((float)$coupon->min_order_value)]) : _l('No minimum spend');
                        $expiryText = $coupon->expires_at ? _l('Expires: :date', ['date' => $coupon->expires_at->format('M d, Y')]) : _l('No expiration');
                    @endphp
                    <div class="product-coupon-ticket" style="padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; transition: all 0.2s ease; display: flex; flex-direction: column; justify-content: space-between; gap: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <div>
                            <!-- Top row with discount badge & scope -->
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="padding: 2px 8px; border-radius: 6px; background: #f1f5f9; color: #0f172a; font-family: ui-monospace, monospace; font-weight: 800; font-size: 0.75rem; border: 1px solid #e2e8f0;">
                                        {{ $valFormatted }}
                                    </span>
                                    <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 2px 6px; border-radius: 4px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">
                                        {{ $isSpecific ? _l('Exclusive') : _l('Storewide') }}
                                    </span>
                                </div>
                                <span style="font-size: 0.72rem; color: #64748b; font-weight: 500;">
                                    {{ $isSpecific ? _l('Selected Item') : _l('All Products') }}
                                </span>
                            </div>

                            <!-- Title & Description -->
                            <h4 style="margin: 0; font-size: 0.875rem; font-weight: 700; color: #0f172a; line-height: 1.35;">{{ $coupon->title ?: $coupon->code }}</h4>
                            @if(!empty($coupon->description))
                                <p style="margin: 4px 0 0; font-size: 0.775rem; color: #64748b; line-height: 1.4;">{{ $coupon->description }}</p>
                            @endif
                            <div style="display: flex; flex-wrap: wrap; gap: 4px 10px; margin-top: 6px; font-size: 0.7rem; color: #94a3b8; font-weight: 500;">
                                <span>• {{ $minSpendText }}</span>
                                <span>• {{ $expiryText }}</span>
                            </div>
                        </div>

                        <!-- Bottom row: Code box & Use / Copy Code button -->
                        <div style="padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.8125rem; font-weight: 700; background: #f8fafc; color: #0f172a; padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; letter-spacing: 0.04em;">
                                    {{ $coupon->code }}
                                </span>
                            </div>
                            <button type="button" class="btn-copy-coupon" data-code="{{ $coupon->code }}" onclick="copyCouponCode(this, '{{ $coupon->code }}')" style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; font-size: 0.75rem; font-weight: 700; border-radius: 6px; background: #0f172a; color: #ffffff; border: 1px solid #0f172a; cursor: pointer; transition: all 0.15s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.backgroundColor='#1e293b'" onmouseout="this.style.backgroundColor='#0f172a'">
                                <span>{{ _l('Copy Code') }}</span>
                                <span style="font-size: 0.8rem;">&rarr;</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Modal Footer -->
            <div style="padding: 14px 24px; background: #ffffff; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
                    💡 {{ _l('Apply verified promo codes at checkout to save instantly.') }}
                </span>
                <button type="button" onclick="closeProductCouponsModal()" style="padding: 6px 14px; font-size: 0.8125rem; font-weight: 600; border-radius: 6px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.backgroundColor='#e2e8f0'" onmouseout="this.style.backgroundColor='#f1f5f9'">{{ _l('Close') }}</button>
            </div>
        </div>
    </div>

    <!-- Script for Coupon Modal & Copy action -->
    <script>
        function openProductCouponsModal() {
            const modal = document.getElementById('product-coupons-modal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        function closeProductCouponsModal() {
            const modal = document.getElementById('product-coupons-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        function copyCouponCode(btn, code) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(() => showCopiedFeedback(btn));
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = code;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopiedFeedback(btn);
                } catch (err) {}
                document.body.removeChild(textArea);
            }
        }
        function showCopiedFeedback(btn) {
            const span = btn.querySelector('span');
            const originalText = span ? span.textContent : 'Copy Code';
            btn.style.backgroundColor = '#10b981';
            btn.style.borderColor = '#10b981';
            if (span) span.textContent = '✓ {{ _l('Copied!') }}';
            setTimeout(() => {
                btn.style.backgroundColor = '#0f172a';
                btn.style.borderColor = '#0f172a';
                if (span) span.textContent = originalText;
            }, 2000);
        }

        // Close on backdrop click and Escape key
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('product-coupons-modal');
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeProductCouponsModal();
                    }
                });
            }
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeProductCouponsModal();
                }
            });
        });
    </script>
    <style>
        @keyframes polyModalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .product-coupons-trigger:hover {
            border-color: #94a3b8 !important;
            background: #f1f5f9 !important;
            transform: translateY(-1px);
        }
        html.dark .product-coupons-trigger {
            background: rgba(39, 39, 42, 0.6) !important;
            border-color: #3f3f46 !important;
        }
        html.dark .product-coupons-trigger .coupons-trigger-title {
            color: #f4f4f5 !important;
        }
        html.dark .product-coupons-modal-card {
            background: #18181b !important;
            border-color: #27272a !important;
            color: #f4f4f5 !important;
        }
        html.dark .product-coupons-modal-card > div:first-child,
        html.dark .product-coupons-modal-card > div:last-child {
            background: #18181b !important;
            border-color: #27272a !important;
        }
        html.dark .product-coupons-modal-card > div:nth-child(2) {
            background: #09090b !important;
        }
        html.dark .product-coupon-ticket {
            background: #18181b !important;
            border-color: #27272a !important;
        }
        html.dark .product-coupon-ticket span,
        html.dark .product-coupon-ticket h4 {
            color: #f4f4f5 !important;
        }
        html.dark .btn-copy-coupon {
            background: #ffffff !important;
            color: #0f172a !important;
            border-color: #ffffff !important;
        }
    </style>
@endif

@endsection
