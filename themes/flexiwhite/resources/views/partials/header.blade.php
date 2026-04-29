<header class="header">
    <div class="container header-inner">
        <!-- Site Branding -->
        <div>
            @if(theme_get_option('site_logo'))
                <a href="{{ url('/') }}" class="logo">
                    <img src="{{ get_image_url(theme_get_option('site_logo')) }}" alt="{{ $site_title ?? config('app.name') }}" style="max-height: 40px; width: auto;">
                </a>
            @else
                <a href="{{ url('/') }}" class="logo">
                    {{ $site_title ?? config('app.name', 'PolyCMS') }}
                </a>
            @endif
        </div>

        <!-- Desktop Navigation -->
        @php 
            $headerMenu = theme_menu('header'); 
            $menuAlign = theme_get_option('flexiwhite_header_menu_align', 'right');
        @endphp
        <nav class="nav-links align-{{ $menuAlign }}">
            @if($headerMenu && $headerMenu->items->isNotEmpty())
                @foreach($headerMenu->items as $item)
                    @include('partials.menu-item', ['item' => $item, 'isRoot' => true])
                @endforeach
            @else
                @php
                    $postsArchive = trim(theme_permalink_structure()['posts']['archive'] ?? 'posts', '/');
                @endphp
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">{{ _l('Home') }}</a>
                <a href="{{ url('/' . $postsArchive) }}" class="nav-link {{ request()->is($postsArchive . '*') ? 'active' : '' }}">{{ _l('Blog') }}</a>
            @endif
        </nav>

        <!-- Mobile Toggle -->
        <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="{{ _l('Toggle navigation') }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- CTA / Auth -->
        @guest
        <div class="header-actions">
            <a href="/login" class="btn btn-primary">{{ _l('Log In') }}</a>
        </div>
        @endguest
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobile-menu">
        <div class="container">
            @if($headerMenu && $headerMenu->items->isNotEmpty())
                @foreach($headerMenu->items as $item)
                    @include('partials.menu-item-mobile', ['item' => $item])
                @endforeach
            @endif
        </div>
    </div>
</header>
