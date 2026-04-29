@php
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';
    
    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<section class="landing-hero {{ !$padding ? 'py-20' : '' }} px-4 bg-indigo-900 text-white text-center" style="{{ $styleAttr }}">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-5xl md:text-6xl font-extrabold mb-6 animate-fade-in-up">
            {{ $attrs['heading'] ?? '' }}
        </h1>
        <p class="text-xl md:text-2xl text-indigo-100 mb-10 leading-relaxed">
            {{ $attrs['subheading'] ?? '' }}
        </p>
        @if(!empty($attrs['button_text']))
            <a href="{{ $attrs['button_link'] ?? '#' }}" class="inline-block bg-white text-indigo-900 px-8 py-4 rounded-full font-bold text-lg hover:bg-indigo-50 transition-all transform hover:scale-105 shadow-xl">
                {{ $attrs['button_text'] }}
            </a>
        @endif
    </div>
</section>

<style>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out forwards;
}
</style>
