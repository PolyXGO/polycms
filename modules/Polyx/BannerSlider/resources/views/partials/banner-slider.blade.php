@php
    // Banners are passed from parent view or can be fetched via hook
    $banners = $banners ?? \App\Facades\Hook::applyFilters('frontend.topbar.banners', []);

    // Get settings
    $bannerService = app(\Modules\Polyx\BannerSlider\Services\BannerService::class);
    $settings = $bannerService->getSettings();

    $autoSlide = ($settings['auto_slide'] ?? '1') === '1';
    $autoSlideInterval = (int)($settings['auto_slide_interval'] ?? 5000);
    $transitionEffect = $settings['transition_effect'] ?? 'slide';
    $showNavigation = ($settings['show_navigation'] ?? '1') === '1';
    $showIndicators = ($settings['show_indicators'] ?? '1') === '1';

    // Check if user is admin
    $isAdmin = false;
    $user = null;
    try {
        // Try multiple guards to get authenticated user
        $user = \Illuminate\Support\Facades\Auth::guard('web')->user();
        if (!$user) {
            $user = \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
        }
        if (!$user) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }

        if ($user) {
            // Check if user has admin role using Spatie Permission
            if (method_exists($user, 'hasRole')) {
                $isAdmin = $user->hasRole('admin');
            } elseif (method_exists($user, 'hasAnyRole')) {
                $isAdmin = $user->hasAnyRole(['admin']);
            } else {
                // Fallback: check if user has any roles and one of them is admin
                if (method_exists($user, 'roles')) {
                    try {
                        $roles = $user->roles;
                        if ($roles && method_exists($roles, 'pluck')) {
                            $roleNames = $roles->pluck('name')->toArray();
                            $isAdmin = in_array('admin', $roleNames);
                        }
                    } catch (\Exception $e) {
                        // If roles relationship fails, try direct check
                        $isAdmin = false;
                    }
                }
            }
        }
    } catch (\Exception $e) {
        // If any error occurs, default to false
        $isAdmin = false;
    }

    // Debug: Uncomment to see values (remove in production)
    // \Log::info('Banner Slider - Admin Check', ['user' => $user ? $user->id : null, 'isAdmin' => $isAdmin]);
@endphp

