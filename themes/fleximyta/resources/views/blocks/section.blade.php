@php
    $bgColor = $attrs['bg_color'] ?? '';
    $bgImage = $attrs['bg_image'] ?? '';
    $textColor = $attrs['text_color'] ?? '';
    $fullWidth = $attrs['full_width'] ?? false;
    $margin = $attrs['margin'] ?? '';
    $cssPadding = $attrs['padding_css'] ?? $attrs['padding'] ?? ''; // Support both for safety
    $paddingClass = $attrs['padding_class'] ?? '';
    
    // If padding looks like a Tailwind class (e.g., py-), use it as class
    if (!$paddingClass && is_string($cssPadding) && str_starts_with($cssPadding, 'py-')) {
        $paddingClass = $cssPadding;
        $cssPadding = '';
    } else if (!$paddingClass) {
        $paddingClass = 'py-16';
    }

    $style = [];
    if ($bgColor) $style[] = "background-color: {$bgColor}";
    if ($bgImage) $style[] = "background-image: url('{$bgImage}')";
    if ($textColor) $style[] = "color: {$textColor}";
    if ($margin) $style[] = "margin: {$margin}";
    if ($cssPadding && !str_starts_with($cssPadding, 'py-')) $style[] = "padding: {$cssPadding}";
    
    $styleString = !empty($style) ? 'style="' . implode('; ', $style) . '"' : '';
    $bgClass = $bgImage ? 'bg-cover bg-center' : '';
@endphp

<section class="landing-section {{ $paddingClass }} {{ $bgClass }} relative" {!! $styleString !!}>
    @if($bgImage && ($attrs['overlay'] ?? false))
        <div class="absolute inset-0 bg-black/40 pointer-events-none"></div>
    @endif
    
    <div class="{{ $fullWidth ? 'w-full' : 'max-w-6xl mx-auto px-4' }} relative">
        {!! $children !!}
    </div>
</section>
