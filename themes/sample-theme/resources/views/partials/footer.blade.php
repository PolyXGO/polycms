<footer class="footer">
    <div class="container">
        <div class="footer-content">
            {{-- About Section --}}
            <div class="footer-section">
                <h3>{{ _l('About') }}</h3>
                <p>{{ $tagline ?? _l('Just another PolyCMS site') }}</p>
                <p class="mt-md">
                    <small>{{ _l('Powered by') }} <a href="https://polycms.com" class="footer-link">PolyCMS</a></small>
                </p>
            </div>
            
            {{-- Quick Links --}}
            <div class="footer-section">
                <h3>{{ _l('Quick Links') }}</h3>
                <a href="{{ url('/') }}" class="footer-link">{{ _l('Home') }}</a>
                <a href="{{ url('/posts') }}" class="footer-link">{{ _l('Blog') }}</a>
                <a href="{{ url('/products') }}" class="footer-link">{{ _l('Products') }}</a>
                <a href="{{ url('/#about') }}" class="footer-link">{{ _l('About Us') }}</a>
                <a href="{{ url('/#contact') }}" class="footer-link">{{ _l('Contact') }}</a>
            </div>
            
            {{-- Contact Info --}}
            <div class="footer-section">
                <h3>{{ _l('Contact') }}</h3>
                <p>{{ _l('Get in touch with us for more information.') }}</p>
                <p class="mt-md">
                    <small>{{ _l('We\'re here to help with any questions you may have.') }}</small>
                </p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} {{ $site_title ?? config('app.name', 'PolyCMS') }}. {{ _l('All rights reserved.') }}</p>
        </div>
    </div>
</footer>
