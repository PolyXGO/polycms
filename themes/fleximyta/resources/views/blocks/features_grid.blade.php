@php
    $containerClass = !empty($attrs['viewport_full_width']) ? 'container-fluid' : 'container';
@endphp

<section class="features" id="features">
    <div class="{{ $containerClass }}">
        <div class="section-title">
            <h2>{{ $attrs['heading'] ?? 'Why Choose FlexiMyTa' }}</h2>
            <p>{{ $attrs['subheading'] ?? '' }}</p>
        </div>
        <div class="features-grid" style="grid-template-columns: repeat({{ $attrs['columns'] ?? 3 }}, 1fr);">
            @foreach($attrs['features'] ?? [] as $feature)
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="{{ $feature['icon'] ?? 'fas fa-star' }}"></i>
                </div>
                <h3>{{ $feature['title'] ?? '' }}</h3>
                <p>{{ $feature['description'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
