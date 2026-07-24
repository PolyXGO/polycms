<?php

declare(strict_types=1);

namespace Modules\Polyx\ExternalAuth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ExternalAuthController extends Controller
{
    /**
     * Redirect to the provider's OAuth server
     */
    public function redirectToProvider(Request $request, string $provider): RedirectResponse
    {
        $settings = app(\App\Services\SettingsService::class);
        $enabled = (bool) $settings->get("external_auth_{$provider}_enabled", false);

        $isAdmin = (bool) $request->input('isAdmin');
        $fallbackRoute = $isAdmin ? '/admin/login' : route('account.login');

        if (!$enabled) {
            return redirect($fallbackRoute)->with('error', "{$provider} login is not enabled.");
        }

        $clientId = $settings->get("external_auth_{$provider}_client_id");
        $redirectUri = route('external-auth.callback', ['provider' => $provider]);
        $state = Str::random(40);
        session(['oauth_state' => $state]);
        
        if ($isAdmin) {
            session(['oauth_is_admin' => true]);
        } else {
            session()->forget('oauth_is_admin');
        }

        switch ($provider) {
            case 'google':
                $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                    'client_id'     => $clientId,
                    'redirect_uri'  => $redirectUri,
                    'response_type' => 'code',
                    'scope'         => 'openid email profile',
                    'state'         => $state,
                ]);
                break;

            case 'facebook':
                $url = 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query([
                    'client_id'    => $clientId,
                    'redirect_uri' => $redirectUri,
                    'state'        => $state,
                    'scope'        => 'email,public_profile',
                ]);
                break;

            case 'github':
                $url = 'https://github.com/login/oauth/authorize?' . http_build_query([
                    'client_id'    => $clientId,
                    'redirect_uri' => $redirectUri,
                    'scope'        => 'user:email',
                    'state'        => $state,
                ]);
                break;

