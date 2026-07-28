<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\LanguageHelper;
use App\Services\SettingsService;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(SettingsService::class);
        $lang = session('locale');

        // If user is switching language via ?lang= param
        if ($request->has('lang')) {
            $lang = $request->query('lang');
            session(['locale' => $lang]);
        }

        $defaultLocale = cache()->remember('default_language_code', 3600, function () {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                    return 'en';
                }
                return \App\Models\Language::where('is_default', true)->value('code') ?? 'en';
            } catch (\Exception $e) {
                return 'en';
            }
        });

        if (!$lang) {
            $lang = $defaultLocale;
        }

        $firstSegment = $request->segment(1);
        $activeLocales = cache()->remember('active_languages_non_default', 3600, function () {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('languages')) {
                    return [];
                }
                return \App\Models\Language::where('is_active', true)
                    ->where('is_default', false)
                    ->pluck('code')
                    ->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });
        $isPrefixed = $firstSegment && in_array($firstSegment, $activeLocales, true);

        // If user is logged in, handle their language preference
        $user = auth()->guard('web')->user() ?? auth()->guard('sanctum')->user() ?? auth()->user();

        if ($user) {
            $userId = $user->id;
            
            if ($request->has('lang')) {
                // User is switching right now, save to their preference
                $settings->set("user_language_{$userId}", $lang, 'user_preferences', 'string', false);
            } else {
                // Otherwise, load from their preference (prefixed route takes priority)
                if (!$isPrefixed) {
                    $userLang = $settings->get("user_language_{$userId}");
                    if ($userLang) {
                        $lang = $userLang;
                        // Also sync back to session if session is available
                        if ($request->hasSession()) {
                            session(['locale' => $lang]);
                        }
                    }
                }
            }
        }

        // Apply language to app and LanguageHelper only for backend/API requests.
        // Frontend locale is fully managed by LanguageRoutingMiddleware.
        $isFrontend = ($firstSegment !== 'admin' && 
                       $firstSegment !== 'api' && 
                       $firstSegment !== 'install' && 
                       $firstSegment !== 'themes' && 
                       $firstSegment !== 'modules' && 
                       $firstSegment !== 'storage' && 
                       $firstSegment !== 'assets' && 
                       $firstSegment !== 'build' && 
                       $firstSegment !== 'up' &&
                       $firstSegment !== 'login' &&
                       $firstSegment !== 'register' &&
                       $firstSegment !== 'logout' &&
                       $firstSegment !== 'account' &&
                       $firstSegment !== 'robots.txt' &&
                       !str_ends_with((string)$firstSegment, '.xml') &&
                       !str_starts_with((string)$firstSegment, 'sitemap') &&
                       !$request->expectsJson());

        if (!$isFrontend && $lang) {
            App::setLocale($lang);
            LanguageHelper::setCurrentLanguage($lang);
            \Illuminate\Support\Facades\URL::defaults(['locale' => $lang]);
        }

        return $next($request);
    }
}
