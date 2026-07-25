<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CacheInvalidationCoordinator
{
    protected CacheGenerationStore $generationStore;

    public function __construct(CacheGenerationStore $generationStore)
    {
        $this->generationStore = $generationStore;
    }

    /**
     * Invalidate cache for a specific content entity after DB commit.
     */
    public function invalidateEntity(string $entityType, string|int $entityId, array $tags = []): void
    {
        DB::afterCommit(function () use ($entityType, $entityId, $tags) {
            // 1. Bump generation token for fencing protection
            $this->generationStore->bump($entityType, $entityId);

            // 2. Clear local cache key if specific
            $specificKey = 'polycms_page_cache_' . $entityType . '_' . $entityId;
            Cache::forget($specificKey);

            // 3. Dispatch Edge Purge async job (best-effort)
            // \App\Jobs\PurgeEdgeCacheJob::dispatch($entityType, $entityId, $tags);
        });
    }

    /**
     * Invalidate entire application page cache.
     */
    public function invalidateAll(): void
    {
        DB::afterCommit(function () {
            $this->generationStore->bump('site', 'global');
            Cache::flush();
        });
    }
}
