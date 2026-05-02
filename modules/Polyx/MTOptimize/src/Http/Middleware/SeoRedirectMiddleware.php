<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Polyx\MTOptimize\Models\Seo404Log;
use Modules\Polyx\MTOptimize\Models\SeoRedirect;
use Symfony\Component\HttpFoundation\Response;

class SeoRedirectMiddleware
{
    /**
     * Handle incoming request — check for 301/302 redirects before routing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip admin, API, and asset requests
        $path = '/' . ltrim($request->path(), '/');
        if (
            str_starts_with($path, '/admin') ||
            str_starts_with($path, '/api') ||
            str_starts_with($path, '/_') ||
            preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|ico|woff2?|ttf|eot|map)$/i', $path)
        ) {
            return $next($request);
        }

        // Check redirect table (cached for 5 minutes)
        $redirect = $this->findRedirect($path);

        if ($redirect !== null) {
            // Increment hit counter asynchronously
            DB::table('seo_redirects')
                ->where('id', $redirect['id'])
                ->update([
                    'hits' => DB::raw('hits + 1'),
                    'last_hit_at' => now(),
                ]);

            return redirect($redirect['to_url'], $redirect['type']);
        }

        $response = $next($request);

        // Log 404 errors
        if ($response->getStatusCode() === 404 && !$request->expectsJson()) {
            $this->log404($path, $request);
        }

        return $response;
    }

    /**
     * @return array{id: int, to_url: string, type: int}|null
     */
    protected function findRedirect(string $path): ?array
    {
        $cacheKey = 'mtoptimize_redirect_' . md5($path);

        return Cache::remember($cacheKey, 300, function () use ($path): ?array {
            $redirect = SeoRedirect::query()
                ->where('from_path', $path)
                ->where('is_active', true)
                ->first();

            if ($redirect === null) {
                return null;
            }

            return [
                'id' => (int) $redirect->id,
                'to_url' => (string) $redirect->to_url,
                'type' => (int) $redirect->type,
            ];
        });
    }

    protected function log404(string $path, Request $request): void
    {
        try {
            Seo404Log::query()->upsert(
                [
                    'path' => mb_substr($path, 0, 2048),
                    'referrer' => mb_substr((string) $request->header('referer', ''), 0, 2048) ?: null,
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500) ?: null,
                    'ip' => $request->ip(),
                    'hits' => 1,
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                ],
                ['path'],
                ['hits' => DB::raw('hits + 1'), 'last_seen_at' => now(), 'referrer' => mb_substr((string) $request->header('referer', ''), 0, 2048) ?: null]
            );
        } catch (\Throwable $e) {
            Log::debug('[MTOptimize] 404 log failed', ['path' => $path, 'error' => $e->getMessage()]);
        }
    }
}
