{{-- Listing Toolbar: Grid/List Toggle + View Controls --}}
@php
    $showViewToggle = $showViewToggle ?? true;
    $execMs = defined('LARAVEL_START') ? number_format((microtime(true) - LARAVEL_START) * 1000, 2, '.', ',') : '0.00';
@endphp
<div class="listing-toolbar" style="display: flex; align-items: center; gap: 0.75rem;">
    <div class="listing-results">
        @if(isset($totalCount))
            <span>{{ $totalCount }} {{ _l('results') }}</span>
        @endif
    </div>
    <span class="execution-time-badge" data-server-ms="{{ $execMs }}" style="font-size: 0.8125rem; font-weight: 500; color: var(--geist-accents-5, #888); white-space: nowrap; margin-right: 0.25rem;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 0.75rem; height: 0.75rem; display: inline-block; vertical-align: -1px; margin-right: 2px; color: var(--geist-accents-5, #888);"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.265-.723l1.992-7.289H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd" /></svg>{{ $execMs }} ms
    </span>
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
</div>
