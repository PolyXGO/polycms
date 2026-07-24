<?php

namespace Polyx\CookieConsent;

use App\Facades\Hook;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class CookieConsentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register settings into the Settings Hub (must be in register() to run before SettingsService)
        Hook::addFilter('settings.defaults', function ($defaults) {
            $defaults['cookie_consent'] = [
                'cookie_consent_is_enabled' => [
                    'key' => 'cookie_consent_is_enabled',
                    'value' => true,
                    'type' => 'boolean',
                    'label' => 'Enable Cookie Consent (GDPR)',
                    'description' => 'Complies with EU and California privacy laws. Blocks tracking scripts if rejected.',
                ],
                'cookie_consent_message' => [
                    'key' => 'cookie_consent_message',
                    'value' => 'Your experience on this site will be improved by allowing cookies.',
                    'type' => 'textarea',
                    'label' => 'Consent Message',
                    'description' => 'Text displayed on the cookie banner.',
                ],
                'cookie_consent_policy_url' => [
                    'key' => 'cookie_consent_policy_url',
                    'value' => '/privacy-policy',
                    'type' => 'text',
                    'label' => 'Privacy Policy URL',
                    'description' => 'Where should the Customize/Learn More button redirect to?',
                ],
                'cookie_consent_btn_accept' => [
                    'key' => 'cookie_consent_btn_accept',
                    'value' => 'Accept cookies',
                    'type' => 'string',
                    'label' => 'Accept Button Text',
                ],
                'cookie_consent_btn_reject' => [
                    'key' => 'cookie_consent_btn_reject',
                    'value' => 'Reject',
                    'type' => 'string',
                    'label' => 'Reject Button Text',
                ],
                'cookie_consent_btn_customize' => [
                    'key' => 'cookie_consent_btn_customize',
                    'value' => 'Customize preferences',
                    'type' => 'string',
                    'label' => 'Customize/Policy Button Text',
                ],
            ];
            return $defaults;
        });
    }

    public function boot(Kernel $kernel): void
    {
        // Inject middleware into all web requests
        $kernel->pushMiddleware(\Polyx\CookieConsent\Http\Middleware\InjectCookieConsentBanner::class);

        // Register Blade directive so themes can conditionally block scripts
        \Illuminate\Support\Facades\Blade::if('consent', function() {
            return !isset($_COOKIE['polycms_consent']) || $_COOKIE['polycms_consent'] === 'accepted';
        });
    }
}
