@php
use App\Services\TopbarMenuService;
use App\Services\SettingsService;
$topbarService = app(TopbarMenuService::class);
$settingsService = app(SettingsService::class);

// Always render topbar but hide by default, JavaScript will show it if user is authenticated
$menuItems = $topbarService->getGroupedMenuItems();

// Get permalink structure for route detection
$permalinks = $settingsService->getPermalinkStructure();

// Check if user is authenticated via web guard (session)
$isWebAuth = Auth::guard('web')->check();

// Fetch Languages
$languages = \App\Models\Language::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
$currentLangCode = \App\Helpers\LanguageHelper::getCurrentLanguage();

// Fetch Currencies
$ecommerceSettings = $settingsService->getGroupSettings('ecommerce');
$currencies = [];
try {
    $currenciesValue = $ecommerceSettings['currencies']['value'] ?? '[]';
    $currencies = is_string($currenciesValue) ? json_decode($currenciesValue, true) : $currenciesValue;
} catch (\Exception $e) {}
$currentCurrencyCode = $ecommerceSettings['ecommerce_currency']['value'] ?? 'USD';
$currentCurrencySymbol = $ecommerceSettings['ecommerce_currency_symbol']['value'] ?? '$';
@endphp

<div id="polycms-topbar" class="polycms-topbar" style="{{ $isWebAuth ? 'display: block;' : 'display: none;' }}">
    <div class="polycms-topbar-container">
        {{-- Left Menu --}}
        <div class="polycms-topbar-left">
            @foreach($menuItems['left'] ?? [] as $item)
                @include('components.topbar-menu-item', ['item' => $item])
            @endforeach
        </div>

        {{-- Right Menu --}}
        <div class="polycms-topbar-right">
            @foreach($menuItems['right'] ?? [] as $item)
                @include('components.topbar-menu-item', ['item' => $item])
            @endforeach

            <!-- Language Switcher -->
            @if(count($languages) >= 1)
                <div class="topbar-switcher-dropdown" id="topbar-language-dropdown">
                    <button class="topbar-switcher-btn" onclick="toggleTopbarDropdown('topbar-language-content')" title="Switch Language">
                        <span class="topbar-switcher-icon">
                            {!! \App\Support\IconRegistry::render('globe') !!}
                        </span>
                        <span class="topbar-switcher-label">{{ strtoupper($currentLangCode) }}</span>
                    </button>
                    @if(count($languages) > 1)
                    <div class="topbar-switcher-panel" id="topbar-language-content">
                        <div class="topbar-switcher-body">
                            @foreach($languages as $lang)
                                <button class="topbar-switcher-item {{ $currentLangCode === $lang->code ? 'is-active' : '' }}" onclick="switchTopbarLanguage('{{ $lang->code }}')">
                                    <span class="topbar-switcher-item-content">
                                        @if($lang->flag)<span class="topbar-switcher-flag">{{ $lang->flag }}</span>@endif
                                        <span>{{ $lang->name }}</span>
                                    </span>
                                    @if($currentLangCode === $lang->code)
                                    {!! \App\Support\IconRegistry::render('check', 'topbar-switcher-check') !!}
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Currency Switcher -->
            @php
                // Ensure there is always a fallback array with 1 currency (the default) if empty
                if (empty($currencies)) {
                    $currencies = [
                        ['code' => $currentCurrencyCode, 'symbol' => $currentCurrencySymbol, 'decimals' => 2, 'symbol_position' => 'before', 'thousands_separator' => ',', 'decimal_separator' => '.', 'space_between' => false]
                    ];
                }
            @endphp
            @if(count($currencies) >= 1)
                <div class="topbar-switcher-dropdown" id="topbar-currency-dropdown">
                    <button class="topbar-switcher-btn" @if(count($currencies) > 1) onclick="toggleTopbarDropdown('topbar-currency-content')" @endif title="Switch Currency">
                        <span class="topbar-switcher-icon">
                            {!! \App\Support\IconRegistry::render('banknotes') !!}
                        </span>
                        @if(count($currencies) > 1)
                        <span class="topbar-switcher-chevron">
                            {!! \App\Support\IconRegistry::render('chevron-down') !!}
                        </span>
                        @endif
                    </button>
                    <div class="topbar-switcher-panel" id="topbar-currency-content">
                        <div class="topbar-switcher-body">
                            @foreach($currencies as $curr)
                                <button class="topbar-switcher-item {{ $currentCurrencyCode === $curr['code'] ? 'is-active' : '' }}" onclick="switchTopbarCurrency(@js($curr))">
                                    <span class="topbar-switcher-item-content">
                                        <span>{{ $curr['code'] }} - {{ $curr['symbol'] }}</span>
                                    </span>
                                    @if($currentCurrencyCode === $curr['code'])
                                    {!! \App\Support\IconRegistry::render('check', 'topbar-switcher-check') !!}
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Theme Toggle Button -->
            @if($settingsService->get('admin_topbar_dark_mode', true))
            <button id="topbar-theme-toggle" class="topbar-button theme-toggle-btn" title="Toggle theme" onclick="handleThemeToggle()" style="padding: 0; width: auto;">
                <span id="theme-toggle-light-icon" class="topbar-icon" style="display: none;">
                    {!! \App\Support\IconRegistry::render('sun') !!}
                </span>
                <span id="theme-toggle-dark-icon" class="topbar-icon" style="display: none;">
                    {!! \App\Support\IconRegistry::render('moon') !!}
                </span>
            </button>
            @endif
        </div>
    </div>
