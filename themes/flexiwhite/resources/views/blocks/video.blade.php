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
        <div class="relative overflow-hidden rounded-2xl shadow-2xl bg-black landing-video-wrapper" style="{{ $aspectStyle }}; position: relative; width: 100%;">
            <div class="landing-video-preview" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                <img src="{{ $previewImage }}" alt="{{ $caption ?: 'Video preview' }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; margin: 0;" {!! media_lazy_attr() !!}>
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(15,23,42,0.05), rgba(15,23,42,0.2));"></div>

                @if($url)
                    @if($attrs['play_inline'] ?? false)
                        <button
                            onclick="
                                var wrapper = this.closest('.landing-video-wrapper');
                                var tmpl = wrapper.querySelector('.video-embed-template');
                                var preview = wrapper.querySelector('.landing-video-preview');
                                if (tmpl && preview) {
                                    preview.replaceWith(tmpl.content.cloneNode(true));
                                }
                            "
                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; border-radius: 50%; background: rgba(255, 255, 255, 0.95); color: #4f46e5; box-shadow: 0 10px 25px rgba(0,0,0,0.2); border: none; cursor: pointer; z-index: 10;"
                            aria-label="Play video"
                            onmouseover="this.style.transform='translate(-50%, -50%) scale(1.05)'; this.style.background='var(--geist-background)';"
                            onmouseout="this.style.transform='translate(-50%, -50%) scale(1)'; this.style.background='rgba(255, 255, 255, 0.95)';"
                        >
                            <svg style="width: 32px; height: 32px; margin-left: 4px;" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor" />
                            </svg>
                        </button>
                    @else
                        <a
                            href="{{ $url }}"
                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; border-radius: 50%; background: rgba(255, 255, 255, 0.95); color: #4f46e5; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-decoration: none; z-index: 10;"
                            target="{{ get_link_target($url, '_blank') }}"
                            rel="noopener noreferrer"
                            aria-label="Play video"
                            onmouseover="this.style.transform='translate(-50%, -50%) scale(1.05)'; this.style.background='var(--geist-background)';"
                            onmouseout="this.style.transform='translate(-50%, -50%) scale(1)'; this.style.background='rgba(255, 255, 255, 0.95)';"
                        >
                            <svg style="width: 32px; height: 32px; margin-left: 4px;" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor" />
                            </svg>
                        </a>
                    @endif
                @endif
            </div>

            <template class="video-embed-template">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                    @if($provider === 'youtube')
                        <iframe 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                            src="https://www.youtube.com/embed/{{ $videoId }}?autoplay=1" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    @elseif($provider === 'vimeo')
                        <iframe 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                            src="https://player.vimeo.com/video/{{ $videoId }}?autoplay=1" 
                            allow="autoplay; fullscreen; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    @elseif($provider === 'file' && $directVideoUrl)
                        <video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; margin: 0;" src="{{ $directVideoUrl }}" controls autoplay preload="metadata"></video>
                    @endif
                </div>
            </template>
        </div>
        @if($caption)
            <p class="mt-4 text-center text-sm text-gray-500 italic">{{ $caption }}</p>
        @endif
    @elseif($provider === 'file' && $directVideoUrl)
        <div class="relative overflow-hidden rounded-2xl shadow-2xl bg-black landing-video-wrapper" style="{{ $aspectStyle }}; position: relative; width: 100%;">
            <video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; margin: 0;" src="{{ $directVideoUrl }}" controls preload="metadata"></video>
        </div>
        @if($caption)
            <p class="mt-4 text-center text-sm text-gray-500 italic">{{ $caption }}</p>
        @endif
    @elseif($videoId)
        <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-black landing-video-wrapper" style="{{ $aspectStyle }}; position: relative; width: 100%;">
            @if($provider === 'youtube')
                <iframe 
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                    src="https://www.youtube.com/embed/{{ $videoId }}" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            @elseif($provider === 'vimeo')
                <iframe 
                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                    src="https://player.vimeo.com/video/{{ $videoId }}" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen>
                </iframe>
            @endif
        </div>
        @if($caption)
            <p class="mt-4 text-center text-sm text-gray-500 italic">{{ $caption }}</p>
        @endif
    @else
        <div style="background: var(--geist-accents-1); border: 2px dashed var(--geist-accents-2); border-radius: 1rem; padding: 3rem; text-align: center;">
            <p style="color: var(--geist-accents-5);">Please provide a valid YouTube, Vimeo, or direct video URL</p>
        </div>
    @endif
</div>
