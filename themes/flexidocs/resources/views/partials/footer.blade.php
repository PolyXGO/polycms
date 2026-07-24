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
        @php
            $footerCopyright = theme_get_option('flexidocs_footer_copyright', '');
            $footerPoweredBy = theme_get_option('flexidocs_footer_powered_by', 'show');
        @endphp
        <div class="mt-8 pt-8 border-t border-gray-700 text-center text-sm footer-copyright">
            @if(!empty($footerCopyright))
                {!! $footerCopyright !!}
            @else
                <p class="inline">&copy; {{ date('Y') }} {{ $site_title ?? config('app.name', 'PolyCMS') }}. {{ _l('All rights reserved.') }}</p>
            @endif

            @if($footerPoweredBy !== 'hide')
                <span class="footer-powered" style="margin-left: 8px;">
                    {{ _l('Powered by') }} <a href="https://polycms.org" target="_blank" rel="noopener" class="underline hover:text-white">PolyCMS</a>
                </span>
            @endif

            @if(auth()->check() && auth()->user()->hasRole('admin'))
                <a href="{{ url('/admin/themes/options') }}" class="footer-edit-link" title="{{ _l('Edit Theme Options') }}" style="display: inline-flex; align-items: center; justify-content: center; margin-left: 8px; vertical-align: middle; color: #a1a1aa; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#a1a1aa'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</footer>
