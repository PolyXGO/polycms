@php
    $color = $attrs['color'] ?? 'gray-200';
    $spacingClass = $attrs['spacing'] ?? 'py-8';
    $width = $attrs['width'] ?? 'full'; // full, 1/2, 1/3
    $style = $attrs['style'] ?? 'solid'; // solid, dashed, dotted
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';

    $widthClass = match($width) {
        '1/2' => 'w-1/2 mx-auto',
        '1/3' => 'w-1/3 mx-auto',
        default => 'w-full',
    };

    $borderStyle = match($style) {
        'dashed' => 'border-dashed',
        'dotted' => 'border-dotted',
        default => 'border-solid',
    };

    $inlineStyles = [];
    if (str_contains($color, '#')) $inlineStyles[] = "border-color: {$color}";
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) {
        $inlineStyles[] = "padding: {$padding}";
        $spacingClass = ''; // Clear default spacing if padding is provided
    }
    
    $styleAttr = !empty($inlineStyles) ? 'style="' . implode('; ', $inlineStyles) . '"' : '';
    $colorClass = str_contains($color, '#') ? '' : 'border-' . $color;
@endphp

<div class="landing-divider {{ $spacingClass }}" {!! $styleAttr !!}>
    <hr class="{{ $widthClass }} {{ $borderStyle }} {{ $colorClass }} border-t">
</div>
