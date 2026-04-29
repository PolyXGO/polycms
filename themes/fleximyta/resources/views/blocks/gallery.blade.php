@php
    $images = $attrs['images'] ?? [];
    $columns = $attrs['columns'] ?? 3;
    $gap = $attrs['gap'] ?? 'gap-4';
    $rounded = $attrs['rounded'] ?? 'rounded-xl';
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';
    
    // Context-aware: If no images selected, try to pull from context (product/post)
    if (empty($images)) {
        $contextModel = $product ?? $post ?? null;
        if ($contextModel && method_exists($contextModel, 'media')) {
            $images = $contextModel->media->map(fn($m) => [
                'url' => $m->url,
                'alt' => $m->name ?? $m->file_name ?? ''
            ])->toArray();
        }
    }

    $gridCols = match((int)$columns) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        4 => 'grid-cols-2 md:grid-cols-4',
        default => 'grid-cols-2 lg:grid-cols-3',
    };

    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<div class="landing-gallery {{ !$padding ? 'py-8' : '' }} max-w-6xl mx-auto px-4" style="{{ $styleAttr }}">
    @if(!empty($images))
        <div class="grid {{ $gridCols }} {{ $gap }}">
            @foreach($images as $image)
                <div class="group relative overflow-hidden {{ $rounded }} aspect-square bg-gray-100 dark:bg-gray-800 shadow-sm transition-all hover:shadow-xl">
                    <img 
                        src="{{ $image['url'] }}" 
                        alt="{{ $image['alt'] ?? '' }}"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors pointer-events-none"></div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-12 text-center border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl">
            <p class="text-gray-400">No images to display in gallery.</p>
        </div>
    @endif
</div>
