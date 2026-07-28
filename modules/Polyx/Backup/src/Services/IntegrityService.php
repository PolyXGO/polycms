<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Services;

class IntegrityService
{
    private const ALGORITHM = 'sha256';

    /**
     * Generate SHA-256 checksum for a file.
     */
    public function hashFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('File not found: ' . $filePath);
        }

        $hash = hash_file(self::ALGORITHM, $filePath);

        if ($hash === false) {
            throw new \RuntimeException('Failed to compute checksum for: ' . $filePath);
        }

        return $hash;
    }

    /**
     * Verify a file's checksum matches the expected value.
     */
    public function verify(string $filePath, string $expectedChecksum): bool
    {
        return hash_equals($expectedChecksum, $this->hashFile($filePath));
    }

    /**
     * Get the algorithm name.
     */
    public function getAlgorithm(): string
    {
        return self::ALGORITHM;
    }
}
