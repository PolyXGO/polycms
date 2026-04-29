@php
    $align = $attrs['alignment'] ?? 'left';
    $width = $attrs['width'] ?? 'w-full';
    $radius = $attrs['border_radius'] ?? 'rounded-xl';
    
    $justifyClass = match($align) {
        'center' => 'justify-center',
        'right' => 'justify-end',
        default => 'justify-start'
    };
@endphp

<div class="flex {{ $justifyClass }}">
    <div class="{{ $width }} overflow-hidden {{ $radius }}">
        @if(!empty($attrs['src']))
            <img src="{{ $attrs['src'] }}" alt="{{ $attrs['alt'] ?? '' }}" class="w-full h-auto object-cover">
        @endif
    </div>
</div>
