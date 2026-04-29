@php
    $headerMenu = theme_menu('header');
    $postsArchiveUrl = theme_permalink_url('posts', '', 'archive');
    $productsArchiveUrl = theme_permalink_url('products', '', 'archive');
@endphp

<header id="main-header">
    <div class="{{ $containerClass ?? 'container' }}">
        <nav class="navbar">
            <a href="{{ url('/') }}" class="logo">{{ $site_title ?? config('app.name', 'FlexiMyTa') }}</a>
            
            {{-- Desktop Navigation --}}
            <div class="nav-links">
                @if($headerMenu && $headerMenu->items->isNotEmpty())
                    @foreach($headerMenu->items as $item)
                        @include('partials.menu-item', ['item' => $item, 'isRoot' => true])
                    @endforeach
                @else
                    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">{{ __('Home') }}</a>
                    <a href="{{ $postsArchiveUrl }}" class="{{ request()->is(trim(parse_url($postsArchiveUrl, PHP_URL_PATH), '/')) ? 'active' : '' }}">{{ __('Blog') }}</a>
                    <a href="{{ $productsArchiveUrl }}" class="{{ request()->is(trim(parse_url($productsArchiveUrl, PHP_URL_PATH), '/')) ? 'active' : '' }}">{{ __('Products') }}</a>
                @endif
            </div>


            {{-- Right side elements (Cart & Theme Toggle) --}}
            <div class="flex items-center">
                {{-- Mini Cart Root --}}
                <div id="mini-cart-root" class="ml-4"></div>
                
                {{-- Dark Mode Toggle --}}
                @if(get_theme_mod('fleximyta_dark_mode_toggle', true))
                    <style>
                        /* Hide the frontend theme toggle if the PolyCMS Admin Topbar is active to prevent duplication */
                        body.polycms-topbar-active #front-theme-toggle {
                            display: none !important;
                        }
                    </style>
                    <button id="front-theme-toggle" class="ml-4 flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition" aria-label="{{ __('Toggle Dark Mode') }}" style="border: none; cursor: pointer;">
                        <i class="fas fa-moon" id="front-icon-moon" style="display: none;"></i>
                        <i class="fas fa-sun" id="front-icon-sun" style="display: none;"></i>
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
                            
                            // Watch for class changes on HTML (in case topbar toggle is clicked)
                            const observer = new MutationObserver(updateIcons);
                            observer.observe(htmlEl, { attributes: true, attributeFilter: ['class'] });
                            
                            // Check initial state (sometimes set by inline script before DOM is loaded)
                            if (localStorage.getItem('color-theme') === 'dark' || (!localStorage.getItem('color-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                                htmlEl.classList.add('dark');
                            } else {
                                htmlEl.classList.remove('dark');
                            }
                            updateIcons();
                        });
                    </script>
                @endif
                
                <button class="mobile-menu-btn ml-4" id="mobile-menu-toggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </div>

    {{-- Mobile Menu --}}
    <div class="mobile-menu" id="mobile-menu">
        @if($headerMenu && $headerMenu->items->isNotEmpty())
            @foreach($headerMenu->items as $item)
                @include('partials.menu-item-mobile', ['item' => $item])
            @endforeach
        @else
            <a href="{{ url('/') }}">{{ __('Home') }}</a>
            <a href="{{ $postsArchiveUrl }}">{{ __('Blog') }}</a>
            <a href="{{ $productsArchiveUrl }}">{{ __('Products') }}</a>
        @endif
    </div>
</header>
