@php
    $heading = $attrs['heading'] ?? 'Why Choose PolyCMS';
    $subheading = $attrs['subheading'] ?? '';
    $features = $attrs['features'] ?? [];
    $columns = $attrs['columns'] ?? 3;
    $align = $attrs['align'] ?? 'center';

    // Support layout and spacing settings
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding_css'] ?? $attrs['padding'] ?? '';

    $inlineStyles = ["text-align: {$align}"];
    if ($margin !== '') {
        $inlineStyles[] = "margin: {$margin}";
    }
    if ($padding !== '') {
        $inlineStyles[] = "padding: {$padding}";
    }
    $styleAttr = 'style="' . implode('; ', $inlineStyles) . '"';
@endphp

<section class="fw-features" {!! $styleAttr !!}>
    <div class="container">
        <div class="fw-features-header" style="text-align: center;">
            <h2>{{ $heading }}</h2>
            @if($subheading)
                <p>{{ $subheading }}</p>
            @endif
        </div>
        <div class="fw-features-grid" style="--grid-cols: {{ $columns }};">
            @foreach($features as $feature)
                <div class="fw-feature-card">
                    @if(!empty($feature['icon']) || !empty($feature['icon_svg']))
                        <div class="fw-feature-icon-wrapper" style="display: flex; justify-content: {{ $align === 'center' ? 'center' : ($align === 'right' ? 'flex-end' : 'flex-start') }};">
                            @if(!empty($feature['icon']))
                                {!! \App\Support\IconRegistry::render($feature['icon'], 'fw-feature-icon', 40, 40) !!}
                            @else
                                {!! $feature['icon_svg'] !!}
                            @endif
                        </div>
                    @endif
                    <h3>{{ $feature['title'] ?? '' }}</h3>
                    <p>{{ $feature['description'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

