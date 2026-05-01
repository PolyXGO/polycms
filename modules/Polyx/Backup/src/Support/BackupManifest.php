<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Support;

class BackupManifest
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Build a manifest for a new backup.
     */
    public static function build(array $params): self
    {
        return new self([
            'version' => '1.0.0',
            'polycms_version' => config('app.version', '1.0.0'),
            'created_at' => now()->toIso8601String(),
            'type' => $params['type'] ?? 'full',
            'database' => $params['database'] ?? null,
            'files' => $params['files'] ?? [],
            'checksum' => $params['checksum'] ?? [],
            'encrypted' => $params['encrypted'] ?? false,
            'created_by' => $params['created_by'] ?? null,
            'php_version' => PHP_VERSION,
            'node' => gethostname() ?: 'unknown',
            'protected_tables' => $params['protected_tables'] ?? [],
        ]);
    }

    /**
     * Load manifest from a JSON file.
     */
    public static function fromFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \RuntimeException('Manifest file not found: ' . $path);
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid manifest JSON: ' . json_last_error_msg());
        }

        return new self($data);
    }

    /**
     * Save manifest to a JSON file.
     */
    public function saveTo(string $path): void
    {
        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($path, $json) === false) {
            throw new \RuntimeException('Failed to write manifest to: ' . $path);
        }
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
}
