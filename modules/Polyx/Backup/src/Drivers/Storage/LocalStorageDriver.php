<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Storage;

use Modules\Polyx\Backup\Contracts\StorageDriverInterface;
use Illuminate\Support\Facades\Log;

class LocalStorageDriver implements StorageDriverInterface
{
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? storage_path('app/backups');

        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    public function getDriverName(): string
    {
        return 'local';
    }

    public function upload(string $localPath, string $remotePath): array
    {
        try {
            $destination = $this->basePath . '/' . ltrim($remotePath, '/');
            $dir = dirname($destination);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if ($localPath === $destination) {
                return ['success' => true, 'remotePath' => $remotePath];
            }

            if (!copy($localPath, $destination)) {
                throw new \RuntimeException('Failed to copy file');
            }

            return ['success' => true, 'remotePath' => $remotePath];
        } catch (\Exception $e) {
            Log::error('Backup: Local upload failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function download(string $remotePath, string $localPath): array
    {
        try {
            $source = $this->basePath . '/' . ltrim($remotePath, '/');

            if (!file_exists($source)) {
                return ['success' => false, 'error' => 'File not found: ' . $remotePath];
            }

            $dir = dirname($localPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            if ($source === $localPath) {
                return ['success' => true, 'localPath' => $localPath];
            }

            if (!copy($source, $localPath)) {
                throw new \RuntimeException('Failed to copy file');
            }

            return ['success' => true, 'localPath' => $localPath];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listBackups(string $remotePath = ''): array
    {
        try {
            $dir = $this->basePath . ($remotePath ? '/' . ltrim($remotePath, '/') : '');

            if (!is_dir($dir)) {
                return ['success' => true, 'files' => []];
            }

            $files = [];
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $path = $dir . '/' . $file;
                if (is_file($path) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'zip') {
                    $files[] = [
                        'name' => $file,
                        'path' => ($remotePath ? $remotePath . '/' : '') . $file,
                        'size' => filesize($path),
                        'modified_at' => date('Y-m-d H:i:s', filemtime($path)),
                    ];
                }
            }

            // Sort by modified date, newest first
            usort($files, fn ($a, $b) => strcmp($b['modified_at'], $a['modified_at']));

            return ['success' => true, 'files' => $files];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function delete(string $remotePath): array
    {
        try {
            $file = $this->basePath . '/' . ltrim($remotePath, '/');

            if (!file_exists($file)) {
                return ['success' => true]; // Already gone
            }

            if (!@unlink($file)) {
                throw new \RuntimeException('Failed to delete file');
            }

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listFolders(?string $parentId = null): array
    {
        // Local storage doesn't need folder browsing
        return ['success' => true, 'folders' => []];
    }

    public function getAuthUrl(): string
    {
        return ''; // No auth needed for local
    }

    public function processCallback(string $code): bool
    {
        return true; // No-op for local
    }

    /**
     * Get the full path to a backup file.
     */
    public function getFullPath(string $filename): string
    {
        return $this->basePath . '/' . ltrim($filename, '/');
    }

    /**
     * Check if a backup file exists.
     */
    public function exists(string $filename): bool
    {
        return file_exists($this->getFullPath($filename));
    }
}
