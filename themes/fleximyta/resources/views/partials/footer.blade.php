<footer>
    <div class="{{ $containerClass ?? 'container' }}">
        <div class="footer-content">
            <div class="footer-column">
                <h3>{{ $site_title ?? config('app.name', 'FlexiMyTa') }}</h3>
                <p style="color: #aaa; margin-bottom: 20px; font-size: 14px;">{{ $tagline ?? 'A modern, flexible blog theme for content creators.' }}</p>
            </div>
            <div class="footer-column">
                <h3>{{ __('Quick Links') }}</h3>
                <ul class="footer-links">
                    <li><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
                    <li><a href="{{ url('/posts') }}">{{ __('Blog') }}</a></li>
                    <li><a href="{{ url('/products') }}">{{ __('Products') }}</a></li>
                </ul>
            </div>
            <div class="footer-column">
                @if(theme_widget_area_has_content('footer'))
                    {!! \App\Facades\Widget::renderArea('footer') !!}
                @else
                    <h3>{{ __('About') }}</h3>
                    <p style="color: #aaa; font-size: 14px;">{{ __('Modern blog theme with clean design and responsive layout.') }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="copyright">
            <p>&copy; {{ date('Y') }} {{ $site_title ?? config('app.name', 'FlexiMyTa') }}. {{ __('All rights reserved.') }}</p>
        </div>
</footer>
