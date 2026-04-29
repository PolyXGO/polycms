@php
    $items = $attrs['items'] ?? [];
    $style = $attrs['style'] ?? 'standard';
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';
    
    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<div class="landing-block-accordion {{ !$padding ? 'py-12' : '' }}">
    <x-accordion :items="$items" :style="$style" style="{{ $styleAttr }}" />
</div>
