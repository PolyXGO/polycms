<header class="bg-white dark:bg-gray-800 shadow">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ url('/') }}" class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $site_title ?? config('app.name', 'PolyCMS') }}
                </a>
            </div>
            
            {{-- Desktop Navigation --}}
            @php $headerMenu = theme_menu('header'); @endphp
            <nav class="hidden md:block">
                <ul class="flex space-x-4">
                    @if($headerMenu && $headerMenu->items->isNotEmpty())
                        @foreach($headerMenu->items as $item)
                            @include('partials.menu-item', ['item' => $item, 'isRoot' => true])
                        @endforeach
                    @else
                        <li><a href="{{ url('/') }}" class="nav-link">{{ _l('Home') }}</a></li>
                        <li><a href="{{ url('/posts') }}" class="nav-link">{{ _l('Blog') }}</a></li>
                    @endif
                </ul>
            </nav>

            {{-- Mobile Toggle --}}
            <button class="md:hidden p-2 text-gray-600 dark:text-gray-400" id="mobile-menu-toggle">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="pt-2 pb-4 space-y-1">
                @if($headerMenu && $headerMenu->items->isNotEmpty())
                    @foreach($headerMenu->items as $item)
                        @include('partials.menu-item-mobile', ['item' => $item])
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</header>
