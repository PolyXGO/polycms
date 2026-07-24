@php
    $stats = $attrs['stats'] ?? [];

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

<section class="fw-stats-section" {!! $styleAttr !!}>
    <div class="container">
        <div class="fw-stats">
            @foreach($stats as $stat)
                <div class="fw-stat">
                    <div class="fw-stat-value">{{ $stat['value'] ?? '' }}</div>
                    <div class="fw-stat-label">{{ $stat['label'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

