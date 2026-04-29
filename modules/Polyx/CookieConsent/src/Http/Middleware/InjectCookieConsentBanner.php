<?php

namespace Polyx\CookieConsent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SettingsService;

class InjectCookieConsentBanner
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip API requests, Admin routes, and non-HTML responses
        if (
            $request->is('api/*') || 
            $request->is('admin/*') || 
            !$response instanceof \Illuminate\Http\Response ||
            !str_contains($response->headers->get('Content-Type') ?? '', 'text/html')
        ) {
            return $response;
        }

        $settings = app(SettingsService::class);
        $isEnabled = $settings->get('cookie_consent_is_enabled', true);

        // Use $_COOKIE instead of $request->cookie() because JS creates raw (unencrypted) cookies,
        // Laravel EncryptCookies middleware silently discards them if using $request->cookie().
        if (!$isEnabled || isset($_COOKIE['polycms_consent'])) {
            return $response;
        }

        // Inject HTML Consent Banner
        $content = $response->getContent();
        
        $message = htmlspecialchars($settings->get('cookie_consent_message', 'Your experience on this site will be improved by allowing cookies.'));
        $btnAccept = htmlspecialchars($settings->get('cookie_consent_btn_accept', 'Accept cookies'));
        $btnReject = htmlspecialchars($settings->get('cookie_consent_btn_reject', 'Reject'));
        $btnCustomize = htmlspecialchars($settings->get('cookie_consent_btn_customize', 'Customize preferences'));
        $policyUrl = addslashes($settings->get('cookie_consent_policy_url', '/privacy-policy'));

        $bannerHtml = <<<HTML
<!-- PolyCMS Cookie Consent Banner -->
<div id="polycms-cookie-consent" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 99999; background-color: #111; color: #fff; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between; font-family: -apple-system, system-ui, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; flex-wrap: wrap; gap: 15px; box-shadow: 0 -2px 10px rgba(0,0,0,0.5);">
    <div style="display: flex; align-items: center; gap: 15px; flex: 1; min-width: 250px;">
        <div style="background-color: #ef4444; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg style="width: 20px; height: 20px; fill: #fff;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <p style="margin: 0; line-height: 1.4;">{$message}</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button id="cookie-reject" style="background: #fff; color: #333; border: 1px solid #ccc; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500; font-size: 13px; transition: all 0.2s;">{$btnReject}</button>
        <button id="cookie-customize" style="background: #fff; color: #333; border: 1px solid #ccc; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500; font-size: 13px; transition: all 0.2s;">{$btnCustomize}</button>
        <button id="cookie-accept" style="background: #3b82f6; color: #fff; border: 1px solid #2563eb; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500; font-size: 13px; transition: all 0.2s;">{$btnAccept}</button>
    </div>
</div>

<script>
(function() {
    function setCookie(value) {
        var d = new Date();
        d.setTime(d.getTime() + (365*24*60*60*1000));
        document.cookie = "polycms_consent=" + value + ";expires=" + d.toUTCString() + ";path=/";
        document.getElementById("polycms-cookie-consent").style.display = "none";
    }

    document.getElementById("cookie-accept").addEventListener("click", function() { setCookie("accepted"); });
    document.getElementById("cookie-reject").addEventListener("click", function() { setCookie("rejected"); });
    document.getElementById("cookie-customize").addEventListener("click", function() {
        window.location.href = "{$policyUrl}";
    });
})();
</script>
<!-- End PolyCMS Cookie Consent -->
HTML;

        // Insert just before the </body> tag
        $pos = strripos($content, '</body>');
        
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . $bannerHtml . substr($content, $pos);
            $response->setContent($content);
        }

        return $response;
    }
}