</div>

<style>
    #polycms-topbar.polycms-topbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        background: #1f2937;
        color: #fff;
        font-size: 13px;
        line-height: 32px;
        height: 32px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        -webkit-font-smoothing: antialiased;
        overflow: visible;
    }

    .polycms-topbar-container {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 16px;
        height: 32px;
        box-sizing: border-box;
    }

    .polycms-topbar-left,
    .polycms-topbar-right {
        display: flex;
        align-items: center;
        gap: 4px;
        height: 32px;
    }

    /* Unified styling for all topbar items */
    .polycms-topbar a,
    .polycms-topbar button,
    .polycms-topbar .topbar-button {
        display: inline-flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        gap: 6px !important;
        color: #d1d5db !important;
        text-decoration: none !important;
        border-radius: 3px !important;
        transition: all 0.15s ease !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        font-size: 13px !important;
        font-weight: 400 !important;
        font-family: inherit !important;
        white-space: nowrap !important;
        box-sizing: border-box !important;
        line-height: 28px !important;
        height: 28px !important;
        margin-top: 0;
        margin-bottom: 0;
        vertical-align: middle;
    }

    .polycms-topbar a:hover,
    .polycms-topbar button:hover {
        color: #60a5fa !important;
        background: rgba(255, 255, 255, 0.1) !important;
    }

    .polycms-topbar .topbar-highlight {
        background: #3b82f6 !important;
        color: #fff !important;
        font-weight: 500 !important;
    }

    .polycms-topbar .topbar-highlight:hover {
        background: #2563eb !important;
        color: #fff !important;
    }

    /* Icon unified styling */
    .polycms-topbar .topbar-icon {
        display: inline-flex;
        align-items: center !important;
        justify-content: center !important;
        width: 16px !important;
        height: 16px !important;
        line-height: 1 !important;
    }

    .polycms-topbar .topbar-icon svg {
        width: 16px !important;
        height: 16px !important;
        display: block !important;
    }

    .theme-toggle-btn {
        padding: 0 8px !important;
        width: auto !important;
        margin-left: 4px !important;
        height: 24px !important;
    }

    .polycms-topbar .topbar-dropdown {
        position: relative;
        display: flex;
        align-items: center;
        height: 32px;
    }

    .polycms-topbar .topbar-dropdown:not(.submenu) > a::after {
        content: '';
        display: inline-block;
        width: 0;
        height: 0;
        margin-left: 6px;
        vertical-align: middle;
        border-top: 4px solid #9ca3af;
        border-right: 4px solid transparent;
        border-bottom: 0px;
        border-left: 4px solid transparent;
        transition: border-color 150ms ease;
    }

    .polycms-topbar .topbar-dropdown:not(.submenu):hover > a::after {
        border-top-color: #60a5fa;
    }

    .polycms-topbar .topbar-dropdown-content {
        display: none;
        opacity: 0;
        visibility: hidden;
        position: absolute;
        top: 100%;
        margin-top: 0;
        background: #1f2937 !important;
        min-width: 210px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        border-radius: 0 0 6px 6px;
        padding: 6px 0;
        z-index: 100000;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-top: none;
    }

    .polycms-topbar .topbar-dropdown.submenu > a::after {
        display: none !important;
    }

    .polycms-topbar .topbar-dropdown:hover > .topbar-dropdown-content {
        display: flex !important;
        flex-direction: column !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Flyout for submenus in Blade topbar */
    .polycms-topbar .topbar-dropdown.submenu {
        width: 100% !important;
        height: auto !important;
    }
    
    .polycms-topbar .topbar-dropdown.submenu > .topbar-dropdown-content {
        top: 0 !important;
        left: 100% !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 0 6px 6px 6px !important;
        margin-top: -6px !important;
        margin-left: 0 !important;
    }

    .polycms-topbar .topbar-dropdown-item.has-arrow {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 8px !important;
    }

    .polycms-topbar .submenu-arrow {
        width: 12px;
        height: 12px;
        margin-left: auto;
        opacity: 0.5;
        transition: transform 150ms ease;
    }

    .polycms-topbar-right .submenu-arrow {
        /* If momentum is left, the arrow might need to rotate, but for now fixed like Vue */
    }

    .polycms-topbar .topbar-dropdown-item.has-arrow:hover .submenu-arrow {
        color: #60a5fa;
        opacity: 1;
    }

    /* Dynamic Alignment Classes (Zig-Zag / Snake Logic) */
    .polycms-topbar .topbar-dropdown.align-right > .topbar-dropdown-content {
        left: auto !important;
        right: 100% !important;
        margin-right: 2px !important;
    }

    .polycms-topbar .topbar-dropdown.align-below > .topbar-dropdown-content {
        top: 100% !important;
        left: 0 !important;
        right: auto !important;
        margin-top: 12px !important;
        position: absolute !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 6px !important;
    }

    /* Hover Stability Bridges (Prevent menu closing when moving mouse) */
    .polycms-topbar .topbar-dropdown-content::before {
        content: '';
        position: absolute;
        z-index: -1;
        background: transparent;
    }

    /* Standard Flyout Bridge (Left side) */
    .polycms-topbar .topbar-dropdown.submenu:not(.align-right):not(.align-below) > .topbar-dropdown-content::before {
        top: 0;
        bottom: 0;
        left: -14px;
        width: 14px;
    }

    /* Flipped Flyout Bridge (Right side) */
    .polycms-topbar .topbar-dropdown.submenu.align-right:not(.align-below) > .topbar-dropdown-content::before {
        top: 0;
        bottom: 0;
        right: -14px;
        width: 14px;
        left: auto;
    }

    /* Drop-below Bridge (Top side) */
    .polycms-topbar .topbar-dropdown.align-below > .topbar-dropdown-content::before {
        left: 0;
        right: 0;
        top: -14px !important;
        bottom: auto !important;
        height: 14px !important;
        width: 100% !important;
    }

    /* Arrow rotation for flipped submenus */
    .polycms-topbar .topbar-dropdown.align-right > a .submenu-arrow {
        transform: rotate(180deg);
    }

    .polycms-topbar-right .topbar-dropdown-content {
        right: 0;
        left: auto;
    }

    .polycms-topbar-left .topbar-dropdown-content {
        left: 0;
        right: auto;
    }

    .polycms-topbar .topbar-dropdown-item {
        display: flex !important;
        align-items: center !important;
        padding: 8px 16px !important;
        color: #d1d5db !important;
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        border-radius: 0 !important;
        text-align: left !important;
    }

    .polycms-topbar .topbar-dropdown-item:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #60a5fa !important;
    }

    .polycms-topbar .topbar-dropdown-item.active {
        color: #60a5fa !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }

    body.polycms-topbar-active {
        padding-top: 32px !important;
    }
    body.polycms-topbar-active header {
        top: 32px !important;
    }

    /* ===== Switcher styles (Language / Currency) — matches Vue components ===== */
    .topbar-switcher-dropdown {
        position: relative;
        display: inline-flex;
        align-items: center;
        height: 32px;
    }

    .polycms-topbar .topbar-switcher-btn {
        padding: 0 8px !important;
        height: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        background: transparent !important;
        border: none !important;
        border-radius: 3px !important;
        color: #d1d5db !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        font-family: inherit !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
        line-height: 32px !important;
        margin-left: 2px;
    }

    .polycms-topbar .topbar-switcher-btn:hover,
    .polycms-topbar .topbar-switcher-btn.active {
        color: #60a5fa !important;
        background: rgba(255, 255, 255, 0.1) !important;
    }

    .topbar-switcher-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    .topbar-switcher-icon svg {
        width: 14px !important;
        height: 14px !important;
        display: block !important;
        fill: none !important;
    }

    .topbar-switcher-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .topbar-switcher-chevron {
        display: inline-flex;
        align-items: center;
        margin-left: 2px;
    }

    .topbar-switcher-chevron svg {
        width: 10px !important;
        height: 10px !important;
        fill: none !important;
        transition: transform 0.2s ease;
    }

    .topbar-switcher-panel {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 4px;
        background: #1f2937;
        min-width: 200px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 6px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        z-index: 100001;
        overflow: hidden;
    }

    .topbar-switcher-header {
        padding: 12px 16px;
        font-weight: 600;
        color: #9ca3af;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        text-transform: uppercase;
        letter-spacing: 0.025em;
        font-size: 11px;
    }

    .topbar-switcher-body {
        padding: 4px 0;
    }

    .polycms-topbar .topbar-switcher-panel .topbar-switcher-item {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        padding: 12px 16px !important;
        background: transparent !important;
        border: none !important;
        color: #d1d5db !important;
        font-size: 14px !important;
        font-family: inherit !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        text-align: left !important;
        line-height: 1.4 !important;
        height: auto !important;
        border-radius: 0 !important;
        margin: 0 !important;
        gap: 0 !important;
    }

    .polycms-topbar .topbar-switcher-panel .topbar-switcher-item:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #60a5fa !important;
    }

    .polycms-topbar .topbar-switcher-panel .topbar-switcher-item.is-active {
        color: #60a5fa !important;
        background: rgba(255, 255, 255, 0.05) !important;
    }

    .topbar-switcher-item-content {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .topbar-switcher-flag {
        font-size: 16px;
    }

    .topbar-switcher-check {
        width: 16px;
        height: 16px;
        color: #3b82f6;
        flex-shrink: 0;
        fill: none !important;
    }
</style>

<script>
    window.toggleTopbarDropdown = function(id) {
        const content = document.getElementById(id);
        const allDropdowns = document.querySelectorAll('.topbar-switcher-panel');
        
        allDropdowns.forEach(dropdown => {
            if (dropdown.id !== id) {
                dropdown.style.display = 'none';
            }
        });

        if (content) {
            content.style.display = content.style.display === 'block' ? 'none' : 'block';
            content.style.opacity = '1';
            content.style.visibility = 'visible';
        }
    };

    window.switchTopbarLanguage = function(code) {
        let url = new URL(window.location.href);
        url.searchParams.set('lang', code);
        window.location.href = url.toString();
    };

    window.switchTopbarCurrency = function(currency) {
        const authToken = localStorage.getItem('auth_token');
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '@json(csrf_token())'
        };
        if (authToken) headers['Authorization'] = `Bearer ${authToken}`;

        fetch('/api/v1/settings/group/ecommerce', {
            method: 'PUT',
            headers: headers,
            body: JSON.stringify({
                settings: {
                    ecommerce_currency: currency.code,
                    ecommerce_currency_symbol: currency.symbol,
                    currency_decimals: currency.decimals,
                    currency_symbol_position: currency.symbol_position,
                    currency_thousands_separator: currency.thousands_separator,
                    currency_decimal_separator: currency.decimal_separator,
                    currency_space: currency.space_between
                }
            })
        }).then(() => window.location.reload());
    };

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.topbar-switcher-dropdown')) {
            document.querySelectorAll('.topbar-switcher-panel').forEach(d => {
                d.style.display = 'none';
            });
        }
    });

    (function() {
        // Permalink structure from server
        const permalinkStructure = @json($permalinks);
        const iconAliases = @json(\App\Support\IconRegistry::getAllAliases());

        // Function to detect route from current path
        function detectRouteFromPath(pathname) {
            const path = pathname.replace(/^\/+|\/+$/g, ''); // Remove leading/trailing slashes
            const segments = path.split('/').filter(s => s);

            const routeInfo = { route: null, params: {} };

            if (segments.length === 0) {
                routeInfo.route = 'home';
                return routeInfo;
            }

            // Get permalink bases (trim and split)
            const postsSingleBase = (permalinkStructure?.posts?.single || 'posts').trim().split('/').filter(s => s);
            const pagesBase = (permalinkStructure?.pages?.single || '').trim().split('/').filter(s => s);
            const productsSingleBase = (permalinkStructure?.products?.single || 'products').trim().split('/').filter(s => s);
            const categoryBase = (permalinkStructure?.categories?.single || 'categories').trim().split('/').filter(s => s);
            const postTagBase = (permalinkStructure?.tags?.post || 'tags').trim().split('/').filter(s => s);
            const productTagBase = (permalinkStructure?.tags?.product || 'product-tags').trim().split('/').filter(s => s);

            // Helper function to check if segments match a base
            function matchesBase(segments, baseArray) {
                if (baseArray.length === 0) return false;
                if (segments.length < baseArray.length) return false;
                return baseArray.every((base, idx) => segments[idx] === base);
            }

            // Check routes in priority order (more specific first)
            // 1. Check for categories.show: /{categoryBase}/{slug}
            if (categoryBase.length > 0 && matchesBase(segments, categoryBase) && segments[categoryBase.length]) {
                routeInfo.route = 'categories.show';
                routeInfo.params = { slug: segments[categoryBase.length] };
                return routeInfo;
            }

            // 2. Check for tags.show (post tags): /{postTagBase}/{slug}
            if (postTagBase.length > 0 && matchesBase(segments, postTagBase) && segments[postTagBase.length]) {
                routeInfo.route = 'tags.show';
                routeInfo.params = { slug: segments[postTagBase.length] };
                return routeInfo;
            }

            // 3. Check for product-tags.show: /{productTagBase}/{slug}
            if (productTagBase.length > 0 && matchesBase(segments, productTagBase) && segments[productTagBase.length]) {
                routeInfo.route = 'product-tags.show';
                routeInfo.params = { slug: segments[productTagBase.length] };
                return routeInfo;
            }

            // 4. Check for posts.show: /{postsSingleBase}/{slug}
            if (postsSingleBase.length > 0 && matchesBase(segments, postsSingleBase) && segments[postsSingleBase.length]) {
                routeInfo.route = 'posts.show';
                routeInfo.params = { slug: segments[postsSingleBase.length] };
                return routeInfo;
            }

            // 5. Check for products.show: /{productsSingleBase}/{slug}
            if (productsSingleBase.length > 0 && matchesBase(segments, productsSingleBase) && segments[productsSingleBase.length]) {
                routeInfo.route = 'products.show';
                routeInfo.params = { slug: segments[productsSingleBase.length] };
                return routeInfo;
            }

            // 6. Check for pages.show: /{slug} (if no base) or /{pagesBase}/{slug}
            // Pages should be checked last as it's the fallback
            if (pagesBase.length === 0) {
                // No base, single segment could be a page (but check it's not reserved)
                if (segments.length === 1) {
                    routeInfo.route = 'pages.show';
                    routeInfo.params = { slug: segments[0] };
                    return routeInfo;
                }
            } else {
                // Has base, check if matches
                if (matchesBase(segments, pagesBase) && segments[pagesBase.length]) {
                    routeInfo.route = 'pages.show';
                    routeInfo.params = { slug: segments[pagesBase.length] };
                    return routeInfo;
                }
            }

            // Debug logging
            if (window.console && console.debug) {
                console.debug('Topbar: Route detection', {
                    pathname: pathname,
                    segments: segments,
                    permalinkStructure: permalinkStructure,
                    detectedRoute: routeInfo.route,
                    detectedSlug: routeInfo.params.slug
                });
            }

            return routeInfo;
        }

        // Server-side auth status
        const isWebAuth = @json($isWebAuth);

        // Check if user is authenticated and show topbar
        function checkAuthAndShowTopbar() {
            // Case 1: Session Auth (Customer / Web Guard)
            if (isWebAuth) {
                 const topbar = document.getElementById('polycms-topbar');
                 if (topbar) {
                     document.body.classList.add('polycms-topbar-active');
                     adjustStickyHeaders();
                 }
                 return;
            }

            // Case 2: API Token Auth (Admin / SPA)
            const authToken = localStorage.getItem('auth_token');
            if (!authToken) {
                return; // No token, user not logged in
            }

            // Check if user is authenticated by calling API
            fetch('/api/v1/auth/me', {
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('Not authenticated');
            })
            .then(data => {
                // User is authenticated, fetch menu items and show topbar
                if (data && data.data) {
                    // Detect current route and params from URL
                    const currentPath = window.location.pathname;
                    const routeInfo = detectRouteFromPath(currentPath);

                    // Debug logging
                    if (console && console.debug) {
                        console.debug('Topbar: Detected route info', {
                            pathname: currentPath,
                            route: routeInfo.route,
                            params: routeInfo.params
                        });
                    }

                    // Build API URL with route info
                    const apiUrl = new URL('/api/v1/topbar/menu', window.location.origin);
                    if (routeInfo.route) {
                        apiUrl.searchParams.set('route', routeInfo.route);
                        if (routeInfo.params && Object.keys(routeInfo.params).length > 0) {
                            apiUrl.searchParams.set('route_params', JSON.stringify(routeInfo.params));
                        }
                    }

                    // Fetch menu items from API
                    fetch(apiUrl.toString(), {
                        headers: {
                            'Authorization': `Bearer ${authToken}`,
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error('Failed to fetch menu');
                    })
                    .then(menuData => {
                        if (menuData && menuData.data && Array.isArray(menuData.data)) {
                            const leftMenu = document.querySelector('#polycms-topbar .polycms-topbar-left');
                            const rightMenu = document.querySelector('#polycms-topbar .polycms-topbar-right');

                            // Check if menu is empty (no items rendered server-side)
                            const hasItems = leftMenu && leftMenu.children.length > 0;

                            if (!hasItems && menuData.data.length > 0) {
                                // Render menu items dynamically
                                renderMenuItems(menuData.data, leftMenu, rightMenu);
                            }

                            // Show topbar
                            const topbar = document.getElementById('polycms-topbar');
                            if (topbar) {
                                topbar.style.display = 'block';
                                document.body.classList.add('polycms-topbar-active');

                                // Adjust sticky headers
                                adjustStickyHeaders();
                            }
                        }
                    })
                    .catch(error => {
                        console.debug('Topbar: Failed to fetch menu items', error);
                        // Still show topbar even if menu fetch fails
                        const topbar = document.getElementById('polycms-topbar');
                        if (topbar) {
                            topbar.style.display = 'block';
                            document.body.classList.add('polycms-topbar-active');
                        }
                    });
                }
            })
            .catch(error => {
                // User not authenticated, keep topbar hidden
                console.debug('Topbar: User not authenticated');
                // Ensure class is removed when user is not authenticated
                document.body.classList.remove('polycms-topbar-active');
            });
        }

        // Check after DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkAuthAndShowTopbar);
        } else {
            checkAuthAndShowTopbar();
        }

        function renderMenuItems(items, leftMenu, rightMenu) {
            if (!leftMenu || !rightMenu) return;

            // Clear existing items
            leftMenu.innerHTML = '';
            
            // PRESERVE Right Menu elements when clearing Right Menu
            const themeToggle = document.getElementById('topbar-theme-toggle');
            const langToggle = document.getElementById('topbar-language-dropdown');
            const currToggle = document.getElementById('topbar-currency-dropdown');

            rightMenu.innerHTML = '';

            // Group items by left/right
            const leftItems = [];
            const rightItems = [];

            items.forEach(item => {
                const group = item.group || 'left';
                if (group === 'right') {
                    rightItems.push(item);
                } else {
                    leftItems.push(item);
                }
            });

            // Sort by priority
            leftItems.sort((a, b) => (a.priority || 10) - (b.priority || 10));
            rightItems.sort((a, b) => (a.priority || 10) - (b.priority || 10));

            // Render left items
            leftItems.forEach(item => {
                const element = createMenuItemElement(item);
                if (element) leftMenu.appendChild(element);
            });

            // Render right items 
            rightItems.forEach(item => {
                const element = createMenuItemElement(item);
                if (element) {
                    rightMenu.appendChild(element);
                }
            });

            // Re-append preserved items at the end of right menu
            if (langToggle) rightMenu.appendChild(langToggle);
            if (currToggle) rightMenu.appendChild(currToggle);
            if (themeToggle) rightMenu.appendChild(themeToggle);
        }

        // Alignment check function for zig-zag (snake) logic
        window.checkTopbarMenuAlignment = function(dropdown) {
            const dropdownContent = dropdown.querySelector('.topbar-dropdown-content');
            if (!dropdownContent) return;

            // Reset alignment classes
            dropdown.classList.remove('align-right', 'align-below');
            
            // Wait for display: flex to apply via CSS :hover to measure
            requestAnimationFrame(() => {
                const rect = dropdownContent.getBoundingClientRect();
                const winWidth = window.innerWidth;
                const winHeight = window.innerHeight;
                
                const initialMomentum = dropdown.getAttribute('data-momentum') || 'right';
                let horizontal = '';
                let vertical = '';
                let currentMomentum = initialMomentum;

                // Collision detection
                if (initialMomentum === 'right' && rect.right > (winWidth - 20)) {
                    currentMomentum = 'left';
                    horizontal = 'align-right';
                } else if (initialMomentum === 'left' && rect.left < 20) {
                    currentMomentum = 'right';
                    horizontal = '';
                } else if (initialMomentum === 'left') {
                    horizontal = 'align-right';
                }

                // If momentum flipped, align below
                if (initialMomentum !== currentMomentum || rect.bottom > winHeight) {
                    vertical = 'align-below';
                }

                if (horizontal) dropdown.classList.add(horizontal);
                if (vertical) dropdown.classList.add(vertical);
                
                // Pass momentum to children
                dropdownContent.querySelectorAll('.topbar-dropdown').forEach(child => {
                    child.setAttribute('data-momentum', currentMomentum);
                    // Also trigger the alignment for children recursively if they are already visible
                    if (window.getComputedStyle(child).display !== 'none') {
                        window.checkTopbarMenuAlignment(child);
                    }
                });
            });
        };

        // Function to create menu item element
        function createMenuItemElement(item) {
            const hasChildren = item.children && item.children.length > 0;
            const isHighlight = item.highlight || false;
            const icon = item.icon || null;
            const label = item.label || '';
            const url = item.url || '#';
            const method = item.method || 'GET';

            if (hasChildren) {
                // Create dropdown
                const dropdown = document.createElement('div');
                dropdown.id = item.id || '';
                dropdown.className = 'topbar-dropdown' + (item.depth > 0 ? ' submenu' : '') + ' momentum-right';
                dropdown.setAttribute('data-momentum', 'right');
                dropdown.onmouseenter = function() { window.checkTopbarMenuAlignment(this); };

                const link = document.createElement('a');
                link.href = url;
                link.className = (isHighlight ? 'topbar-highlight ' : '') + 'has-arrow';

                if (icon) {
                    const iconSpan = document.createElement('span');
                    iconSpan.className = 'topbar-icon';
                    iconSpan.innerHTML = getIconSvg(icon);
                    link.appendChild(iconSpan);
                }

                const labelSpan = document.createElement('span');
                labelSpan.textContent = label;
                link.appendChild(labelSpan);

                // Add arrow for submenu parents
                if (item.depth > 0) {
                    link.classList.add('topbar-dropdown-item');
                    
                    const arrowSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    arrowSvg.setAttribute('class', 'submenu-arrow');
                    arrowSvg.setAttribute('fill', 'none');
                    arrowSvg.setAttribute('stroke', 'currentColor');
                    arrowSvg.setAttribute('viewBox', '0 0 24 24');
                    
                    const arrowPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    arrowPath.setAttribute('stroke-linecap', 'round');
                    arrowPath.setAttribute('stroke-linejoin', 'round');
                    arrowPath.setAttribute('stroke-width', '2');
                    arrowPath.setAttribute('d', 'M9 5l7 7-7 7');
                    
                    arrowSvg.appendChild(arrowPath);
                    link.appendChild(arrowSvg);
                }

                const dropdownContent = document.createElement('div');
                dropdownContent.className = 'topbar-dropdown-content';

                item.children.forEach(child => {
                    // Inject depth for recursion
                    child.depth = (item.depth || 0) + 1;
                    const childElement = createMenuItemElement(child);
                    if (childElement) dropdownContent.appendChild(childElement);
                });

                dropdown.appendChild(link);
                dropdown.appendChild(dropdownContent);

                return dropdown;
            } else {
                // Create simple link or form
                if (method === 'POST') {
                    const form = document.createElement('form');
                    form.className = item.depth > 0 ? 'topbar-dropdown-form' : 'topbar-form';
                    form.action = url;
                    form.method = 'POST';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = getCsrfToken();
                    form.appendChild(csrfInput);

                    const button = document.createElement('button');
                    button.type = 'submit';
                    button.id = item.id || '';
                    button.className = (item.depth > 0 ? 'topbar-dropdown-item' : 'topbar-button') + (isHighlight ? ' topbar-highlight' : '');
                    
                    if (item.id === 'logout') {
                        button.onclick = handleTopbarLogout;
                    }

                    if (icon) {
                        const iconSpan = document.createElement('span');
                        iconSpan.className = 'topbar-icon';
                        iconSpan.innerHTML = getIconSvg(icon);
                        button.appendChild(iconSpan);
                    }

                    const labelSpan = document.createElement('span');
                    labelSpan.textContent = label;
                    button.appendChild(labelSpan);

                    form.appendChild(button);
                    return form;
                } else {
                    const link = document.createElement('a');
                    link.id = item.id || '';
                    link.href = url;
                    link.className = (item.depth > 0 ? 'topbar-dropdown-item' : '') + (isHighlight ? ' topbar-highlight' : '');

                    if (icon) {
                        const iconSpan = document.createElement('span');
                        iconSpan.className = 'topbar-icon';
                        iconSpan.innerHTML = getIconSvg(icon);
                        link.appendChild(iconSpan);
                    }

                    const labelSpan = document.createElement('span');
                    labelSpan.textContent = label;
                    link.appendChild(labelSpan);

                    return link;
                }
            }
        }

        // Function to get CSRF token from meta tag or cookie
        function getCsrfToken() {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) return metaTag.getAttribute('content');

            // Try to get from cookie
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'XSRF-TOKEN') {
                    return decodeURIComponent(value);
                }
            }

            return '';
        }

        // Function to get icon SVG using sprite <use> reference
        function getIconSvg(iconName) {
            if (!iconName) return '';

            // Handle raw SVG path if name looks like one (M...)
            if (iconName.startsWith('M') && iconName.includes(' ')) {
                return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="${iconName}" /></svg>`;
            }

            // Resolve alias if exists
            let resolved = iconAliases[iconName] || iconName;

            // Ensure icon name ends with 'Icon' suffix and starts with Uppercase (for sprite)
            if (!resolved.endsWith('Icon')) {
                resolved = resolved.charAt(0).toUpperCase() + resolved.slice(1) + 'Icon';
            } else {
                // Already ends with Icon, but ensure first letter is capitalized for safety
                resolved = resolved.charAt(0).toUpperCase() + resolved.slice(1);
            }

            return `<svg width="16" height="16"><use href="/icons/heroicons.svg#${resolved}"/></svg>`;
        }

        // Function to handle logout from topbar (Frontend/Web session only)
        window.handleTopbarLogout = function(event) {
            event.preventDefault();
            
            // 1. Clear API token from localStorage (Admin SPA session)
            try {
                localStorage.removeItem('auth_token');
            } catch (e) {
                // Ignore storage errors
            }

            const form = event.target.closest('form');
            if (form) {
                // 2. Refresh the CSRF token in the form from the meta tag
                const freshToken = getCsrfToken();
                const tokenInput = form.querySelector('input[name="_token"]');
                if (tokenInput && freshToken) {
                    tokenInput.value = freshToken;
                }

                // 3. Submit the form natively (avoids fetch/AJAX 419 issues)
                form.submit();
                return;
            }

            // Fallback: if no form found, redirect to logout URL directly
            window.location.href = '/logout';
        };

        // Theme toggle logic
        window.handleThemeToggle = function() {
            const currentMode = localStorage.getItem('theme_mode') || 'system';
            let newMode = 'light';
            
            if (currentMode === 'light') {
                newMode = 'dark';
            } else if (currentMode === 'dark') {
                newMode = 'light';
            } else {
                // System mode - check current preference and toggle
                const isSystemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                newMode = isSystemDark ? 'light' : 'dark';
            }
            
            localStorage.setItem('theme_mode', newMode);
            localStorage.setItem('color-theme', newMode);
            updateThemeUI();
        };

        function updateThemeUI() {
            const mode = localStorage.getItem('theme_mode') || 'system';
            let isDark = false;

            if (mode === 'system') {
                isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            } else {
                isDark = mode === 'dark';
            }

            const lightIcon = document.getElementById('theme-toggle-light-icon');
            const darkIcon = document.getElementById('theme-toggle-dark-icon');

            if (isDark) {
                document.documentElement.classList.add('dark');
                if (lightIcon) lightIcon.style.display = 'inline-flex';
                if (darkIcon) darkIcon.style.display = 'none';
            } else {
                document.documentElement.classList.remove('dark');
                if (lightIcon) lightIcon.style.display = 'none';
                if (darkIcon) darkIcon.style.display = 'inline-flex';
            }
        }

        // Initialize theme UI
        updateThemeUI();

        // Global helper: fixed top offset (wpadminbar + PolyCMS topbar + main header)
        window.PolyCMS = window.PolyCMS || {};

        function getFixedElementHeight(element) {
            if (!element) return 0;
            const style = window.getComputedStyle(element);
            if (style.display === 'none' || style.visibility === 'hidden') return 0;
            if (style.position !== 'fixed' && style.position !== 'sticky') return 0;
            return element.getBoundingClientRect().height || element.offsetHeight || 0;
        }

        function computeFixedTopOffset(extraOffset = 0) {
            const wpAdminBar = document.getElementById('wpadminbar');
            const polycmsTopbar = document.getElementById('polycms-topbar');
            const mainHeader = document.getElementById('main-header');
            const extra = Number(extraOffset) || 0;

            return Math.max(
                0,
                Math.round(
                    getFixedElementHeight(wpAdminBar) +
                    getFixedElementHeight(polycmsTopbar) +
                    getFixedElementHeight(mainHeader) +
                    extra
                )
            );
        }

        window.PolyCMS.getFixedTopOffset = function(extraOffset = 0) {
            return computeFixedTopOffset(extraOffset);
        };

        window.PolyCMS.refreshFixedTopOffset = function(extraOffset = 0) {
            const offset = computeFixedTopOffset(extraOffset);
            document.documentElement.style.setProperty('--polycms-fixed-top-offset', `${offset}px`);
            return offset;
        };

        let fixedTopOffsetSyncing = false;
        function syncFixedTopOffset() {
            if (fixedTopOffsetSyncing) return;
            fixedTopOffsetSyncing = true;
            window.requestAnimationFrame(() => {
                window.PolyCMS.refreshFixedTopOffset();
                fixedTopOffsetSyncing = false;
            });
        }

        window.addEventListener('resize', syncFixedTopOffset, { passive: true });
        window.addEventListener('scroll', syncFixedTopOffset, { passive: true });
        window.PolyCMS.refreshFixedTopOffset();

        // Function to adjust sticky headers when topbar is active
        function adjustStickyHeaders() {
            const topbarHeight = 32; // Topbar height in pixels
            const headers = document.querySelectorAll('.header, .main-header, header[id="main-header"], header.main-header');

            headers.forEach(header => {
                const computedStyle = window.getComputedStyle(header);
                if (computedStyle.position === 'sticky' || computedStyle.position === 'fixed') {
                    // Check if top is currently 0
                    const currentTop = parseInt(computedStyle.top) || 0;
                    if (currentTop === 0) {
                        header.style.top = topbarHeight + 'px';
                    }
                }
            });
        }

        // Also adjust when topbar is already visible on page load
        // Only add class if topbar is actually visible (not display: none)
        const topbarOnLoad = document.getElementById('polycms-topbar');
        if (topbarOnLoad) {
            const isVisible = window.getComputedStyle(topbarOnLoad).display !== 'none';
            if (isVisible) {
                document.body.classList.add('polycms-topbar-active');
                setTimeout(adjustStickyHeaders, 100);
                setTimeout(syncFixedTopOffset, 120);
            } else {
                // Ensure class is removed if topbar is hidden on page load
                document.body.classList.remove('polycms-topbar-active');
                syncFixedTopOffset();
            }
        } else {
            // Ensure class is removed if topbar doesn't exist
            document.body.classList.remove('polycms-topbar-active');
            syncFixedTopOffset();
        }

        // Watch for topbar visibility changes
        const topbarObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const topbar = document.getElementById('polycms-topbar');
                    if (topbar) {
                        const isVisible = window.getComputedStyle(topbar).display !== 'none';
                        if (isVisible) {
                            document.body.classList.add('polycms-topbar-active');
                            adjustStickyHeaders();
                            syncFixedTopOffset();
                        } else {
                            document.body.classList.remove('polycms-topbar-active');
                            // Reset headers
                            const headers = document.querySelectorAll('.header, .main-header, header[id="main-header"], header.main-header');
                            headers.forEach(header => {
                                const computedStyle = window.getComputedStyle(header);
                                if (computedStyle.position === 'sticky' || computedStyle.position === 'fixed') {
                                    if (header.style.top === '32px') {
                                        header.style.top = '0px';
                                    }
                                }
                            });
                            syncFixedTopOffset();
                        }
                    }
                }
            });
        });

        const topbar = document.getElementById('polycms-topbar');
        if (topbar) {
            topbarObserver.observe(topbar, {
                attributes: true,
                attributeFilter: ['style']
            });
        }

        // Fix for Inertia SPA navigation losing topbar events
        // Re-ensure the topbar has correct interactivity state after navigation
        if (typeof document !== 'undefined') {
            document.addEventListener('inertia:navigate', function() {
                const tb = document.getElementById('polycms-topbar');
                if (tb) {
                    tb.style.pointerEvents = 'auto';
                    tb.style.zIndex = '100000';
                }
                
                // Clear any inline display styles from hover menus to restore CSS :hover
                document.querySelectorAll('.topbar-dropdown-content').forEach(d => {
                    d.style.display = '';
                });
                
                // Explicitly hide click-based panels upon navigation
                document.querySelectorAll('.topbar-switcher-panel').forEach(d => {
                    d.style.display = 'none';
                });

                syncFixedTopOffset();
            });
            
            document.addEventListener('inertia:success', function() {
                checkAuthAndShowTopbar();
                syncFixedTopOffset();
            });
        }

    })();
</script>
