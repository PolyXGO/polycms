<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Contracts;

interface BackupDriverInterface
{
    /**
     * Execute the backup operation.
     *
     * @param  string  $destinationDir  Temporary directory to put backup artifacts.
     * @param  array{
     *   directories?: array<int, array{path: string, name: string}>,
     *   excludedTables?: string[],
     * }  $options
     * @return array{
     *   success: bool,
     *   archivePath?: string,
     *   manifest?: array,
     *   error?: string,
     * }
     */
    public function execute(string $destinationDir, array $options = []): array;

    /**
     * Get the backup type identifier.
     */
    public function getType(): string;
}
