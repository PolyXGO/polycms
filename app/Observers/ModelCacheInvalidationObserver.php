<?php

declare(strict_types=1);

namespace App\Observers;

use App\Services\Cache\CacheGenerationStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ModelCacheInvalidationObserver
{
    public function __construct(
        protected CacheGenerationStore $generationStore
    ) {}

    /**
     * Handle the Model "saved" event (created or updated).
     */
    public function saved(Model $model): void
    {
        $this->invalidate($model, 'saved');
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->invalidate($model, 'deleted');
    }

    /**
     * Invalidate page cache for the specific model type and ID.
     */
    public function invalidate(Model $model, string $event = 'updated'): void
    {
        try {
            $class = class_basename($model);
            $type = strtolower($class);
            $id = $model->getKey();

            // Map model class name to entityType used by CacheGenerationStore & PageCacheMiddleware
            $entityType = match ($type) {
                'product', 'productcategory', 'productbrand', 'producttag' => 'product',
                'post', 'category', 'tag', 'posttag' => 'post',
                'page' => 'page',
                'project', 'projectcategory' => 'project',
                default => 'site',
            };

            // 1. Bump specific entity cache generation
            if ($id) {
                $this->generationStore->bump($entityType, $id);
            }

            // 2. Bump global entity type & site generation
            $this->generationStore->bump($entityType, 'global');
            $this->generationStore->bump('site', 'global');

            // 3. Clear settings / theme cache if settings/theme/language model
            if (in_array($type, ['setting', 'language', 'theme', 'menu', 'menuitem'], true)) {
                try {
                    app(\App\Services\SettingsService::class)->clearCache();
                } catch (\Throwable $e) {}
            }

            Log::info("[CacheInvalidation] Auto-invalidated page cache for model {$class} (ID: {$id}, Entity: {$entityType}) on event {$event}.");
        } catch (\Throwable $e) {
            Log::warning("[CacheInvalidation] Failed to invalidate cache for model {$class}: " . $e->getMessage());
        }
    }
}
