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

        <!-- Header Controls (Right Side on Mobile) -->
        <div class="header-controls">
            @if(theme_widget_area_has_content('header_controls'))
                <div class="header-widget-area" data-widget-area="header_controls">
                    {!! app('widget')->renderArea('header_controls') !!}
                </div>
            @endif

            <!-- Mini Cart Root -->
            <div id="mini-cart-root"></div>

            <!-- Dark Mode Toggle -->
            @if(theme_get_option('flexiwhite_dark_mode_toggle', true))
                <style>
                    .header-controls {
                        display: flex;
                        align-items: center;
                        gap: 0.75rem;
                    }
                    .fw-theme-toggle {
                        display: none; /* Hidden by default to prevent flicker, revealed via JS if guest */
                        align-items: center; 
                        justify-content: center; 
                        width: 40px; 
                        height: 40px; 
                        border-radius: 50%; 
                        background-color: #f3f4f6; 
                        border: none; 
                        cursor: pointer; 
                        color: #374151;
                        transition: all 0.2s;
                        margin-right: 0;
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

                    #front-login-btn {
                        margin-right: 0;
                    }
                    #front-login-btn .btn {
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                    }
                    #front-login-btn .btn-icon {
                        display: none;
                    }

                    @media (max-width: 768px) {
                        .header-controls {
                            margin-left: auto;
                        }
                        .fw-theme-toggle {
                            width: 36px;
                            height: 36px;
                            margin-right: 0;
                        }
                        #front-login-btn .btn {
                            width: 36px;
                            height: 36px;
                            border-radius: 50%;
                            padding: 0 !important;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            background-color: #f3f4f6;
                            color: #374151;
                            border: none;
                            box-shadow: none;
                        }
                        #front-login-btn .btn-text {
                            display: none;
                        }
                        #front-login-btn .btn-icon {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        html.dark #front-login-btn .btn {
                            background-color: #1f2937;
                            color: #d1d5db;
                        }
                        html.dark #front-login-btn .btn:hover {
                            background-color: #374151;
                        }
                    }

                    /* Mobile Menu Harmonization */
                    .mobile-menu {
                        background: var(--geist-background) !important;
                        padding: 1.5rem 0 3rem 0 !important;
                    }
                    .mobile-menu .container {
                        padding: 0 1.25rem;
                    }
                    .mobile-menu-item {
                        border-bottom: 1px solid var(--geist-accents-2);
                        padding: 0.25rem 0;
                    }
                    .mobile-menu-item-search {
                        border-bottom: none !important;
                        padding: 0.75rem 0 1rem 0 !important;
                        margin-bottom: 0.5rem !important;
                        width: 100% !important;
                        box-sizing: border-box !important;
                        background: transparent !important;
                    }
                    .mobile-menu-item-search .widget,
                    .mobile-menu-item-search .widget-blog-search {
                        margin: 0 !important;
                        padding: 0 !important;
                        background: transparent !important;
                        border: none !important;
                        border-radius: 0 !important;
                        box-shadow: none !important;
                        width: 100% !important;
                    }
                    .mobile-menu-item-search .widget-search-form {
                        margin-top: 0 !important;
                        display: flex !important;
                        width: 100% !important;
                        box-sizing: border-box !important;
                        background: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                        padding: 0 !important;
                    }
                    .mobile-menu-item-search .widget-search-form input[type="text"],
                    .mobile-menu-item-search .widget-search-form input[type="search"] {
                        flex: 1 !important;
                        border-radius: 8px 0 0 8px !important;
                        border: 1px solid var(--geist-accents-2, #eaeaea) !important;
                        border-right: none !important;
                        background: var(--geist-background, #fff) !important;
                        color: var(--geist-foreground, #000) !important;
                        padding: 10px 14px !important;
                        height: 42px !important;
                        font-size: 0.95rem !important;
                        box-sizing: border-box !important;
                        box-shadow: none !important;
                    }
                    .mobile-menu-item-search .widget-search-form button,
                    .mobile-menu-item-search .widget-search-form button[type="submit"] {
                        border-radius: 0 8px 8px 0 !important;
                        border: 1px solid var(--geist-foreground, #000) !important;
                        background: var(--geist-foreground, #000) !important;
                        color: var(--geist-background, #fff) !important;
                        padding: 0 16px !important;
                        height: 42px !important;
                        font-weight: 600 !important;
                        font-size: 0.9rem !important;
                        cursor: pointer !important;
                        box-sizing: border-box !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        appearance: none !important;
                        -webkit-appearance: none !important;
                        box-shadow: none !important;
                    }
                    html.dark .mobile-menu-item-search .widget-search-form input[type="text"],
                    html.dark .mobile-menu-item-search .widget-search-form input[type="search"] {
                        background: rgba(255, 255, 255, 0.05) !important;
                        border-color: rgba(148, 163, 184, 0.2) !important;
                        color: #e2e8f0 !important;
                    }
                    html.dark .mobile-menu-item-search .widget-search-form button,
                    html.dark .mobile-menu-item-search .widget-search-form button[type="submit"] {
                        border-color: #3b82f6 !important;
                        background: #3b82f6 !important;
                        color: #fff !important;
                    }

                    /* Mobile Language Switcher Flat Grid styles (No card box) */
                    .mobile-language-switcher-grid {
                        margin-top: 12px !important;
                        margin-bottom: 8px !important;
                        padding: 0 !important;
                        border: none !important;
                        background: transparent !important;
                    }
                    .mobile-language-grid-item {
                        display: inline-flex !important;
                        flex-direction: row !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 8px !important;
                        padding: 10px 14px !important;
                        border-radius: 8px !important;
                        border: none !important;
                        background: transparent !important;
                        box-shadow: none !important;
                        text-decoration: none !important;
                        color: var(--geist-foreground, #000) !important;
                        text-align: center;
                        font-size: 0.9rem !important;
                        font-weight: 500 !important;
                        transition: all 0.2s !important;
                    }
                    .mobile-language-grid-item.active {
                        border: none !important;
                        background: transparent !important;
                        color: var(--primary-color, #3b82f6) !important;
                        font-weight: 700 !important;
                    }
                    html.dark .mobile-language-grid-item {
                        background: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                        color: #cbd5e1 !important;
                    }
                    html.dark .mobile-language-grid-item.active {
                        background: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                        color: #60a5fa !important;
                        font-weight: 700 !important;
                    }
                    .mobile-menu .nav-link {
                        padding: 0.75rem 0.5rem !important;
                        font-weight: 500;
                        font-size: 0.9375rem !important;
                    }
                    .mobile-submenu {
                        padding-left: 1.25rem !important;
                        margin-bottom: 0.5rem !important;
                    }
                    .mobile-submenu .mobile-menu-item {
                        border-bottom: none;
                        padding: 0.125rem 0;
                    }
                    .mobile-submenu .nav-link {
                        font-size: 0.875rem !important;
                        color: var(--geist-accents-5) !important;
                    }
                    .mobile-submenu .nav-link:hover,
                    .mobile-submenu .nav-link.active {
                        color: var(--geist-foreground) !important;
                        background: transparent !important;
                    }
                    .submenu-toggle {
                        padding: 10px 12px !important;
                        color: var(--geist-accents-4) !important;
                    }
                    .submenu-toggle.active {
                        color: var(--geist-foreground) !important;
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
                            // Toggle class manually (1-click guaranteed based on current state)
                            htmlEl.classList.toggle('dark');
                            
                            // Sync with BOTH potential localStorage keys used by core and theme
                            const newTheme = htmlEl.classList.contains('dark') ? 'dark' : 'light';
                            localStorage.setItem('theme_mode', newTheme);
                            localStorage.setItem('color-theme', newTheme);
                            
                            // Re-sync icons immediately and after a short delay
                            updateIcons();
                            setTimeout(updateIcons, 50);
                        });
                        
                        // Sync icons when classes change (e.g. by other scripts)
                        const observer = new MutationObserver(updateIcons);
                        observer.observe(htmlEl, { attributes: true, attributeFilter: ['class'] });
                        
                        // Check initial state
                        updateIcons();
                    });
                </script>
            @endif

            <!-- CTA / Auth -->
            <div class="header-actions" id="front-login-btn" style="display: none;">
                <a href="/login" class="btn btn-primary">
                    <span class="btn-text">{{ _l('Log In') }}</span>
                    <span class="btn-icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                </a>
            </div>

            <!-- Mobile Toggle -->
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="{{ _l('Toggle navigation') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <script>
            // Synchronous auth check to prevent UI flickering
            (function() {
                var isWebAuth = {{ Auth::guard('web')->check() ? 'true' : 'false' }};
                var hasApiToken = localStorage.getItem('auth_token');
                
                // If neither server-side session nor client-side API token exists, user is a true guest
                if (!isWebAuth && !hasApiToken) {
                    var themeToggle = document.getElementById('front-theme-toggle');
                    if (themeToggle) {
                        themeToggle.style.display = 'flex';
                    }
                    
                    var loginBtn = document.getElementById('front-login-btn');
                    if (loginBtn) {
                        loginBtn.style.display = 'block';
                    }
                }
            })();
        </script>
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
