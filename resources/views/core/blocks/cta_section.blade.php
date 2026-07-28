@php
    $containerClass = !empty($attrs['viewport_full_width']) ? 'container-fluid' : 'container';
@endphp

<section class="cta-section" id="contact">
    <div class="{{ $containerClass }}">
        <h2>{{ $attrs['heading'] ?? 'Ready to Launch Your SaaS Business?' }}</h2>
        <p>{{ $attrs['text'] ?? '' }}</p>
        
        <p style="font-size:16px; opacity:0.95; margin-bottom:20px; text-align:center;">
            {{ $attrs['info_text'] ?? '' }}
        </p>
        
        <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 25px; color:#212529">
            {!! $attrs['form_html'] ?? '<!-- Form Placeholder -->' !!}
        </div>

        <div class="stats">
            @foreach($attrs['stats'] ?? [] as $stat)
            <div class="stat-item">
                <div class="stat-number">{{ $stat['number'] ?? '' }}</div>
                <div>{{ $stat['label'] ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>
