@php
    $containerClass = !empty($attrs['viewport_full_width']) ? 'container-fluid' : 'container';
    $heading = $attrs['heading'] ?? 'See It In Action';
    $subheading = $attrs['subheading'] ?? 'Section Description';
    $demoTitle = $attrs['demo_title'] ?? 'From Zero to SaaS in 10 Days';
    $steps = $attrs['steps'] ?? [
        ['title' => 'Branding & Setup', 'text' => 'We customize everything with your logo and colors.'],
        ['title' => 'Training & Handover', 'text' => 'We walk you through the admin panel.'],
    ];
    $previewImage = $attrs['preview_image'] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80';
    $videoLink = $attrs['video_link'] ?? '';
    $isImageLeft = ($attrs['image_position'] ?? 'right') === 'left';

    $button1Text = array_key_exists('button1_text', $attrs) ? $attrs['button1_text'] : 'Start Now';
    $button1Url = array_key_exists('button1_url', $attrs) ? $attrs['button1_url'] : '#';
    $button2Text = array_key_exists('button2_text', $attrs) ? $attrs['button2_text'] : 'Learn More';
    $button2Url = array_key_exists('button2_url', $attrs) ? $attrs['button2_url'] : '#';

    $embedUrl = '';
    $directVideoUrl = '';
    if (!empty($videoLink)) {
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoLink, $match)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $match[1];
        } elseif (preg_match('%vimeo\.com/(?:video/)?([0-9]+)%i', $videoLink, $match)) {
            $embedUrl = 'https://player.vimeo.com/video/' . $match[1];
        } elseif (preg_match('/\.(mp4|webm|ogg|mov|m4v)(\?.*)?$/i', $videoLink)) {
            $directVideoUrl = $videoLink;
        }
    }
@endphp

<section class="showcase-block" id="demo">
    <div class="{{ $containerClass }}">
        <div class="showcase-block__header">
            <h2 class="showcase-block__heading">{{ $heading }}</h2>
            @if(!empty($subheading))
                <p class="showcase-block__subheading">{{ $subheading }}</p>
            @endif
        </div>

        <div class="showcase-block__layout {{ $isImageLeft ? 'showcase-block__layout--reverse' : '' }}">
            <div class="showcase-block__copy">
                <h3 class="showcase-block__title">{{ $demoTitle }}</h3>

                @if(!empty($button1Text) || !empty($button2Text))
                    <div class="showcase-block__actions">
                        @if(!empty($button1Text))
                            <a href="{{ $button1Url ?: '#' }}" class="showcase-block__button showcase-block__button--primary">
                                {{ $button1Text }}
                            </a>
                        @endif

                        @if(!empty($button2Text))
                            <a href="{{ $button2Url ?: '#' }}" class="showcase-block__button showcase-block__button--secondary">
                                {{ $button2Text }}
                            </a>
                        @endif
                    </div>
                @endif

                <div class="showcase-block__steps">
                    @foreach($steps as $index => $step)
                        <div class="showcase-block__step">
                            <span class="showcase-block__step-badge">{{ $index + 1 }}</span>
                            <div class="showcase-block__step-body">
                                <h4 class="showcase-block__step-title">{{ $step['title'] ?? '' }}</h4>
                                <p class="showcase-block__step-text">{{ $step['text'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="showcase-block__media-wrap">
                <div class="showcase-block__media">
                    @if(!empty($previewImage))
                        <img src="{{ $previewImage }}" alt="Showcase preview">
                    @elseif(!empty($directVideoUrl))
                        <video src="{{ $directVideoUrl }}" controls preload="metadata"></video>
                    @elseif(!empty($embedUrl))
                        <iframe
                            src="{{ $embedUrl }}"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    @else
                        <div class="showcase-block__placeholder">Preview Image</div>
                    @endif

                    @if(!empty($previewImage))
                        <div class="showcase-block__media-overlay"></div>
                    @endif

                    @if(!empty($previewImage) && !empty($videoLink))
                        <a href="{{ $videoLink }}" class="showcase-block__play" target="_blank" rel="noopener noreferrer" aria-label="Play showcase video">
                            <svg class="showcase-block__play-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 6.5v11l9-5.5-9-5.5Z" fill="currentColor" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
