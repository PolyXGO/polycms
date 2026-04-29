@php
    $level = $attrs['level'] ?? 2;
    $align = $attrs['alignment'] ?? 'left';
    $weight = $attrs['font_weight'] ?? 'font-bold';
    $color = $attrs['color'] ?? '';
    
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left'
    };
@endphp

<h{{ $level }} class="{{ $alignClass }} {{ $weight }}" style="{{ $color ? 'color: ' . $color . ';' : '' }}">
    {{ $attrs['text'] ?? '' }}
</h{{ $level }}>
