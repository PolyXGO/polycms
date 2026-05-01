<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Backup;

use Modules\Polyx\Backup\Contracts\BackupDriverInterface;
use Modules\Polyx\Backup\Contracts\DatabaseDumperInterface;
use Modules\Polyx\Backup\Support\BackupManifest;
use Modules\Polyx\Backup\Support\ChunkedArchiver;
use Modules\Polyx\Backup\Services\IntegrityService;
use Illuminate\Support\Facades\Log;

class DatabaseOnlyDriver implements BackupDriverInterface
{
    public function __construct(
        private DatabaseDumperInterface $dumper,
        private IntegrityService $integrity,
    ) {}

    public function getType(): string
    {
        return 'database';
    }

    public function execute(string $destinationDir, array $options = []): array
    {
        try {
            $excludedTables = $options['excludedTables'] ?? [];

            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            // Dump database only
            $dbDir = $destinationDir . '/database';
            mkdir($dbDir, 0755, true);
            $dbFile = $dbDir . '/dump.sql';

            $this->dumper->dump($dbFile, ['excludedTables' => $excludedTables]);
            $dbSize = filesize($dbFile);
            $dbChecksum = $this->integrity->hashFile($dbFile);

            // Create manifest
            $manifest = BackupManifest::build([
                'type' => 'database',
                'database' => [
                    'driver' => $this->dumper->getDriverName(),
                    'name' => config('database.connections.' . config('database.default') . '.database'),
                    'tables_count' => count($this->dumper->getTables()),
                    'excluded_tables' => $excludedTables,
                    'protected_tables' => $this->dumper->getProtectedTables(),
                    'dump_file' => 'database/dump.sql',
                    'dump_size' => $dbSize,
                ],
                'files' => [],
                'checksum' => [
                    'algorithm' => $this->integrity->getAlgorithm(),
                    'database' => $dbChecksum,
                ],
                'protected_tables' => $this->dumper->getProtectedTables(),
                'created_by' => auth()->user()?->email,
            ]);

            $manifest->saveTo($destinationDir . '/manifest.json');

            // Create ZIP
            $archivePath = dirname($destinationDir) . '/' . basename($destinationDir) . '.zip';
            $zipResult = ChunkedArchiver::compress($destinationDir, $archivePath);

            if (!$zipResult['success']) {
                throw new \RuntimeException('ZIP failed: ' . ($zipResult['error'] ?? 'unknown'));
            }

            $archiveChecksum = $this->integrity->hashFile($archivePath);
            $manifest->set('checksum', array_merge($manifest->get('checksum', []), [
                'archive' => $archiveChecksum,
            ]));

            ChunkedArchiver::removeDirectory($destinationDir);

            return [
                'success' => true,
                'archivePath' => $archivePath,
                'manifest' => $manifest->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Backup: DatabaseOnlyDriver failed', ['error' => $e->getMessage()]);
            if (is_dir($destinationDir)) {
                ChunkedArchiver::removeDirectory($destinationDir);
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
