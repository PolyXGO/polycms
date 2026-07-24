@php
    $message = $message ?? _l('Please log in to leave a comment or review.');
    $activeModules = class_exists(\App\Facades\Hook::class) 
        ? \App\Facades\Hook::applyFilters('modules.enabled', config('modules.enabled', []))
        : config('modules.enabled', []);

    $isExternalAuthActive = in_array('Polyx.ExternalAuth', $activeModules, true) || in_array('ExternalAuth', $activeModules, true);
    $isMTElementsActive = in_array('Polyx.MTElements', $activeModules, true) || in_array('MTElements', $activeModules, true);
    $isMarketIntegrationActive = in_array('Polyx.MarketIntegration', $activeModules, true) || in_array('MarketIntegration', $activeModules, true);

    $settingsService = app(\App\Services\SettingsService::class);

    // Check enabled providers in ExternalAuth
    $enabledProviders = [];
    if ($isExternalAuthActive) {
        if ($settingsService->get('external_auth_google_enabled', '0') == '1') {
            $enabledProviders[] = ['id' => 'google', 'name' => 'Google', 'url' => url('/external-auth/redirect/google')];
        }
        if ($settingsService->get('external_auth_facebook_enabled', '0') == '1') {
            $enabledProviders[] = ['id' => 'facebook', 'name' => 'Facebook', 'url' => url('/external-auth/redirect/facebook')];
        }
        if ($settingsService->get('external_auth_github_enabled', '0') == '1') {
            $enabledProviders[] = ['id' => 'github', 'name' => 'GitHub', 'url' => url('/external-auth/redirect/github')];
        }
    }

    $renderedMTElementsBlock = '';
    if ($isExternalAuthActive && $isMTElementsActive && !empty($enabledProviders)) {
        if (class_exists(\App\Facades\Hook::class)) {
            $renderedMTElementsBlock = (string) \App\Facades\Hook::applyFilters('content.render.landing_block.landing_external_auth', '', [
                'title' => _l('Sign in to your account'),
                'subtitle' => $message,
                'card_style' => 'card',
                'alignment' => 'center',
                'button_layout' => 'grid',
                'show_divider' => 'yes',
                'divider_text' => _l('Or continue with social account'),
            ]);
        }
    }
@endphp

<div class="external-auth-prompt-container my-4 w-full">
    @if(!empty(trim($renderedMTElementsBlock)))
        {{-- MTElements landing_external_auth block rendering --}}
        {!! $renderedMTElementsBlock !!}
    @elseif($isExternalAuthActive && !empty($enabledProviders))
        {{-- Standalone ExternalAuth social login fallback rendering --}}
        <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm text-center max-w-md mx-auto">
            <h4 class="text-base font-bold text-slate-900 dark:text-white mb-1">{{ _l('Sign in to continue') }}</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ $message }}</p>
            
            <div class="flex flex-wrap gap-2 justify-center mb-4">
                @foreach($enabledProviders as $provider)
                    <a href="{{ $provider['url'] }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        @if($provider['id'] === 'google')
                            <i class="fab fa-google text-red-500"></i>
                        @elseif($provider['id'] === 'facebook')
                            <i class="fab fa-facebook text-blue-600"></i>
                        @elseif($provider['id'] === 'github')
                            <i class="fab fa-github"></i>
                        @endif
                        <span>{{ $provider['name'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="text-xs text-slate-500 dark:text-slate-400">
                {!! sprintf(_l('Or %s using traditional account.'), '<a href="'.route('login').'" class="text-blue-600 font-semibold underline">'._l('Log in').'</a>') !!}
            </div>
        </div>
    @else
        {{-- Standard fallback --}}
        <div style="padding: 16px; border-radius: 8px; background: rgba(148, 163, 184, 0.05); border: 1px dashed var(--border-color, #cbd5e1); color: #64748b; font-size: 0.9rem; text-align: center;" class="dark:border-slate-800">
            <i class="fas fa-lock" style="margin-right: 6px;"></i>
            {!! sprintf(_l('Please %s to leave a comment or review.'), '<a href="'.route('login').'" style="color: var(--primary-color, #3b82f6); font-weight: 600; text-decoration: underline;">'._l('log in').'</a>') !!}
        </div>
    @endif

    {{-- MarketIntegration fallback/notice if active --}}
    @if($isMarketIntegrationActive && !empty($product->settings['envato_item_id']))
        <div class="text-center mt-3 text-xs text-slate-500">
            <i class="fas fa-shopping-bag text-emerald-500 mr-1"></i>
            {{ _l('Purchased on Envato Market? Log in to sync your purchase or verify your license.') }}
        </div>
    @endif
</div>
