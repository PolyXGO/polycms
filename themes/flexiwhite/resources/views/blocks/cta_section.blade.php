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
@endphp

@if($isCoreCta)
{{-- Core CTA layout: gradient background, info text, form, stats --}}
<section class="lp-cta lp-cta--core">
    <div class="container">
        <h2>{{ $heading }}</h2>
        @if($text)
            <p class="lp-cta__text">{{ $text }}</p>
        @endif
        @if($infoText)
            <p class="lp-cta__info">{{ $infoText }}</p>
        @endif
        @if($formHtml)
            <div class="lp-cta__form">
                {!! $formHtml !!}
            </div>
        @endif
        @if(!empty($stats))
            <div class="lp-cta__stats">
                @foreach($stats as $stat)
                    <div class="lp-cta__stat">
                        <div class="lp-cta__stat-number">{{ $stat['number'] ?? '' }}</div>
                        <div class="lp-cta__stat-label">{{ $stat['label'] ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@else
{{-- Simple theme CTA layout: heading + text + button --}}
<section class="lp-cta">
    <div class="container">
        <h2>{{ $heading }}</h2>
        @if($text)
            <p>{{ $text }}</p>
        @endif
        @if($buttonText)
            <a href="{{ $buttonUrl }}" class="btn btn-primary btn-lg">{{ $buttonText }}</a>
        @endif
    </div>
</section>
@endif
