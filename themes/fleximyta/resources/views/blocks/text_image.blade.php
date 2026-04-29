@php
    $imagePos = $attrs['image_position'] ?? 'left';
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';

    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<section class="landing-text-image {{ !$padding ? 'py-20' : '' }} px-4 bg-white dark:bg-gray-900 overflow-hidden" style="{{ $styleAttr }}">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row items-center gap-12 md:gap-20">
            <div class="flex-1 {{ $imagePos === 'left' ? 'md:order-2' : '' }}">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-6 leading-tight">
                    {{ $attrs['heading'] ?? '' }}
                </h2>
                <div class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed prose dark:prose-invert max-w-none">
                    {!! nl2br(e($attrs['content'] ?? '')) !!}
                </div>
            </div>
            
            <div class="flex-1 {{ $imagePos === 'left' ? 'md:order-1' : '' }}">
                <div class="relative group">
                    <div class="absolute -inset-4 bg-indigo-500/10 rounded-2xl blur-xl group-hover:bg-indigo-500/20 transition-all"></div>
                    <img 
                        src="{{ $attrs['image_url'] ?? 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80' }}" 
                        alt="{{ $attrs['heading'] ?? 'Image' }}" 
                        class="relative w-full h-auto rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700"
                    >
                </div>
            </div>
        </div>
    </div>
</section>
