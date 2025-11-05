<header class="bg-white dark:bg-gray-800 shadow-sm">
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
            {{-- Logo / Site Title --}}
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $site_title ?? config('app.name', 'PolyCMS') }}
                </a>
                @if(!empty($tagline))
                    <span class="ml-4 text-sm text-gray-500 dark:text-gray-400 hidden md:inline">
                        {{ $tagline }}
                    </span>
                @endif
            </div>
            
            {{-- Navigation --}}
            <nav class="hidden md:flex items-center space-x-6">
                <a href="{{ url('/') }}" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Home
                </a>
                <a href="{{ url('/posts') }}" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Blog
                </a>
                <a href="{{ url('/products') }}" class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Products
                </a>
                {{-- Add more menu items here or use dynamic menu system --}}
            </nav>
            
            {{-- Mobile Menu Toggle --}}
            <button class="md:hidden p-2 text-gray-700 dark:text-gray-300" id="mobile-menu-toggle">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
    
    {{-- Mobile Menu --}}
    <div class="md:hidden hidden border-t border-gray-200 dark:border-gray-700" id="mobile-menu">
        <div class="container mx-auto px-4 py-4 space-y-2">
            <a href="{{ url('/') }}" class="block text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                Home
            </a>
            <a href="{{ url('/posts') }}" class="block text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                Blog
            </a>
            <a href="{{ url('/products') }}" class="block text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                Products
            </a>
        </div>
    </div>
</header>
