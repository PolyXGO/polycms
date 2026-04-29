@php
    $containerClass = !empty($attrs['viewport_full_width']) ? 'container-fluid' : 'container';
@endphp

<section class="what-you-get" id="what-you-get">
    <div class="{{ $containerClass }}">
            <div class="get-title">
                <h2>{{ $attrs['heading'] ?? "Here's Exactly What You Get" }}</h2>
                <p>{{ $attrs['subheading'] ?? "A complete invoice SaaS solution that saves you 6+ months of development time" }}</p>
            </div>
            @if(!empty($attrs['button_text']))
            <div class="landing-btn-wrap landing-btn-wrap--center">
                <a
                    href="{{ $attrs['button_link'] ?? url('/posts') }}"
                    target="_blank"
                    class="landing-btn landing-btn-primary px-6 py-3 text-base"
                >{{ $attrs['button_text'] }}</a>
            </div>
            @endif
            <div class="features-list">
                @foreach($attrs['features'] ?? [] as $feature)
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>{{ $feature }}</strong>
                    </div>
                </div>
                @endforeach
                
                @if($attrs['show_highlight'] ?? true)
                <!-- Subscription Billing Highlight -->
                <div class="subscription-highlight">
                    <h4>{{ $attrs['highlight_title'] ?? '💰 Automated Subscription Payments' }}</h4>
                    <p>{!! nl2br(e($attrs['highlight_text'] ?? '')) !!}</p>
                </div>
                @endif
            </div>
            
            @if(!empty($attrs['banner_title']))
            <div class="ownership-banner">
                <h3>{{ $attrs['banner_title'] }}</h3>
                <p>{{ $attrs['banner_text'] ?? '' }}</p>
            </div>
            @endif
    </div>
</section>
