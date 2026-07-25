<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DurableViewCounter
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Record a view count for a post or entity cleanly and durably.
     */
    public function recordView(string $entityType, int $entityId): void
    {
        try {
            $mode = $this->settingsService->get('analytics_counter_mode', 'mysql_bucket');

            if ($mode === 'mysql_direct') {
                $this->recordDirect($entityType, $entityId);
            } else {
                $this->recordBucket($entityType, $entityId);
            }
        } catch (\Throwable $e) {
            // Fail-closed/safe logging — Analytics must never crash request handling
            report($e);
        }
    }

    /**
     * Direct database increment.
     */
    protected function recordDirect(string $entityType, int $entityId): void
    {
        $table = $entityType === 'product' ? 'products' : 'posts';
        if (Schema::hasTable($table)) {
            DB::table($table)->where('id', $entityId)->increment('views_count');
        }
    }

    /**
     * Bucket table entry for load distribution.
     */
    protected function recordBucket(string $entityType, int $entityId): void
    {
        if (Schema::hasTable('post_view_buckets')) {
            DB::table('post_view_buckets')->insert([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'bucket_at' => now(),
                'count' => 1,
            ]);
        } else {
            // Fallback to direct update if bucket table is not yet migrated
            $this->recordDirect($entityType, $entityId);
        }
    }
}
