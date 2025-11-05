<footer class="bg-gray-800 dark:bg-gray-900 text-white mt-auto">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Footer Widget Area 1 --}}
            <div>
                <h3 class="text-lg font-semibold mb-4">About</h3>
                <p class="text-gray-300 dark:text-gray-400">
                    {{ $tagline ?? 'Just another PolyCMS site' }}
                </p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">
                    Powered by <a href="https://polycms.com" class="text-indigo-400 hover:text-indigo-300">PolyCMS</a>
                </p>
            </div>
            
            {{-- Footer Widget Area 2 --}}
            <div>
                <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-gray-300 dark:text-gray-400">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                    <li><a href="{{ url('/posts') }}" class="hover:text-white transition-colors">Blog</a></li>
                    <li><a href="{{ url('/products') }}" class="hover:text-white transition-colors">Products</a></li>
                </ul>
            </div>
            
            {{-- Footer Widget Area 3 --}}
            <div>
                <h3 class="text-lg font-semibold mb-4">Contact</h3>
                <p class="text-gray-300 dark:text-gray-400 text-sm">
                    Get in touch with us for more information.
                </p>
            </div>
        </div>
        
        <div class="border-t border-gray-700 dark:border-gray-800 mt-8 pt-6 text-center text-gray-400 dark:text-gray-500 text-sm">
            <p>&copy; {{ date('Y') }} {{ $site_title ?? config('app.name', 'PolyCMS') }}. All rights reserved.</p>
        </div>
    </div>
</footer>
