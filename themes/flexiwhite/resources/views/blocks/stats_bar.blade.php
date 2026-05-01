@php
    $stats = $attrs['stats'] ?? [];
@endphp

<div class="container">
    <div class="lp-stats">
        @foreach($stats as $stat)
            <div class="lp-stat">
                <div class="lp-stat-value">{{ $stat['value'] ?? '' }}</div>
                <div class="lp-stat-label">{{ $stat['label'] ?? '' }}</div>
            </div>
        @endforeach
    </div>
</div>

