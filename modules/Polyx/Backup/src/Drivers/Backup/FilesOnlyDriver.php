<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Backup;

use Modules\Polyx\Backup\Contracts\BackupDriverInterface;
use Modules\Polyx\Backup\Support\BackupManifest;
use Modules\Polyx\Backup\Support\ChunkedArchiver;
use Modules\Polyx\Backup\Services\IntegrityService;
use Illuminate\Support\Facades\Log;

class FilesOnlyDriver implements BackupDriverInterface
{
    public function __construct(
        private IntegrityService $integrity,
    ) {}

    public function getType(): string
    {
        return 'files';
    }

    public function execute(string $destinationDir, array $options = []): array
    {
        try {
            $directories = $options['directories'] ?? $this->getDefaultDirectories();

            if (empty($directories)) {
                return ['success' => false, 'error' => 'No directories specified for files backup'];
            }

            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            // Copy directories
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
                    continue;
                }

                $filesInfo[] = [
                    'path' => $path,
                    'name' => $name,
                    'size' => $dirSize,
                ];
            }

            if (empty($filesInfo)) {
                return ['success' => false, 'error' => 'No valid directories found to backup'];
            }

            // Create manifest
            $manifest = BackupManifest::build([
                'type' => 'files',
                'files' => $filesInfo,
                'checksum' => ['algorithm' => $this->integrity->getAlgorithm()],
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
            Log::error('Backup: FilesOnlyDriver failed', ['error' => $e->getMessage()]);
            if (is_dir($destinationDir)) {
                ChunkedArchiver::removeDirectory($destinationDir);
            }
            return ['success' => false, 'error' => $e->getMessage()];
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
