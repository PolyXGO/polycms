@php
    $heading = $attrs['heading'] ?? 'Why Choose PolyCMS';
    $subheading = $attrs['subheading'] ?? '';
    $features = $attrs['features'] ?? [];
    $columns = $attrs['columns'] ?? 3;
    $align = $attrs['align'] ?? 'center';
@endphp

<section class="lp-features" style="text-align: {{ $align }};">
    <div class="container">
        <div class="lp-features-header" style="text-align: center;">
            <h2>{{ $heading }}</h2>
            @if($subheading)
                <p>{{ $subheading }}</p>
            @endif
        </div>
        <div class="lp-features-grid" style="grid-template-columns: repeat({{ $columns }}, 1fr);">
            @foreach($features as $feature)
                <div class="lp-feature-card">
                    @if(!empty($feature['icon']) || !empty($feature['icon_svg']))
                        <div class="lp-feature-icon-wrapper" style="display: flex; justify-content: {{ $align === 'center' ? 'center' : ($align === 'right' ? 'flex-end' : 'flex-start') }};">
                            @if(!empty($feature['icon']))
                                {!! \App\Support\IconRegistry::render($feature['icon'], 'lp-feature-icon', 40, 40) !!}
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

