{{-- Listing Toolbar: Grid/List Toggle + View Controls --}}
<div class="listing-toolbar">
    <div class="listing-results">
        @if(isset($totalCount))
            <span>{{ $totalCount }} {{ _l('results') }}</span>
        @endif
    </div>
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
</div>
