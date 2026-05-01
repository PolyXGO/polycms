@php
    $title = $attrs['heading'] ?? $attrs['title'] ?? 'Build something amazing, ship it faster.';
    $subtitle = $attrs['subheading'] ?? $attrs['subtitle'] ?? '';
    $primaryText = $attrs['button_text'] ?? $attrs['primary_button_text'] ?? '';
    $primaryUrl = $attrs['button_link'] ?? $attrs['primary_button_url'] ?? '#';
    $secondaryText = $attrs['secondary_button_text'] ?? '';
    $secondaryUrl = $attrs['secondary_button_url'] ?? '#';
@endphp

<section class="lp-hero">
    <div class="container">
        <h1 class="lp-hero-title">{!! $title !!}</h1>
        @if($subtitle)
            <p class="lp-hero-subtitle">{{ $subtitle }}</p>
        @endif
        @if($primaryText || $secondaryText)
            <div class="lp-hero-actions">
                @if($primaryText)
                    <a href="{{ $primaryUrl }}" class="btn btn-primary btn-lg">{{ $primaryText }}</a>
                @endif
                @if($secondaryText)
                    <a href="{{ $secondaryUrl }}" class="btn btn-secondary btn-lg">{{ $secondaryText }}</a>
                @endif
            </div>
        @endif
    </div>
</section>