@if(!empty($banners) && count($banners) > 0)
    {{-- Load module assets --}}
    @once
    <link rel="stylesheet" href="{{ asset('modules/banner-slider/css/banner-slider.css') }}">
    @endonce

    <div class="banner-slider-container" id="banner-slider-container"
         data-auto-slide="{{ $autoSlide ? 'true' : 'false' }}"
         data-auto-slide-interval="{{ $autoSlideInterval }}"
         data-transition-effect="{{ $transitionEffect }}"
         data-show-navigation="{{ $showNavigation ? 'true' : 'false' }}"
         data-show-indicators="{{ $showIndicators ? 'true' : 'false' }}">
        <div class="banner-slider-wrapper">
            <div class="banner-slider" id="banner-slider">
                @foreach($banners as $banner)
                    <div class="banner-slide" data-type="{{ $banner['type'] ?? 'image' }}" data-banner-id="{{ $banner['id'] ?? '' }}">
                        @if(($banner['type'] ?? 'image') === 'text')
                            {{-- Text Banner --}}
                            @php
                                $bgColor = $banner['background_color'] ?? null;
                                $textColor = $banner['text_color'] ?? '#000000';
                                $gradientColor1 = $banner['background_color'] ?? null;
                                $gradientColor2 = $banner['gradient_color'] ?? null;
                                $gradientDegree = $banner['gradient_degree'] ?? 135;
                                $style = 'color: ' . $textColor . ';';
                                // Chá»‰ hiá»ƒn thá»‹ gradient náº¿u cÃ³ cáº£ 2 mÃ u gradient
                                if ($gradientColor1 && $gradientColor2) {
                                    // CÃ³ cáº£ 2 mÃ u gradient -> dÃ¹ng gradient
                                    $style .= ' background: linear-gradient(' . $gradientDegree . 'deg, ' . $gradientColor1 . ', ' . $gradientColor2 . ');';
                                } elseif ($bgColor && !$gradientColor2) {
                                    // Chỉ có background color (không có gradient color 2) -> dùng background color
                                    $style .= ' background-color: ' . $bgColor . ';';
                                }
                                // Náº¿u chá»‰ cÃ³ gradient color 2 mÃ  khÃ´ng cÃ³ background color -> khÃ´ng hiá»ƒn thá»‹ background
                                $bannerLink = $banner['link'] ?? '#';
                                $bannerTarget = $banner['link_target'] ?? '_self';
                                $bannerRel = $banner['link_rel'] ?? '';
                                $buttonLink = !empty($banner['button_text']) ? ($banner['button_link'] ?? $bannerLink) : null;
                            @endphp

                            <div class="banner-text-wrapper" style="{{ $style }}">
                                <div class="banner-text-content">
                                    @if(!empty($banner['title']))
                                        @if($bannerLink !== '#' && empty($banner['button_text']))
                                            <a href="{{ $bannerLink }}" target="{{ $bannerTarget }}" @if($bannerRel) rel="{{ $bannerRel }}" @endif class="banner-text-title-link">
                                                <h3 class="banner-text-title">{{ $banner['title'] }}</h3>
                                            </a>
                                        @else
                                            <h3 class="banner-text-title">{{ $banner['title'] }}</h3>
                                        @endif
                                    @endif
                                    <div class="banner-text-body-wrapper">
                                        @if(!empty($banner['content']))
                                            @if($bannerLink !== '#' && empty($banner['button_text']))
                                                <a href="{{ $bannerLink }}" target="{{ $bannerTarget }}" @if($bannerRel) rel="{{ $bannerRel }}" @endif class="banner-text-body-link">
                                                    <div class="banner-text-body">{!! nl2br(e($banner['content'])) !!}</div>
                                                </a>
                                            @else
                                                <div class="banner-text-body">{!! nl2br(e($banner['content'])) !!}</div>
                                            @endif
                                        @endif
                                        @if(!empty($banner['button_text']))
                                            @php
                                                $buttonTarget = $banner['button_target'] ?? '_self';
                                                $buttonRel = $banner['button_rel'] ?? '';
                                                $buttonBgColor = $banner['button_bg_color'] ?? '#2563eb';
                                                $buttonTextColor = $banner['button_text_color'] ?? '#ffffff';
                                                $buttonLink = $banner['button_link'] ?? ($bannerLink !== '#' ? $bannerLink : '#');

                                                // Button gradient logic
                                                $buttonGradientColor1 = $banner['button_bg_color'] ?? null;
                                                $buttonGradientColor2 = $banner['button_gradient_color'] ?? null;
                                                $buttonGradientDegree = $banner['button_gradient_degree'] ?? 135;

                                                $buttonStyle = 'color: ' . $buttonTextColor . ';';
                                                if ($buttonGradientColor1 && $buttonGradientColor2) {
                                                    $buttonStyle .= ' background: linear-gradient(' . $buttonGradientDegree . 'deg, ' . $buttonGradientColor1 . ', ' . $buttonGradientColor2 . ');';
                                                } elseif ($buttonBgColor && !$buttonGradientColor2) {
                                                    $buttonStyle .= ' background-color: ' . $buttonBgColor . ';';
                                                }

                                                // Button hover styles
                                                $buttonHoverBgColor = $banner['button_hover_bg_color'] ?? null;
                                                $buttonHoverTextColor = $banner['button_hover_text_color'] ?? null;
                                                $buttonHoverGradientColor1 = $banner['button_hover_bg_color'] ?? null;
                                                $buttonHoverGradientColor2 = $banner['button_hover_gradient_color'] ?? null;

                                                $buttonHoverStyle = '';
                                                if ($buttonHoverTextColor || $buttonHoverBgColor || $buttonHoverGradientColor2) {
                                                    if ($buttonHoverTextColor) {
                                                        $buttonHoverStyle .= 'color: ' . $buttonHoverTextColor . ';';
                                                    }
                                                    if ($buttonHoverGradientColor1 && $buttonHoverGradientColor2) {
                                                        $buttonHoverStyle .= ' background: linear-gradient(' . $buttonGradientDegree . 'deg, ' . $buttonHoverGradientColor1 . ', ' . $buttonHoverGradientColor2 . ');';
                                                    } elseif ($buttonHoverBgColor && !$buttonHoverGradientColor2) {
                                                        $buttonHoverStyle .= ' background-color: ' . $buttonHoverBgColor . ';';
                                                    }
                                                }
                                            @endphp
                                            <a
                                                href="{{ $buttonLink }}"
                                                target="{{ $buttonTarget }}"
                                                @if($buttonRel) rel="{{ $buttonRel }}" @endif
                                                class="banner-cta-button"
                                                style="{{ $buttonStyle }}"
                                                @if($buttonHoverBgColor) data-hover-bg="{{ $buttonHoverBgColor }}" @endif
                                                @if($buttonHoverTextColor) data-hover-color="{{ $buttonHoverTextColor }}" @endif
                                                @if($buttonHoverGradientColor1 && $buttonHoverGradientColor2) data-hover-background="linear-gradient({{ $buttonGradientDegree }}deg, {{ $buttonHoverGradientColor1 }}, {{ $buttonHoverGradientColor2 }})" @endif
                                            >
                                                {{ $banner['button_text'] }}
                                            </a>
                                        @endif
                                    </div>
                                    @if(!empty($banner['countdown_enabled']) && !empty($banner['countdown_date']))
                                        <div class="banner-countdown" data-countdown-date="{{ $banner['countdown_date'] }}">
                                            <div class="countdown-item">
                                                <span class="countdown-value" data-days>00</span>
                                                <span class="countdown-label">DAYS</span>
                                            </div>
                                            <div class="countdown-item">
                                                <span class="countdown-value" data-hours>00</span>
                                                <span class="countdown-label">HOURS</span>
                                            </div>
                                            <div class="countdown-item">
                                                <span class="countdown-value" data-minutes>00</span>
                                                <span class="countdown-label">MINUTES</span>
                                            </div>
                                            <div class="countdown-item">
                                                <span class="countdown-value" data-seconds>00</span>
                                                <span class="countdown-label">SECONDS</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Image Banner --}}
                            @if(!empty($banner['link']))
                                <a href="{{ $banner['link'] }}" target="{{ $banner['link_target'] ?? '_self' }}" @if(!empty($banner['link_rel'])) rel="{{ $banner['link_rel'] }}" @endif class="banner-link">
                                    <img
                                        src="{{ $banner['image_url'] }}"
                                        alt="{{ $banner['title'] ?? 'Banner' }}"
                                        class="banner-image"
                                        loading="lazy"
                                    >
                                    @if(!empty($banner['title']))
                                        <div class="banner-overlay">
                                            <h3 class="banner-title">{{ $banner['title'] }}</h3>
                                            @if(!empty($banner['description']))
                                                <p class="banner-description">{{ $banner['description'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </a>
                            @else
                                <div class="banner-image-wrapper">
                                    <img
                                        src="{{ $banner['image_url'] }}"
                                        alt="{{ $banner['title'] ?? 'Banner' }}"
                                        class="banner-image"
                                        loading="lazy"
                                    >
                                    @if(!empty($banner['title']))
                                        <div class="banner-overlay">
                                            <h3 class="banner-title">{{ $banner['title'] }}</h3>
                                            @if(!empty($banner['description']))
                                                <p class="banner-description">{{ $banner['description'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif

                        {{-- Admin Edit Icon - Will be shown/hidden by JavaScript based on auth status --}}
                        @if(!empty($banner['id']))
                            @php
                                // Generate admin URL for Vue router
                                $adminBaseUrl = url('/admin');
                                $editUrl = $adminBaseUrl . '/banner-slider/banners/' . $banner['id'] . '/edit';
                            @endphp
                            <a
                                href="{{ $editUrl }}"
                                class="banner-edit-icon"
                                data-banner-id="{{ $banner['id'] }}"
                                title="Edit Banner"
                                target="_blank"
                                onclick="event.stopPropagation();"
                                style="display: none;"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            @if(count($banners) > 1)
                @if($showNavigation)
                    <button class="banner-slider-prev" aria-label="Previous banner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="banner-slider-next" aria-label="Next banner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif

                @if($showIndicators)
                    <div class="banner-slider-dots">
                        @foreach($banners as $index => $banner)
                            <button
                                class="banner-dot {{ $index === 0 ? 'active' : '' }}"
                                data-slide="{{ $index }}"
                                aria-label="Go to slide {{ $index + 1 }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
@endif

@if(!empty($banners) && count($banners) > 0)
    <script src="{{ asset('modules/banner-slider/js/banner-slider.js') }}"></script>
@endif


