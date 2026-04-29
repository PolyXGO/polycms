@php
    $align = $attrs['alignment'] ?? 'left';
    $fontSize = $attrs['font_size'] ?? 'text-base';
    $color = $attrs['color'] ?? '';
    
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        'full', 'justify' => 'text-justify',
        default => 'text-left'
    };
@endphp

<div class="landing-text {{ $alignClass }} {{ $fontSize }}" style="{{ $color ? 'color: ' . $color . ';' : '' }}">
    {!! e($attrs['content'] ?? '') !!}
</div>
