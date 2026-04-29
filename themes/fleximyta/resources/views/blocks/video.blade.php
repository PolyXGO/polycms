@php
    $url = $attrs['url'] ?? '';
    $previewImage = $attrs['preview_image'] ?? '';
    $aspectRatio = $attrs['aspect_ratio'] ?? '16/9';
    $caption = $attrs['caption'] ?? '';
    $margin = $attrs['margin'] ?? '';
    $padding = $attrs['padding'] ?? '';

    $videoId = '';
    $provider = 'youtube';
    $directVideoUrl = '';

    if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
        $provider = 'youtube';
        // Improved regex to handle shorts and various parameter orders
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            $videoId = $match[1];
        }
    } elseif (str_contains($url, 'vimeo.com')) {
        $provider = 'vimeo';
        if (preg_match('%vimeo\.com/(?:video/)?([0-9]+)%i', $url, $match)) {
            $videoId = $match[1];
        }
    } elseif (preg_match('/\.(mp4|webm|ogg|mov|m4v)(\?.*)?$/i', $url)) {
        $provider = 'file';
        $directVideoUrl = $url;
    }

    [$aspectWidth, $aspectHeight] = array_pad(array_map('floatval', explode('/', (string) $aspectRatio, 2)), 2, 0);
    if ($aspectWidth <= 0 || $aspectHeight <= 0) {
        $aspectWidth = 16;
        $aspectHeight = 9;
    }
    $aspectStyle = "aspect-ratio: {$aspectWidth} / {$aspectHeight}";

    $inlineStyles = [];
    if ($margin) $inlineStyles[] = "margin: {$margin}";
    if ($padding) $inlineStyles[] = "padding: {$padding}";
    $styleAttr = !empty($inlineStyles) ? implode('; ', $inlineStyles) : '';
@endphp

<div class="landing-video {{ !$padding ? 'py-8' : '' }} max-w-4xl mx-auto px-4" style="{{ $styleAttr }}">
    @if($previewImage)
        <div class="relative overflow-hidden rounded-2xl shadow-2xl bg-black" style="{{ $aspectStyle }}">
            <img src="{{ $previewImage }}" alt="{{ $caption ?: 'Video preview' }}" class="absolute inset-0 h-full w-full object-cover">
            <div class="landing-video__overlay"></div>

            @if($url)
                <a
                    href="{{ $url }}"
                    class="landing-video__play"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Play video"
                >
                    <svg class="landing-video__play-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor" />
                    </svg>
                </a>
            @endif
        </div>
        @if($caption)
            <p class="mt-4 text-center text-sm text-gray-500 italic">{{ $caption }}</p>
        @endif
    @elseif($provider === 'file' && $directVideoUrl)
        <div class="relative overflow-hidden rounded-2xl shadow-2xl bg-black" style="{{ $aspectStyle }}">
            <video class="absolute inset-0 h-full w-full object-cover" src="{{ $directVideoUrl }}" controls preload="metadata"></video>
        </div>
        @if($caption)
            <p class="mt-4 text-center text-sm text-gray-500 italic">{{ $caption }}</p>
        @endif
    @elseif($videoId)
        <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-black" style="{{ $aspectStyle }}">
            @if($provider === 'youtube')
                <iframe 
                    class="absolute inset-0 w-full h-full"
                    src="https://www.youtube.com/embed/{{ $videoId }}" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            @elseif($provider === 'vimeo')
                <iframe 
                    class="absolute inset-0 w-full h-full"
                    src="https://player.vimeo.com/video/{{ $videoId }}" 
                    frameborder="0" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            @endif
        </div>
        @if($caption)
            <p class="mt-4 text-center text-sm text-gray-500 italic">{{ $caption }}</p>
        @endif
    @else
        <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-12 text-center border-2 border-dashed border-gray-300 dark:border-gray-700">
            <p class="text-gray-400">Please provide a valid YouTube, Vimeo, or direct video URL</p>
        </div>
    @endif
</div>
