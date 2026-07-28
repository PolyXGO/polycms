<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Support;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class ChunkedArchiver
{
    /**
     * Create a ZIP archive from a directory.
     * Uses stream-based approach — does NOT load entire files into memory.
     *
     * @param  string  $sourceDir  The directory to archive.
     * @param  string  $outputPath  The output ZIP file path.
     * @param  callable|null  $progressCallback  Reports (currentFile, totalFiles).
     * @return array{success: bool, size?: int, filesCount?: int, error?: string}
     */
    public static function compress(string $sourceDir, string $outputPath, ?callable $progressCallback = null): array
    {
        if (!is_dir($sourceDir)) {
            return ['success' => false, 'error' => 'Source directory not found: ' . $sourceDir];
        }

        $zip = new ZipArchive();
        $result = $zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            return ['success' => false, 'error' => 'Cannot create ZIP archive: error code ' . $result];
        }

        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $totalFiles = iterator_count(new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ));
            $currentFile = 0;

            foreach ($files as $file) {
                $currentFile++;
                $relativePath = substr($file->getPathname(), strlen($sourceDir) + 1);

                if ($file->isDir()) {
                    $zip->addEmptyDir($relativePath);
                } else {
                    $zip->addFile($file->getPathname(), $relativePath);
                }

                if ($progressCallback) {
                    $progressCallback($currentFile, $totalFiles);
                }
            }

            $zip->close();
            unset($files);

            return [
                'success' => true,
                'size' => filesize($outputPath),
                'filesCount' => $currentFile,
            ];
        } catch (\Exception $e) {
            $zip->close();
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
            Log::error('Backup: ZIP compression failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extract a ZIP archive to a directory.
     *
     * @param  string  $archivePath  Path to the ZIP file.
     * @param  string  $destinationDir  Directory to extract into.
     * @return array{success: bool, filesCount?: int, error?: string}
     */
    public static function extract(string $archivePath, string $destinationDir): array
    {
        if (!file_exists($archivePath)) {
            return ['success' => false, 'error' => 'Archive not found: ' . $archivePath];
        }

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $zip = new ZipArchive();
        $result = $zip->open($archivePath);

        if ($result !== true) {
            return ['success' => false, 'error' => 'Cannot open ZIP archive: error code ' . $result];
        }

        try {
            $filesCount = $zip->numFiles;
            $zip->extractTo($destinationDir);
            $zip->close();

            return [
                'success' => true,
                'filesCount' => $filesCount,
            ];
        } catch (\Exception $e) {
            $zip->close();
            Log::error('Backup: ZIP extraction failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate total size of a directory recursively.
     */
    public static function directorySize(string $dir): int
    {
        $size = 0;
        if (!is_dir($dir)) {
            return 0;
        }

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        ) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Copy a directory recursively.
     */
    public static function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathname();

            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    /**
     * Remove a directory and all its contents.
     */
    public static function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        \Illuminate\Support\Facades\File::deleteDirectory($dir);
    }
}
