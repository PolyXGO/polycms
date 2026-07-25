<?php

declare(strict_types=1);

namespace App\Services\Cache;

final readonly class PageCacheEntry
{
    /**
     * Whitelisted headers allowed to be cached and replayed.
     *
     * @var array<int, string>
     */
    public const SAFE_HEADERS = [
        'content-type',
        'content-language',
        'etag',
        'last-modified',
        'vary',
    ];

    public function __construct(
        public int $schemaVersion,
        public int $status,
        public string $body,
        public array $headers,
        public string $contentRevision,
        public int $generatedAt,
        public int $freshUntil,
        public int $staleUntil,
        public string $checksum
    ) {}

    public function isFresh(): bool
    {
        return time() <= $this->freshUntil;
    }

    public function isStaleAllowed(): bool
    {
        $now = time();
        return $now > $this->freshUntil && $now <= $this->staleUntil;
    }

    public function isExpired(): bool
    {
        return time() > $this->staleUntil;
    }

    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'status' => $this->status,
            'body' => $this->body,
            'headers' => $this->headers,
            'contentRevision' => $this->contentRevision,
            'generatedAt' => $this->generatedAt,
            'freshUntil' => $this->freshUntil,
            'staleUntil' => $this->staleUntil,
            'checksum' => $this->checksum,
        ];
    }

    public static function fromArray(array $data): ?self
    {
        if (
            !isset($data['schemaVersion'], $data['status'], $data['body'], $data['headers']) ||
            !isset($data['checksum'], $data['generatedAt'], $data['freshUntil'], $data['staleUntil'])
        ) {
            return null;
        }

        return new self(
            schemaVersion: (int) $data['schemaVersion'],
            status: (int) $data['status'],
            body: (string) $data['body'],
            headers: (array) $data['headers'],
            contentRevision: (string) ($data['contentRevision'] ?? 'v1'),
            generatedAt: (int) $data['generatedAt'],
            freshUntil: (int) $data['freshUntil'],
            staleUntil: (int) $data['staleUntil'],
            checksum: (string) $data['checksum']
        );
    }
}
