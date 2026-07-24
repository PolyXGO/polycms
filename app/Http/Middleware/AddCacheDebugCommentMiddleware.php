<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCacheDebugCommentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only append to web requests that return HTML responses
        if ($response instanceof \Illuminate\Http\Response || $response instanceof \Symfony\Component\HttpFoundation\Response) {
            $contentType = $response->headers->get('Content-Type');
            if ($contentType && str_contains($contentType, 'text/html')) {
                $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                $cacheDriver = config('cache.default', 'unknown');
                $locale = app()->getLocale();
                
                $settingsService = app(\App\Services\SettingsService::class);
                $layout = $settingsService->get("widget_area_layout_footer_bottom", "1-col");
                $cachePrefix = config('cache.prefix', '');
                
                $comment = sprintf(
                    "\n<!-- Cache Driver: %s | Prefix: %s | Locale: %s | Layout: %s | Execution Time: %s ms -->\n<div style=\"display:none;\" id=\"polycms-cache-debug\" data-driver=\"%s\" data-time=\"%s\"></div>\n",
                    strtoupper((string) $cacheDriver),
                    (string) $cachePrefix,
                    (string) $locale,
                    (string) $layout,
                    (string) $executionTime,
                    strtoupper((string) $cacheDriver),
                    (string) $executionTime
                );

                $content = $response->getContent();
                if ($content && str_contains($content, '</body>')) {
                    // Inject before closing body tag
                    $content = str_replace('</body>', $comment . '</body>', $content);
                    $response->setContent($content);
                } else if ($content) {
                    // Or just append to the end
                    $response->setContent($content . $comment);
                }
            }
        }

        return $response;
    }
}
