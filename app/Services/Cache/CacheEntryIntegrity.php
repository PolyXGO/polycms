<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Services\SettingsService;

class CacheEntryIntegrity
{
    public const CURRENT_SCHEMA_VERSION = 3;

    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Compute SHA-256 checksum of payload.
     */
    public function checksum(string $payload): string
    {
        return hash('sha256', $payload);
    }

    /**
     * Compute HMAC-SHA256 signature for multi-tenant / untrusted storage.
     */
    public function signature(string $payload, string $tenantId, string $secretKey): string
    {
        return hash_hmac('sha256', $tenantId . ':' . $payload, $secretKey);
    }

    /**
     * Validate an entry's integrity and schema version.
     */
    public function validate(PageCacheEntry $entry, string $tenantId = 'tenant_1'): bool
    {
        // 1. Schema version check
        if ($entry->schemaVersion !== self::CURRENT_SCHEMA_VERSION) {
            return false;
        }

        // 2. Body Checksum validation
        $calculatedChecksum = $this->checksum($entry->body);
        if (!hash_equals($entry->checksum, $calculatedChecksum)) {
            return false;
        }

        // 3. Optional HMAC Signature validation if enabled
        $integrityMode = $this->settingsService->get('cache_integrity_mode', 'sha256');
        if ($integrityMode === 'hmac') {
            $key = (string) $this->settingsService->get('cache_integrity_key', config('app.key'));
            $expectedSignature = $this->signature($entry->body, $tenantId, $key);
            if (!isset($entry->headers['x-cache-signature']) || !hash_equals($expectedSignature, $entry->headers['x-cache-signature'])) {
                return false;
            }
        }

        return true;
    }
}
