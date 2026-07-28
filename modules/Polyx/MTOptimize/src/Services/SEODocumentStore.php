<?php

declare(strict_types=1);

namespace Modules\Polyx\MTOptimize\Services;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\Polyx\MTOptimize\Models\SEODocument;

class SEODocumentStore
{
    protected string $cachePrefix = 'mtoptimize:seo:doc:';

    public function __construct(
        protected SettingsService $settingsService,
    ) {}

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>|null
     */
    public function get(array $context): ?array
    {
        if (!Schema::hasTable('seo_documents')) {
            return null;
        }

        $fingerprint = $this->fingerprint($context);
        $cacheKey = $this->cacheKey($fingerprint);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $document = SEODocument::query()
            ->where('site_id', (string) ($context['siteId'] ?? 'default'))
            ->where('locale', (string) ($context['locale'] ?? app()->getLocale()))
            ->where('route_fingerprint', $fingerprint)
            ->first();

        if ($document === null) {
            return null;
        }

        if ($document->expires_at !== null && $document->expires_at->isPast()) {
            $document->delete();
            return null;
        }

        $payload = is_array($document->payload_json) ? $document->payload_json : null;
        if ($payload === null) {
            return null;
        }

        Cache::put($cacheKey, $payload, now()->addSeconds($this->cacheTtl()));

        return $payload;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $payload
     */
    public function put(array $context, array $payload): void
    {
        if (!Schema::hasTable('seo_documents')) {
            return;
        }

        $fingerprint = $this->fingerprint($context);
        $ttl = $this->cacheTtl();

        SEODocument::query()->updateOrCreate(
            [
                'site_id' => (string) ($context['siteId'] ?? 'default'),
                'locale' => (string) ($context['locale'] ?? app()->getLocale()),
                'route_fingerprint' => $fingerprint,
            ],
            [
                'object_type' => (string) ($context['entityType'] ?? ''),
                'object_id' => ($context['entityId'] ?? null) !== null ? (string) $context['entityId'] : null,
                'payload_json' => $payload,
                'checksum' => sha1(json_encode($payload)),
                'resolved_at' => now(),
                'expires_at' => now()->addSeconds($ttl),
            ]
        );

        Cache::put($this->cacheKey($fingerprint), $payload, now()->addSeconds($ttl));
    }

    public function invalidateByObject(string $objectType, ?string $objectId = null): void
    {
        if (!Schema::hasTable('seo_documents')) {
            return;
        }

        $query = SEODocument::query()->where('object_type', $objectType);

        if ($objectId !== null) {
            $query->where('object_id', $objectId);
        }

        $documents = $query->get(['route_fingerprint']);

        foreach ($documents as $document) {
            Cache::forget($this->cacheKey((string) $document->route_fingerprint));
        }

        $query->delete();
    }

    public function invalidateAll(): void
    {
        if (!Schema::hasTable('seo_documents')) {
            return;
        }

        SEODocument::query()->chunkById(200, function ($documents): void {
            foreach ($documents as $document) {
                Cache::forget($this->cacheKey((string) $document->route_fingerprint));
            }
        });

        SEODocument::query()->delete();
    }

    /**
     * @param array<string, mixed> $context
     */
    public function fingerprint(array $context): string
    {
        $data = [
            'site_id' => (string) ($context['siteId'] ?? 'default'),
            'locale' => (string) ($context['locale'] ?? app()->getLocale()),
            'route' => (string) ($context['routeName'] ?? ''),
            'path' => (string) ($context['requestPath'] ?? ''),
            'url' => (string) ($context['fullUrl'] ?? ''),
            'entity_type' => (string) ($context['entityType'] ?? ''),
            'entity_id' => (string) ($context['entityId'] ?? ''),
            'page' => (int) ($context['pagination']['page'] ?? 1),
        ];

        return hash('sha256', json_encode($data));
    }

    protected function cacheKey(string $fingerprint): string
    {
        return $this->cachePrefix . $fingerprint;
    }

    protected function cacheTtl(): int
    {
        return max(60, (int) $this->settingsService->get('mtoptimize_cache_ttl_seconds', 900));
    }
}
