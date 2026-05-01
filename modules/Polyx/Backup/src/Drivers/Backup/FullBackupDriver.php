<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Backup;

use Modules\Polyx\Backup\Contracts\BackupDriverInterface;
use Modules\Polyx\Backup\Contracts\DatabaseDumperInterface;
use Modules\Polyx\Backup\Support\BackupManifest;
use Modules\Polyx\Backup\Support\ChunkedArchiver;
use Modules\Polyx\Backup\Services\IntegrityService;
use Illuminate\Support\Facades\Log;

class FullBackupDriver implements BackupDriverInterface
{
    public function __construct(
        private DatabaseDumperInterface $dumper,
        private IntegrityService $integrity,
    ) {}

    public function getType(): string
    {
        return 'full';
    }

    public function execute(string $destinationDir, array $options = []): array
    {
        try {
            $directories = $options['directories'] ?? $this->getDefaultDirectories();
            $excludedTables = $options['excludedTables'] ?? [];

            // Ensure destination exists
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            // 1. Dump database
            $dbDir = $destinationDir . '/database';
            mkdir($dbDir, 0755, true);
            $dbFile = $dbDir . '/dump.sql';

            $this->dumper->dump($dbFile, ['excludedTables' => $excludedTables]);
            $dbSize = filesize($dbFile);
            $dbChecksum = $this->integrity->hashFile($dbFile);

            Log::info('Backup: Database dump completed', ['size' => $dbSize]);

            // 2. Copy directories
            $filesInfo = [];
            foreach ($directories as $dirConfig) {
                $path = $dirConfig['path'] ?? '';
                $name = $dirConfig['name'] ?? basename($path);
                $sourcePath = base_path($path);

                if (is_file($sourcePath)) {
                    $targetPath = $destinationDir . '/' . $name;
                    copy($sourcePath, $targetPath);
                    $dirSize = filesize($sourcePath);
                } elseif (is_dir($sourcePath)) {
                    $targetPath = $destinationDir . '/' . $name;
                    ChunkedArchiver::copyDirectory($sourcePath, $targetPath);
                    $dirSize = ChunkedArchiver::directorySize($targetPath);
                } else {
                    Log::warning('Backup: Path not found or invalid, skipping', ['path' => $path]);
                    continue;
                }

                $filesInfo[] = [
                    'path' => $path,
                    'name' => $name,
                    'size' => $dirSize,
                ];

                Log::info('Backup: Directory copied', ['path' => $path, 'size' => $dirSize]);
            }

            // 3. Create manifest
            $manifest = BackupManifest::build([
                'type' => 'full',
                'database' => [
                    'driver' => $this->dumper->getDriverName(),
                    'name' => config('database.connections.' . config('database.default') . '.database'),
                    'tables_count' => count($this->dumper->getTables()),
                    'excluded_tables' => $excludedTables,
                    'protected_tables' => $this->dumper->getProtectedTables(),
                    'dump_file' => 'database/dump.sql',
                    'dump_size' => $dbSize,
                ],
                'files' => $filesInfo,
                'checksum' => [
                    'algorithm' => $this->integrity->getAlgorithm(),
                    'database' => $dbChecksum,
                ],
                'protected_tables' => $this->dumper->getProtectedTables(),
                'created_by' => auth()->user()?->email,
            ]);

            $manifest->saveTo($destinationDir . '/manifest.json');

            // 4. Create ZIP archive
            $archivePath = dirname($destinationDir) . '/' . basename($destinationDir) . '.zip';
            $zipResult = ChunkedArchiver::compress($destinationDir, $archivePath);

            if (!$zipResult['success']) {
                throw new \RuntimeException('ZIP compression failed: ' . ($zipResult['error'] ?? 'unknown'));
            }

            // 5. Calculate archive checksum
            $archiveChecksum = $this->integrity->hashFile($archivePath);
            $manifest->set('checksum', array_merge($manifest->get('checksum', []), [
                'archive' => $archiveChecksum,
            ]));

            // Update manifest inside archive is not needed since it's for metadata tracking
            // The manifest stored in BackupRecord.manifest column will have the final checksum

            // 6. Clean up temp directory
            ChunkedArchiver::removeDirectory($destinationDir);

            return [
                'success' => true,
                'archivePath' => $archivePath,
                'manifest' => $manifest->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Backup: FullBackupDriver failed', ['error' => $e->getMessage()]);

            // Clean up on failure
            if (is_dir($destinationDir)) {
                ChunkedArchiver::removeDirectory($destinationDir);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function getDefaultDirectories(): array
    {
        $dirs = [];
        $candidates = [
            ['path' => 'storage/app/public', 'name' => 'storage_public'],
            ['path' => 'themes', 'name' => 'themes'],
        ];

        foreach ($candidates as $dir) {
            if (is_dir(base_path($dir['path']))) {
                $dirs[] = $dir;
            }
        }

        return $dirs;
    }
}
