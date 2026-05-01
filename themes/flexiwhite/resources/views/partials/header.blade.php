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

        <!-- Dark Mode Toggle -->
        @if(theme_get_option('flexiwhite_dark_mode_toggle', true))
            <style>
                /* Hide the frontend theme toggle if the PolyCMS Admin Topbar is active to prevent duplication */
                body.polycms-topbar-active #front-theme-toggle {
                    display: none !important;
                }
                .fw-theme-toggle {
                    margin-left: 1rem;
                    margin-right: 0.5rem; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    width: 40px; 
                    height: 40px; 
                    border-radius: 50%; 
                    background-color: #f3f4f6; 
                    border: none; 
                    cursor: pointer; 
                    color: #374151;
                    transition: background-color 0.2s;
                }
                .fw-theme-toggle:hover {
                    background-color: #e5e7eb;
                }
                html.dark .fw-theme-toggle {
                    background-color: #1f2937;
                    color: #d1d5db;
                }
                html.dark .fw-theme-toggle:hover {
                    background-color: #374151;
                }
            </style>
            <button id="front-theme-toggle" class="fw-theme-toggle" aria-label="{{ _l('Toggle Dark Mode') }}">
                <svg id="front-icon-moon" style="display: none; width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                <svg id="front-icon-sun" style="display: none; width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </button>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toggleBtn = document.getElementById('front-theme-toggle');
                    const moonIcon = document.getElementById('front-icon-moon');
                    const sunIcon = document.getElementById('front-icon-sun');
                    const htmlEl = document.documentElement;
                    
                    function updateIcons() {
                        if (htmlEl.classList.contains('dark')) {
                            moonIcon.style.display = 'none';
                            sunIcon.style.display = 'block';
                        } else {
                            sunIcon.style.display = 'none';
                            moonIcon.style.display = 'block';
                        }
                    }
                    
                    toggleBtn.addEventListener('click', function() {
                        if (typeof window.handleThemeToggle === 'function') {
                            window.handleThemeToggle();
                        } else {
                            htmlEl.classList.toggle('dark');
                            if (htmlEl.classList.contains('dark')) {
                                localStorage.setItem('color-theme', 'dark');
                            } else {
                                localStorage.setItem('color-theme', 'light');
                            }
                        }
                        setTimeout(updateIcons, 10);
                    });
                    
                    const observer = new MutationObserver(updateIcons);
                    observer.observe(htmlEl, { attributes: true, attributeFilter: ['class'] });
                    
                    // Check initial state
                    updateIcons();
                });
            </script>
        @endif

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
