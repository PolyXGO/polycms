@php
    $alignment = $attrs['alignment'] ?? 'center';
    $valueColor = $attrs['value_color'] ?? '';
@endphp

<div class="landing-counter text-{{ $alignment }}">
    @if(!empty($attrs['icon']))
    <div class="landing-counter__icon">
        <i class="{{ $attrs['icon'] }}"></i>
    </div>
    @endif
    <div class="landing-counter__value"@if($valueColor) style="color: {{ $valueColor }}"@endif>
        @if(!empty($attrs['prefix']))<span class="landing-counter__prefix">{{ $attrs['prefix'] }}</span>@endif
        <span class="landing-counter__number">{{ $attrs['value'] ?? '0' }}</span>
        @if(!empty($attrs['suffix']))<span class="landing-counter__suffix">{{ $attrs['suffix'] }}</span>@endif
    </div>
    <div class="landing-counter__label">{{ $attrs['label'] ?? '' }}</div>
</div>
