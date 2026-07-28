<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Mail\MailProtocolRegistry;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailController extends Controller
{
    public function __construct(
        protected MailProtocolRegistry $registry,
        protected SettingsService $settingsService
    ) {}

    /**
     * Get available email protocols and their definitions.
     */
    public function getProtocols()
    {
        return response()->json([
            'data' => $this->registry->getProtocols()
        ]);
    }

    /**
     * Send a test email.
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'driver' => 'required|string',
            'settings' => 'required|array',
        ]);

        $targetEmail = $request->input('email');
        $driver = $request->input('driver');
        $settings = $request->input('settings');

        try {
            // Temporarily override mail config
            $this->applyRuntimeMailConfig($driver, $settings);

            $protocolName = $this->registry->getProtocols()[$driver]['label'] ?? ucfirst($driver);

            Mail::raw("This is a test email from PolyCMS to verify your email settings using the [{$protocolName}] engine.", function ($message) use ($targetEmail, $protocolName) {
                $message->to($targetEmail)
                    ->subject("PolyCMS Test Email: [{$protocolName}]");
            });

            return response()->json([
                'message' => 'Test email sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle(Request $request)
    {
        // 1. Get Client ID from settings
        $settings = $this->settingsService->getGroupSettings('email');
        $clientId = $settings['email_google_client_id']['value'] ?? null;
        
        if (!$clientId) {
            return response()->json(['message' => 'Client ID not configured. Please save settings first.'], 400);
        }

        // 2. Build URL
        $redirectUrl = route('api.v1.email.oauth.google.callback');
        $scopes = [
            'https://mail.google.com/',
            'email',
            'profile'
        ];
        
        // Generate secure random state and cache it
        // State Token (CSRF Protection): We generate a random string and store it in the Cache
        // because the callback route is public. This ensures that only requests initiated 
        // by a logged-in administrator can be processed when Google redirects back.
        $state = \Illuminate\Support\Str::random(40);
        \Illuminate\Support\Facades\Cache::put('oauth_google_state_' . $state, true, 300); // 5 minutes TTL

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline', // Important for refresh token
            'prompt' => 'consent', // Force consent to get refresh token
            'state' => $state,
        ];

        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

        return response()->json([
            'url' => $url
        ]);
    }

    /**
     * Handle Google Callback.
     */
    public function handleGoogleCallback(Request $request)
    {
        $code = $request->input('code');
        $error = $request->input('error');
        $state = $request->input('state');

        if ($error) {
            return redirect('/admin/settings/group/email?error=' . $error);
        }

        if (!$code) {
             return redirect('/admin/settings/group/email?error=no_code');
        }

        // Verify State to prevent CSRF and unauthorized access
        // We check if the state returned by Google matches the one we stored in the Cache.
        // This is crucial because the callback route is public and not protected by Sanctum middleware.
        if (!$state || !\Illuminate\Support\Facades\Cache::has('oauth_google_state_' . $state)) {
             return redirect('/admin/settings/group/email?error=invalid_state_or_expired');
        }
        
        // Clear used state
        \Illuminate\Support\Facades\Cache::forget('oauth_google_state_' . $state);

        // Exchange code
        $settings = $this->settingsService->getGroupSettings('email');
        $clientId = $settings['email_google_client_id']['value'] ?? null;
        $clientSecret = $settings['email_google_client_secret']['value'] ?? null;
        $redirectUrl = route('api.v1.email.oauth.google.callback');

        try {
            $response = \Illuminate\Support\Facades\Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUrl,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $refreshToken = $data['refresh_token'] ?? null;
                $email = null;

                // Optionally fetch email to save as well
                if (isset($data['access_token'])) {
                    $userRes = \Illuminate\Support\Facades\Http::withToken($data['access_token'])
                        ->get('https://www.googleapis.com/oauth2/v1/userinfo');
                    if ($userRes->successful()) {
                        $email = $userRes->json()['email'] ?? null;
                    }
                }

                // Save Refresh Token
                if ($refreshToken) {
                    $this->settingsService->set('email_google_refresh_token', $refreshToken, 'email');
                }
                if ($email) {
                    $this->settingsService->set('email_google_email', $email, 'email');
                }

                return redirect('/admin/settings/group/email?success=google_connected'); 
            } else {
                 return redirect('/admin/settings/group/email?error=token_exchange_failed');
            }
        } catch (\Exception $e) {
            return redirect('/admin/settings/group/email?error=' . $e->getMessage());
        }
    }

    /**
     * Apply runtime mail configuration.
     */
    protected function applyRuntimeMailConfig(string $driver, array $settings)
    {
        // 1. Set default mailer
        Config::set('mail.default', $driver);

        // 2. Set mailer configuration
        if ($driver === 'smtp') {
            Config::set("mail.mailers.{$driver}", [
                'transport' => 'smtp',
                'host' => $settings['email_smtp_host'] ?? '',
                'port' => $settings['email_smtp_port'] ?? 587,
                'encryption' => $settings['email_smtp_encryption'] ?? 'tls',
                'username' => $settings['email_smtp_username'] ?? '',
                'password' => $settings['email_smtp_password'] ?? '',
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]);
        } elseif ($driver === 'sendmail') {
            Config::set("mail.mailers.{$driver}", [
                'transport' => 'sendmail',
                'path' => $settings['email_sendmail_path'] ?? '/usr/sbin/sendmail -bs',
            ]);
        } elseif ($driver === 'google') {
            Config::set("mail.mailers.{$driver}", [
                'transport' => 'google', // Uses our custom driver
                'username' => $settings['email_google_email'] ?? '',
                'client_id' => $settings['email_google_client_id'] ?? '',
                'client_secret' => $settings['email_google_client_secret'] ?? '',
                'refresh_token' => $settings['email_google_refresh_token'] ?? '',
            ]);
        } elseif ($driver === 'log') {
             Config::set("mail.mailers.{$driver}", [
                'transport' => 'log',
                'channel' => env('MAIL_LOG_CHANNEL'),
            ]);
        }

        // 3. Set global "from" address
        Config::set('mail.from', [
            'address' => $settings['email_from_address'] ?? 'noreply@polycms.org',
            'name' => $settings['email_from_name'] ?? 'PolyCMS',
        ]);
    }
}
