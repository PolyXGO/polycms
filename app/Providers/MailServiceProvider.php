<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Mail\MailProtocolRegistry;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(MailProtocolRegistry::class, function ($app) {
            return new MailProtocolRegistry();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(MailProtocolRegistry $registry): void
    {
        // SMTP Protocol
        $registry->register('smtp', 'SMTP', [
            [
                'key' => 'email_smtp_encryption',
                'label' => 'Encryption',
                'type' => 'select',
                'default' => 'ssl',
                'options' => [
                    ['label' => 'SSL', 'value' => 'ssl'],
                    ['label' => 'TLS', 'value' => 'tls'],
                    ['label' => 'None', 'value' => null],
                ],
                'description' => 'Encryption protocol to use (Default: SSL).',
            ],
            [
                'key' => 'email_smtp_host',
                'label' => 'SMTP Host',
                'type' => 'text',
                'description' => 'Your SMTP server host address.',
            ],
            [
                'key' => 'email_smtp_port',
                'label' => 'SMTP Port',
                'type' => 'number',
                'description' => 'The port your SMTP server uses (usually 587 or 465).',
            ],
            [
                'key' => 'email_smtp_username',
                'label' => 'SMTP Username',
                'type' => 'text',
                'description' => 'Username for SMTP authentication.',
            ],
            [
                'key' => 'email_smtp_password',
                'label' => 'SMTP Password',
                'type' => 'password',
                'description' => 'Password for SMTP authentication. For Gmail/Outlook, you must use an <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-blue-500 underline">App Password</a> instead of your regular account password.',
            ],
        ]);

        // Sendmail Protocol
        $registry->register('sendmail', 'Sendmail', [
            [
                'key' => 'email_sendmail_path',
                'label' => 'Sendmail Path',
                'type' => 'text',
                'default' => '/usr/sbin/sendmail -bs',
                'description' => 'Path to the sendmail executable.',
            ],
        ]);

        // Google OAuth2 Protocol
        $registry->register('google', 'Google / Gmail (OAuth2)', [
            [
                'key' => 'email_google_b1_info',
                'label' => '', // No label for info block
                'type' => 'info',
                'content' => 'These details are obtained by setting up a project in your <a href="https://console.developers.google.com/" target="_blank" class="text-indigo-600 hover:text-indigo-900">Google API Console</a>.',
            ],
            [
                'key' => 'email_google_redirect_url_display',
                'label' => 'Redirect URL',
                'type' => 'readonly',
                // We will populate this value dynamically in the SettingsService or Controller if possible, 
                // but since this is a static registry, we can use a placeholder or injected value. 
                // Actually, let's use a default here that relies on valid env or config if needed, 
                // OR handle the value population in SettingsService::getDefaultEmailSettings
                'default' => url('/api/v1/email/oauth/google/callback'), 
                'description' => 'Add this to your Authorized redirect URIs in Google Console.',
            ],
            [
                'key' => 'email_google_email',
                'label' => 'Account Email',
                'type' => 'text',
                'description' => 'The Google/Gmail address you are sending from.',
            ],
            [
                'key' => 'email_google_client_id',
                'label' => 'Client ID',
                'type' => 'text',
                'description' => 'OAuth2 Client ID from Google Cloud Console.',
            ],
            [
                'key' => 'email_google_client_secret',
                'label' => 'Client Secret',
                'type' => 'password',
                'description' => 'OAuth2 Client Secret from Google Cloud Console.',
            ],
            [
                'key' => 'email_google_refresh_token',
                'label' => 'Refresh Token',
                'type' => 'password',
                'description' => 'A valid Refresh Token. Leave empty if you plan to authenticate via the button.',
            ],
            [
                'key' => 'email_google_auth_btn_info',
                'label' => 'Connect',
                'type' => 'oauth_connect',
                'url' => '/api/v1/email/oauth/google/redirect',
                'content' => 'To authenticate, first add Client Id and Client Secret and save settings. Then click the "Connect" button.',
            ],
        ]);

        // Log Protocol (for testing)
        $registry->register('log', 'Log (Development)', []);
        
        // Array Protocol (for testing)
        $registry->register('array', 'Array (Testing)', []);

        // Register Custom Google Driver
        $this->registerGoogleDriver();
    }

    protected function registerGoogleDriver()
    {
        $this->app['mail.manager']->extend('google', function ($config) {
            $clientId = $config['client_id'] ?? null;
            $clientSecret = $config['client_secret'] ?? null;
            $refreshToken = $config['refresh_token'] ?? null;
            $email = $config['username'] ?? null;

            if (!$clientId || !$clientSecret || !$refreshToken) {
                // Fallback or error? For now return basic SMTP if specific keys missing, 
                // but this will likely fail validation.
                // Or maybe the user provided an App Password in the "password" field?
                // Let's assume if they chose "google", they want OAuth logic if refresh token exists.
            }

            $accessToken = $this->getGoogleAccessToken($clientId, $clientSecret, $refreshToken);

            // Return SMTP Transport configured for Gmail OAuth2
            // We use the Symfony Mailer classes directly as Laravel 9+ wraps them.
            // But to be safe and version agnostic-ish (assuming Laravel 9/10):
            $factory = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory();
            $dsn = new \Symfony\Component\Mailer\Transport\Dsn(
                'smtp',
                'smtp.gmail.com',
                $email,
                $accessToken,
                587,
                ['encryption' => 'tls']
            );
            
            return $factory->create($dsn);
        });
    }

    protected function getGoogleAccessToken($clientId, $clientSecret, $refreshToken)
    {
        $cacheKey = 'google_oauth_token_' . md5($refreshToken);
        
        // Return cached token if available
        if ($token = \Illuminate\Support\Facades\Cache::get($cacheKey)) {
            return $token;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3500;

                // Cache it (subtract 1 min to be safe)
                \Illuminate\Support\Facades\Cache::put($cacheKey, $accessToken, $expiresIn - 60);

                return $accessToken;
            } else {
                 \Illuminate\Support\Facades\Log::error('Google OAuth Token Refresh Failed', $response->json());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth Token Exception: ' . $e->getMessage());
        }

    }
}
