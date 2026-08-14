{{-- Listing Toolbar: Grid/List Toggle + Product Sort Tabs (Best sellers, Newest, Best rated, Trending, Price) --}}
@php
    $showViewToggle = $showViewToggle ?? true;
    $showSortTabs = $showSortTabs ?? false;

    $currentSort = request('sort', request('sort_by', 'newest'));
    $currentOrder = strtolower((string) request('order', request('sort_order', 'asc')));

    $buildUrl = function($override = [], $remove = []) {
        $params = request()->except(array_merge(['page'], $remove));
        $merged = array_merge($params, $override);
        $clean = array_filter($merged, fn($v) => $v !== null && $v !== '');
        return request()->url() . (!empty($clean) ? '?' . http_build_query($clean) : '');
    };
@endphp

<div class="listing-toolbar" style="display: flex; align-items: center; justify-content: flex-end; margin-left: auto; gap: 0.75rem; flex-wrap: wrap;">
    @if(isset($totalCount))
        <div class="listing-results" style="font-size: 0.875rem; color: var(--geist-accents-5, #64748b);">
            <span>{{ $totalCount }} {{ _l('results') }}</span>
        </div>
    @endif

    {!! \App\Facades\Hook::doAction('theme.listing.after_title') !!}

    {{-- Grid/List View Toggle --}}
    @if($showViewToggle)
        <div class="listing-view-toggle" data-listing-target="{{ $target ?? 'listing-container' }}">
            <button type="button" class="view-toggle-btn {{ ($defaultView ?? 'grid') === 'grid' ? 'active' : '' }}" data-view="grid" aria-label="{{ _l('Grid view') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
            </button>
            <button type="button" class="view-toggle-btn {{ ($defaultView ?? 'grid') === 'list' ? 'active' : '' }}" data-view="list" aria-label="{{ _l('List view') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Product Sort Tabs: Best sellers, Newest, Best rated, Trending, Price (Only shown on product listings) --}}
    @if($showSortTabs)
        <div class="fw-sort-tabs">
            {{-- Best sellers --}}
            <a href="{{ $buildUrl(['sort' => 'best_sellers']) }}" 
               class="fw-sort-tab {{ in_array($currentSort, ['best_sellers', 'bestsellers']) ? 'is-active' : '' }}"
               title="{{ _l('Best sellers') }}">
                {{ _l('Best sellers') }}
            </a>

            {{-- Newest --}}
            <a href="{{ $buildUrl(['sort' => 'newest']) }}" 
               class="fw-sort-tab {{ in_array($currentSort, ['newest', 'latest', 'created_at']) ? 'is-active' : '' }}"
               title="{{ _l('Newest') }}">
                {{ _l('Newest') }}
            </a>

            {{-- Best rated --}}
            <a href="{{ $buildUrl(['sort' => 'best_rated']) }}" 
               class="fw-sort-tab {{ in_array($currentSort, ['best_rated', 'rating']) ? 'is-active' : '' }}"
               title="{{ _l('Best rated') }}">
                {{ _l('Best rated') }}
            </a>

            {{-- Trending --}}
            <a href="{{ $buildUrl(['sort' => 'trending']) }}" 
               class="fw-sort-tab {{ in_array($currentSort, ['trending', 'views', 'popular']) ? 'is-active' : '' }}"
               title="{{ _l('Trending') }}">
                {{ _l('Trending') }}
            </a>

            {{-- Price Toggle (Ascending / Descending) --}}
            @php
                $isPriceActive = in_array($currentSort, ['price', 'price_asc', 'price_desc']);
                $nextPriceOrder = ($isPriceActive && $currentOrder === 'asc') ? 'desc' : 'asc';
                $priceArrow = !$isPriceActive ? '⇅' : ($currentOrder === 'asc' ? '↑' : '↓');
            @endphp
            <a href="{{ $buildUrl(['sort' => 'price', 'order' => $nextPriceOrder]) }}" 
               class="fw-sort-tab {{ $isPriceActive ? 'is-active' : '' }}"
               title="{{ _l('Price') }}">
                {{ _l('Price') }} <span style="font-size: 0.75rem; margin-left: 2px;">{{ $priceArrow }}</span>
            </a>
        </div>
    @endif
</div>
