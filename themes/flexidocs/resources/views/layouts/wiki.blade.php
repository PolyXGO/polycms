<!DOCTYPE html>
<html lang="{{ $site_language ?? 'en' }}" dir="{{ $site_language_direction ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield('title', $site_title ?? config('app.name', 'PolyCMS'))
        @hasSection('title')
            - {{ $site_title ?? config('app.name', 'PolyCMS') }}
        @endif
    </title>
    <meta name="description" content="@yield('description', $tagline ?? '')">

    {!! \App\Facades\Hook::doAction('cms_head') !!}

    <!-- Theme Styles -->
    <link rel="stylesheet" href="{{ route('theme.asset', ['themeSlug' => 'flexidocs', 'path' => 'css/wiki.css']) }}?v={{ time() }}">
    @stack('theme-styles')
    
    <!-- Tailwind CSS (Development/CDN) -->
    <script src="{{ cdn_asset('assets/vendor/tailwindcss/cdn.min.js', 'https://cdn.tailwindcss.com?plugins=typography') }}?plugins=typography"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js for interactive toggles -->
    <script defer src="{{ cdn_asset('assets/vendor/alpinejs-3.x/cdn.min.js', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js') }}"></script>
    <style>
        :root {
            --primary-color: {{ get_theme_mod('primary_color', get_theme_mod('primary_color_hex', '#3b82f6')) }};
        }
        
        body.polycms-topbar-active .h-screen {
            height: calc(100vh - 32px);
        }

        /* ── Sidebar Filter Menu: client-side navigation filter ── */
        .wiki-sidebar-search {
            margin: 0.625rem 1.25rem 0;
        }

        .wiki-sidebar-search .widget-blog-search,
        .wiki-sidebar-search .widget-search-form {
            width: 100%;
        }

        .wiki-sidebar-search .widget-search-form--minimal {
            border-bottom: 1px solid rgba(148, 163, 184, 0.2) !important;
            padding: 0.125rem 0 !important;
        }

        .wiki-sidebar-search .widget-search-form--minimal:focus-within {
            border-bottom-color: var(--primary-color, #3b82f6) !important;
        }

        html.dark .wiki-sidebar-search .widget-search-form--minimal,
        .dark .wiki-sidebar-search .widget-search-form--minimal {
            border-bottom-color: rgba(51, 65, 85, 0.6) !important;
        }

        html.dark .wiki-sidebar-search .widget-search-form--minimal:focus-within,
        .dark .wiki-sidebar-search .widget-search-form--minimal:focus-within {
            border-bottom-color: var(--primary-color, #3b82f6) !important;
        }

        .wiki-sidebar-search .widget-search-form input[type="text"],
        .wiki-sidebar-search .widget-search-form input[type="search"] {
            padding: 0.35rem 0.1rem !important;
            font-size: 0.78rem !important;
            min-width: 0 !important;
        }

        .wiki-sidebar-search .widget-search-form--minimal input[type="text"]::placeholder,
        .wiki-sidebar-search .widget-search-form--minimal input[type="search"]::placeholder {
            color: #94a3b8;
            font-size: 0.75rem;
        }

        /* ── Sidebar filter match highlight ── */
        .sidebar-filter-hidden {
            display: none !important;
        }

        .sidebar-group-hidden > .wiki-group > ul,
        .sidebar-group-hidden > .wiki-group > .space-y-1 {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 antialiased" x-data="wikiToc()" x-init="initToc()">
    {{-- Banner Slider --}}
    @php
        $banners = \App\Facades\Hook::applyFilters('frontend.topbar.banners', []);
    @endphp
    @if(!empty($banners) && count($banners) > 0)
        @include('banner-slider::partials.banner-slider', ['banners' => $banners])
    @endif

    <x-topbar-menu />
    <!-- Clean Documentation Header -->
    <header class="fixed w-full h-[61px] top-0 left-0 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 z-30">
        <div class="px-4 py-3 flex items-center justify-between h-full relative">
            <div class="flex items-center gap-3 relative z-10 w-1/3">
                @if(!View::hasSection('hide_sidebar'))
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 -ml-2 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 lg:hidden text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block p-1.5 -ml-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    </button>
                @endif
                <a href="/" class="font-bold text-lg text-blue-600 dark:text-blue-400 shrink-0">{{ $site_title ?? 'PolyCMS Docs' }}</a>
            </div>
            
            <!-- Global Dynamic Active Heading Text -->
            <div class="absolute inset-0 hidden lg:flex items-center justify-center pointer-events-none px-4">
                <span x-cloak x-show="activeHeadingText" x-transition x-text="activeHeadingText" class="font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider text-[11px] lg:text-xs truncate max-w-lg text-center"></span>
            </div>

            <div class="flex items-center justify-end gap-3 text-sm font-medium relative z-10 w-1/3">
                @php
                    $headerSearchEnabled = function_exists('get_theme_mod') ? (bool) get_theme_mod('flexidocs_header_search_enabled', true) : true;
                    $headerSearchStyle = function_exists('get_theme_mod') ? (string) get_theme_mod('flexidocs_header_search_style', 'icon_modal') : 'icon_modal';
                    $headerSearchTriggerVariant = function_exists('get_theme_mod') ? (string) get_theme_mod('flexidocs_header_search_trigger_variant', 'icon') : 'icon';
                    $headerSearchPlaceholder = function_exists('get_theme_mod') ? (string) get_theme_mod('flexidocs_header_search_placeholder', _l('Search documentation...')) : _l('Search documentation...');
                @endphp
                @if($headerSearchEnabled)
                    @php
                        $docsHeaderSearch = new \App\Models\WidgetInstance();
                        $docsHeaderSearch->id = 'flexidocs-header-search';
                        $docsHeaderSearch->title = _l('Search');
                        $docsHeaderSearch->config = [
                            'display_style' => in_array($headerSearchStyle, ['icon_modal', 'icon_expand', 'icon_inline'], true) ? $headerSearchStyle : 'icon_modal',
                            'trigger_variant' => in_array($headerSearchTriggerVariant, ['button', 'icon'], true) ? $headerSearchTriggerVariant : 'icon',
                            'placeholder' => $headerSearchPlaceholder,
                            'button_label' => _l('Search'),
                            'search_target' => 'posts',
                            'suggestions_enabled' => true,
                            'suggestion_scope' => 'posts',
                            'suggestion_limit' => 6,
                            'show_title' => false,
                        ];
                        $docsHeaderSearchWidget = new \App\Widgets\BlogSearchWidget();
                    @endphp
                    <div class="wiki-header-search shrink-0">
                        {!! $docsHeaderSearchWidget->render($docsHeaderSearch) !!}
                    </div>
                @endif
                @php $headerMenu = theme_menu('header'); @endphp
                @if($headerMenu && $headerMenu->items->isNotEmpty())
                    @foreach($headerMenu->items as $item)
                        @if($item->type === 'language')
                            @include('theme.flexidocs::partials.menu-item', ['item' => $item, 'isRoot' => true])
                        @endif
                    @endforeach
                @endif
                <a href="/" class="text-gray-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 shrink-0">{{ _l('Back to Main Site') }}</a>
            </div>
        </div>
    </header>

    <div class="flex flex-col lg:flex-row h-screen pt-[61px] overflow-hidden box-border bg-white dark:bg-slate-900 w-full">
        <!-- Sidebar Navigation Tree -->
        @if(!View::hasSection('hide_sidebar'))
        <!-- Mobile Overlay -->
        <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="absolute inset-0 bg-slate-900/50 z-10 lg:hidden h-full w-full"></div>

        <aside 
            x-cloak
            x-show="sidebarOpen" 
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="w-[80%] max-w-sm lg:max-w-none lg:w-72 flex-shrink-0 border-r border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 absolute lg:static z-20 h-full flex flex-col box-border shadow-2xl lg:shadow-none"
        >
            @php
                $sidebar_title = $site_title ?? 'Documentation';
                if(isset($category)) {
                    $sidebar_title = $category->name;
                } elseif(isset($post) && $post->categories->isNotEmpty()) {
                    $sidebar_title = $post->categories->first()->name;
                }
            @endphp
            <div class="p-6 pb-4 border-b border-gray-200 dark:border-slate-800 shrink-0">
                <h3 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] lg:text-xs truncate" title="{{ $sidebar_title }}">
                    {{ $sidebar_title }}
                </h3>
            </div>

            @php
                $sidebarSearchEnabled = function_exists('get_theme_mod') ? (bool) get_theme_mod('flexidocs_sidebar_search_enabled', true) : true;
                $sidebarSearchPlaceholder = function_exists('get_theme_mod') ? (string) get_theme_mod('flexidocs_sidebar_search_placeholder', _l('Filter menu...')) : _l('Filter menu...');
            @endphp
            @if($sidebarSearchEnabled)
                <div class="wiki-sidebar-search shrink-0">
                    <div class="widget-blog-search" data-sidebar-filter>
                        <form class="widget-search-form widget-search-form--minimal" onsubmit="return false;">
                            <input type="text" inputmode="search" enterkeyhint="search" autocomplete="off" spellcheck="false" placeholder="{{ e($sidebarSearchPlaceholder) }}" data-sidebar-filter-input aria-label="{{ e(_l('Filter menu')) }}" />
                        </form>
                    </div>
                </div>
            @endif
            
            <div class="p-6 flex-1 overflow-y-auto w-full" data-sidebar-filter-container>
                @yield('sidebar')
            </div>
            
            @if(theme_widget_area_has_content('wiki_sidebar'))
                <div class="p-4 mt-8 border-t border-gray-200 dark:border-slate-700">
                    {!! \App\Facades\Widget::renderArea('wiki_sidebar') !!}
                </div>
            @endif
        </aside>
        @endif

        <!-- Center Content Area -->
        <main class="flex-1 min-w-0 h-full flex flex-col transition-all duration-300 relative border-x border-gray-100 dark:border-slate-800">
            <!-- Scrollable Content -->
            <div class="wiki-scroll-container flex-1 overflow-y-auto scroll-smooth relative">
                <div class="@yield('wrapper_class', 'px-6 py-8 lg:px-10 lg:py-10')">
                    @yield('content')
                </div>
            </div>
            
            <!-- Scroll Tools -->
            <div class="flex absolute right-4 lg:right-6 top-1/2 -translate-y-1/2 flex-col gap-2 z-40 pointer-events-none">
                <button onclick="document.querySelector('.wiki-scroll-container').scrollTo({top: 0, behavior: 'smooth'})" class="pointer-events-auto w-9 h-9 flex items-center justify-center rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white shadow-sm hover:shadow hover:bg-slate-50 dark:hover:bg-slate-700 transition-all" aria-label="{{ _l('Scroll to top') }}" title="{{ _l('Scroll to top') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                </button>
                <button onclick="document.querySelector('.wiki-scroll-container').scrollTo({top: document.querySelector('.wiki-scroll-container').scrollHeight, behavior: 'smooth'})" class="pointer-events-auto w-9 h-9 flex items-center justify-center rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white shadow-sm hover:shadow hover:bg-slate-50 dark:hover:bg-slate-700 transition-all" aria-label="{{ _l('Scroll to bottom') }}" title="{{ _l('Scroll to bottom') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </button>
            </div>
        </main>

        <!-- Right TOC Sidebar (Visible on xl+ screens) -->
        @hasSection('toc')
        <aside class="hidden xl:flex w-72 flex-shrink-0 border-l border-gray-200 dark:border-slate-700 h-full flex-col bg-gray-50 dark:bg-slate-900 box-border">
            <div class="p-6 pb-4 border-b border-gray-200 dark:border-slate-800 shrink-0">
                <h3 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] lg:text-xs">
                    On this page
                </h3>
            </div>
            <div class="p-6 pt-4 flex-1 overflow-y-auto w-full">
                @yield('toc')
            </div>
        </aside>
        @endif
    </div>
    
    <!-- Theme Scripts -->
    <script src="{{ route('theme.asset', ['themeSlug' => 'flexidocs', 'path' => 'js/wiki.js']) }}?v={{ time() }}"></script>
    @stack('theme-scripts')
    @includeIf('system.partials.theme-assets')
</body>
</html>
