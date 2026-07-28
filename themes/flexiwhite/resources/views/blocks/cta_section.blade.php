@php
    // Support both core schema (info_text, form_html, stats) and simple theme schema (button_text, button_url)
    $heading = $attrs['heading'] ?? 'Ready to get started?';
    $text = $attrs['text'] ?? '';
    
    // Core CTA fields
    $infoText = $attrs['info_text'] ?? '';
    $formHtml = $attrs['form_html'] ?? '';
    $stats = $attrs['stats'] ?? [];
    
    // Simple theme CTA fields (fallback if core fields are empty)
    $buttonText = $attrs['button_text'] ?? '';
    $buttonUrl = $attrs['button_url'] ?? '/login';
    
    // Determine mode: "core" if has stats/form_html/info_text, otherwise "simple"
    $isCoreCta = !empty($stats) || !empty($formHtml) || !empty($infoText);

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

@if($isCoreCta)
{{-- Core CTA layout: gradient background, info text, form, stats --}}
<section class="fw-cta fw-cta--core" {!! $styleAttr !!}>
    <div class="container">
        <h2>{{ $heading }}</h2>
        @if($text)
            <p class="fw-cta__text">{{ $text }}</p>
        @endif
        @if($infoText)
            <p class="fw-cta__info">{{ $infoText }}</p>
        @endif
        @if($formHtml)
            <div class="fw-cta__form">
                {!! $formHtml !!}
            </div>
        @endif
        @if(!empty($stats))
            <div class="fw-cta__stats">
                @foreach($stats as $stat)
                    <div class="fw-cta__stat">
                        <div class="fw-cta__stat-number">{{ $stat['number'] ?? '' }}</div>
                        <div class="fw-cta__stat-label">{{ $stat['label'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@else
{{-- Simple theme CTA layout: heading + text + button --}}
<section class="fw-cta" {!! $styleAttr !!}>
    <div class="container">
        <h2>{{ $heading }}</h2>
        @if($text)
            <p>{{ $text }}</p>
        @endif
        @if($buttonText)
            <a href="{{ $buttonUrl }}" target="{{ get_link_target($buttonUrl) }}" class="btn btn-primary btn-lg">{{ $buttonText }}</a>
        @endif
    </div>
</section>
@endif
