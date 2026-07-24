@php
    $title = $attrs['heading'] ?? $attrs['title'] ?? 'Build something amazing, ship it faster.';
    $subtitle = $attrs['subheading'] ?? $attrs['subtitle'] ?? '';
    $primaryText = $attrs['button_text'] ?? $attrs['primary_button_text'] ?? '';
    $primaryUrl = $attrs['button_link'] ?? $attrs['primary_button_url'] ?? '#';
    $secondaryText = $attrs['secondary_button_text'] ?? '';
    $secondaryUrl = $attrs['secondary_button_url'] ?? '#';

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

<section class="fw-hero" {!! $styleAttr !!}>
    <div class="container">
        <h1 class="fw-hero-title">{!! $title !!}</h1>
        @if($subtitle)
            <p class="fw-hero-subtitle">{{ $subtitle }}</p>
        @endif
        @if($primaryText || $secondaryText)
            <div class="fw-hero-actions">
                @if($primaryText)
                    <a href="{{ $primaryUrl }}" target="{{ get_link_target($primaryUrl) }}" class="btn btn-primary btn-lg">{{ $primaryText }}</a>
                @endif
                @if($secondaryText)
                    <a href="{{ $secondaryUrl }}" target="{{ get_link_target($secondaryUrl) }}" class="btn btn-secondary btn-lg">{{ $secondaryText }}</a>
                @endif
            </div>
        @endif
    </div>
</section>
