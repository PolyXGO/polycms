<header class="header" id="main-header">
    <div class="container">
        <div class="header-content">
            {{-- Logo / Site Title --}}
            <a href="{{ url('/') }}" class="logo">
                <x-brand-logo class="logo-image" style="max-height: 40px;" />
            </a>
            
            {{-- Navigation --}}
            <nav class="nav">
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    {{ _l('Home') }}
                </a>
                <a href="{{ url('/posts') }}" class="nav-link {{ request()->is('posts*') ? 'active' : '' }}">
                    {{ _l('Blog') }}
                </a>
                <a href="{{ url('/products') }}" class="nav-link {{ request()->is('products*') ? 'active' : '' }}">
                    {{ _l('Products') }}
                </a>
                <a href="{{ url('/#about') }}" class="nav-link">
                    {{ _l('About') }}
                </a>
                <a href="{{ url('/#contact') }}" class="nav-link">
                    {{ _l('Contact') }}
                </a>
            </nav>
            
            {{-- Mobile Menu Toggle --}}
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="{{ _l('Toggle menu') }}">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
        
        {{-- Mobile Menu --}}
        <div class="mobile-menu" id="mobile-menu">
            <a href="{{ url('/') }}" class="nav-link">{{ _l('Home') }}</a>
            <a href="{{ url('/posts') }}" class="nav-link">{{ _l('Blog') }}</a>
            <a href="{{ url('/products') }}" class="nav-link">{{ _l('Products') }}</a>
            <a href="{{ url('/#about') }}" class="nav-link">{{ _l('About') }}</a>
            <a href="{{ url('/#contact') }}" class="nav-link">{{ _l('Contact') }}</a>
        </div>
    </div>
</header>
