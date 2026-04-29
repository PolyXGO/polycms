<footer class="bg-gray-800 dark:bg-gray-900 text-gray-300 dark:text-gray-400 mt-12">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-white dark:text-white font-semibold mb-4">{{ _l('About') }}</h3>
                <p class="text-sm">{{ $tagline ?? 'Just another PolyCMS site' }}</p>
            </div>
            <div>
                <h3 class="text-white dark:text-white font-semibold mb-4">{{ _l('Links') }}</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="hover:text-white">{{ _l('Home') }}</a></li>
                    <li><a href="{{ url('/posts') }}" class="hover:text-white">{{ _l('Blog') }}</a></li>
                    <li><a href="{{ url('/products') }}" class="hover:text-white">{{ _l('Products') }}</a></li>
                </ul>
            </div>
            <div>
                @if(theme_widget_area_has_content('footer_col_1'))
                    {!! \App\Facades\Widget::renderArea('footer_col_1') !!}
                @endif
            </div>
        </div>
        <div class="mt-8 pt-8 border-t border-gray-700 text-center text-sm">
            <p>&copy; {{ date('Y') }} {{ $site_title ?? config('app.name', 'PolyCMS') }}. All rights reserved.</p>
        </div>
    </div>
</footer>
