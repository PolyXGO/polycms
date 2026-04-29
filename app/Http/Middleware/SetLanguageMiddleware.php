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

        // If user is logged in, handle their language preference
        $user = auth()->guard('web')->user() ?? auth()->guard('sanctum')->user() ?? auth()->user();

        if ($user) {
            $userId = $user->id;
            
            if ($request->has('lang')) {
                // User is switching right now, save to their preference
                $settings->set("user_language_{$userId}", $lang, 'user_preferences', 'string', false);
            } else {
                // Otherwise, load from their preference
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

        // Apply language to app and LanguageHelper
        if ($lang) {
            App::setLocale($lang);
            LanguageHelper::setCurrentLanguage($lang);
        }

        return $next($request);
    }
}
