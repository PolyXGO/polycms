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
            str_contains($request->getPathInfo(), '/api/') ||
            $request->expectsJson() ||
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
<style>
    #polycms-cookie-consent {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        background-color: #111;
        color: #fff;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 18px;
        font-family: -apple-system, system-ui, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        font-size: 14px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.5);
    }
    #polycms-cookie-consent .polycms-cookie-inner {
        width: min(100%, 1120px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }
    #polycms-cookie-consent .polycms-cookie-message {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
        min-width: 240px;
    }
    #polycms-cookie-consent .polycms-cookie-icon {
        background-color: #ef4444;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    #polycms-cookie-consent .polycms-cookie-message p {
        margin: 0;
        line-height: 1.45;
        font-weight: 600;
    }
    #polycms-cookie-consent .polycms-cookie-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }
    #polycms-cookie-consent button {
        min-height: 36px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
        white-space: nowrap;
    }
    #cookie-reject,
    #cookie-customize {
        background: #fff;
        color: #333;
        border: 1px solid #ccc;
        padding: 8px 16px;
    }
    #cookie-accept {
        background: #3b82f6;
        color: #fff;
        border: 1px solid #2563eb;
        padding: 8px 16px;
    }
    @media (max-width: 640px) {
        #polycms-cookie-consent {
            padding: 14px 18px;
        }
        #polycms-cookie-consent .polycms-cookie-inner {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        #polycms-cookie-consent .polycms-cookie-message {
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
        }
        #polycms-cookie-consent .polycms-cookie-icon {
            width: 34px;
            height: 34px;
            margin-top: 1px;
        }
        #polycms-cookie-consent .polycms-cookie-message p {
            font-size: 12.5px;
            line-height: 1.45;
        }
        #polycms-cookie-consent .polycms-cookie-actions {
            display: grid;
            grid-template-columns: minmax(0, 0.72fr) minmax(0, 1.68fr);
            gap: 8px;
        }
        #polycms-cookie-consent button {
            width: 100%;
            min-height: 34px;
            padding: 7px 10px;
            font-size: 12px;
        }
        #cookie-accept {
            grid-column: 1 / -1;
        }
    }
</style>
<div id="polycms-cookie-consent">
    <div class="polycms-cookie-inner">
    <div class="polycms-cookie-message">
        <div class="polycms-cookie-icon">
            <svg style="width: 20px; height: 20px; fill: #fff;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <p>{$message}</p>
    </div>
    <div class="polycms-cookie-actions">
        <button id="cookie-reject">{$btnReject}</button>
        <button id="cookie-customize">{$btnCustomize}</button>
        <button id="cookie-accept">{$btnAccept}</button>
    </div>
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
