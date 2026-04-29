<footer class="footer">
    <div class="container footer-inner">
        <div class="footer-copyright">
            @php
                $footerCopyright = theme_get_option('flexiwhite_footer_copyright', '');
                $footerPoweredBy = theme_get_option('flexiwhite_footer_powered_by', 'show');
            @endphp

            @if(!empty($footerCopyright))
                {!! $footerCopyright !!}
            @else
                &copy; {{ date('Y') }} {{ $site_title ?? config('app.name', 'PolyCMS') }}. {{ _l('All rights reserved.') }}
            @endif

            @if($footerPoweredBy !== 'hide')
                <span class="footer-powered">
                    {{ _l('Powered by') }} <a href="https://polycms.org" target="_blank" rel="noopener">PolyCMS</a>
                </span>
            @endif
        </div>

        <div class="footer-links">
            @php
                $footerMenu = theme_menu('footer');
            @endphp

            @if($footerMenu && $footerMenu->items->isNotEmpty())
                @foreach($footerMenu->items as $item)
                    <a href="{{ $item->effective_url ?? '#' }}" class="nav-link"{{ $item->target === '_blank' ? ' target="_blank" rel="noopener"' : '' }}>
                        {{ $item->title }}
                    </a>
                @endforeach
            @else
                <a href="{{ url('/') }}" class="nav-link">{{ _l('Home') }}</a>
                <a href="{{ url('/posts') }}" class="nav-link">{{ _l('Blog') }}</a>
                <a href="#" class="nav-link">{{ _l('Privacy Policy') }}</a>
                <a href="#" class="nav-link">{{ _l('Terms of Service') }}</a>
            @endif
        </div>
    </div>
</footer>
