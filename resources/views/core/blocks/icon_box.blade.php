@php
    $layout = $attrs['layout'] ?? 'centered';
    $icon = $attrs['icon'] ?? 'fas fa-star';
    $iconColor = $attrs['icon_color'] ?? '#ffffff';
    $iconBg = $attrs['icon_bg'] ?? '#4f46e5';
    $linkUrl = $attrs['link_url'] ?? '';
    $tag = $linkUrl ? 'a' : 'div';
@endphp

<div class="landing-icon-box landing-icon-box--{{ $layout }}">
    <{{ $tag }} class="landing-icon-box__inner"@if($linkUrl) href="{{ $linkUrl }}" target="{{ get_link_target($linkUrl, '_self') }}"@endif>
        <div class="landing-icon-box__icon" style="color: {{ $iconColor }}; background: {{ $iconBg }}">
            <i class="{{ $icon }}"></i>
        </div>
        <div class="landing-icon-box__content">
            <h4 class="landing-icon-box__title">{{ $attrs['title'] ?? 'Feature Title' }}</h4>
            <p class="landing-icon-box__desc">{{ $attrs['description'] ?? '' }}</p>
        </div>
    </{{ $tag }}>
</div>