            default:
                return redirect()->route('account.login')->with('error', 'Unsupported login provider.');
        }

        return redirect()->away($url);
    }

    /**
     * Handle provider OAuth callback
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        $isAdmin = (bool) session('oauth_is_admin', false);
        $fallbackRoute = $isAdmin ? '/admin/login' : route('account.login');

        $state = $request->input('state');
        $savedState = session('oauth_state');

        if (!$state || $state !== $savedState) {
            session()->forget('oauth_is_admin');
            return redirect($fallbackRoute)->with('error', 'Invalid security state token.');
        }

        $code = $request->input('code');
        if (!$code) {
            session()->forget('oauth_is_admin');
            return redirect($fallbackRoute)->with('error', 'Authorization code not provided.');
        }

        $settings = app(\App\Services\SettingsService::class);
        $clientId = $settings->get("external_auth_{$provider}_client_id");
        $clientSecret = $settings->get("external_auth_{$provider}_client_secret");
        $redirectUri = route('external-auth.callback', ['provider' => $provider]);

        try {
            $userProfile = [];

            if ($provider === 'google') {
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'code'          => $code,
                    'redirect_uri'  => $redirectUri,
                    'grant_type'    => 'authorization_code',
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Failed to exchange Google OAuth code: ' . $response->body());
                }

                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'] ?? null;

                $profileResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
                if (!$profileResponse->successful()) {
                    throw new \Exception('Failed to retrieve Google user profile.');
                }

                $profileData = $profileResponse->json();
                $userProfile = [
                    'id'    => $profileData['sub'] ?? '',
                    'name'  => $profileData['name'] ?? '',
                    'email' => $profileData['email'] ?? '',
                ];

            } elseif ($provider === 'facebook') {
                $response = Http::get('https://graph.facebook.com/v12.0/oauth/access_token', [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'code'          => $code,
                    'redirect_uri'  => $redirectUri,
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Failed to exchange Facebook OAuth code: ' . $response->body());
                }

                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'] ?? null;

                $profileResponse = Http::get('https://graph.facebook.com/me', [
                    'fields'       => 'id,name,email',
                    'access_token' => $accessToken,
                ]);

                if (!$profileResponse->successful()) {
                    throw new \Exception('Failed to retrieve Facebook user profile.');
                }

                $profileData = $profileResponse->json();
                $userProfile = [
                    'id'    => $profileData['id'] ?? '',
                    'name'  => $profileData['name'] ?? '',
                    'email' => $profileData['email'] ?? '',
                ];

            } elseif ($provider === 'github') {
                $response = Http::withHeaders(['Accept' => 'application/json'])->post('https://github.com/login/oauth/access_token', [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'code'          => $code,
                    'redirect_uri'  => $redirectUri,
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Failed to exchange GitHub OAuth code: ' . $response->body());
                }

                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'] ?? null;

                $profileResponse = Http::withHeaders([
                    'User-Agent' => 'PolyCMS-ExternalAuth',
                ])->withToken($accessToken)->get('https://api.github.com/user');

                if (!$profileResponse->successful()) {
                    throw new \Exception('Failed to retrieve GitHub user profile.');
                }

                $profileData = $profileResponse->json();
                $email = $profileData['email'] ?? null;

                // Self-healing email retrieval from GitHub (if private)
                if (empty($email)) {
                    $emailsResponse = Http::withHeaders([
                        'User-Agent' => 'PolyCMS-ExternalAuth',
                    ])->withToken($accessToken)->get('https://api.github.com/user/emails');

                    if ($emailsResponse->successful()) {
                        $emailsList = $emailsResponse->json();
                        foreach ($emailsList as $emailItem) {
                            if ($emailItem['primary'] ?? false) {
                                $email = $emailItem['email'];
                                break;
                            }
                        }
                        if (empty($email) && !empty($emailsList)) {
                            $email = $emailsList[0]['email'] ?? null;
                        }
                    }
                }

                $userProfile = [
                    'id'    => (string) ($profileData['id'] ?? ''),
                    'name'  => $profileData['name'] ?? ($profileData['login'] ?? ''),
                    'email' => $email ?? '',
                ];
            }

            if (empty($userProfile['email'])) {
                throw new \Exception("OAuth verified email address is missing for {$provider}.");
            }

            // Find or create User
            $user = User::where('email', $userProfile['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name'     => $userProfile['name'] ?: 'User',
                    'email'    => $userProfile['email'],
                    'password' => Hash::make(Str::random(24)), // Random password
                ]);
            }

            // Role assignment - only assign default customer role if they don't have any roles yet
            if (method_exists($user, 'assignRole')) {
                $hasRoles = false;
                if (method_exists($user, 'roles')) {
                    $hasRoles = $user->roles()->exists();
                }
                if (!$hasRoles) {
                    try {
                        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer']);
                        $user->assignRole('customer');
                    } catch (\Throwable $roleEx) {
                        Log::warning("Failed to assign role to oauth user: " . $roleEx->getMessage());
                    }
                }
            }

            // Log in user
            Auth::login($user, true);

            if ($isAdmin) {
                // Check if user has permission to access admin panel
                $canAccessAdmin = $user->can('access admin') || $user->hasAnyRole(['admin', 'editor', 'author']);
                $canAccessAdmin = \App\Facades\Hook::applyFilters('auth.can_access_admin', $canAccessAdmin, $user, $request);

                if (!$canAccessAdmin) {
                    Auth::logout();
                    session()->forget('oauth_is_admin');
                    return redirect('/admin/login')->with('error', 'You do not have permission to access the admin panel.');
                }

                $token = $user->createToken('admin-oauth')->plainTextToken;
                session()->forget('oauth_is_admin');
                return redirect('/admin/login?token=' . $token);
            }

            session()->forget('oauth_is_admin');
            // Redirect to frontend customer dashboard
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error("[ExternalAuth] Authentication Exception: " . $e->getMessage());
            $fallbackRoute = session('oauth_is_admin') ? '/admin/login' : route('account.login');
            session()->forget('oauth_is_admin');
            return redirect($fallbackRoute)->with('error', 'OAuth authentication failed. Please try again.');
        }
    }
}
