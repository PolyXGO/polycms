<footer class="footer">
    <div class="container">
        <div class="footer-content">
            {{-- About Section --}}
            <div class="footer-section">
                <h3>About</h3>
                <p>{{ $tagline ?? 'Just another PolyCMS site' }}</p>
                <p class="mt-md">
                    <small>Powered by <a href="https://polycms.com" class="footer-link">PolyCMS</a></small>
                </p>
            </div>
            
            {{-- Quick Links --}}
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="{{ url('/') }}" class="footer-link">Home</a>
                <a href="{{ url('/posts') }}" class="footer-link">Blog</a>
                <a href="{{ url('/products') }}" class="footer-link">Products</a>
                <a href="{{ url('/#about') }}" class="footer-link">About Us</a>
                <a href="{{ url('/#contact') }}" class="footer-link">Contact</a>
            </div>
            
            {{-- Contact Info --}}
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Get in touch with us for more information.</p>
                <p class="mt-md">
                    <small>We're here to help with any questions you may have.</small>
                </p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} {{ $site_title ?? config('app.name', 'PolyCMS') }}. All rights reserved.</p>
        </div>
    </div>
</footer>
