<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Contracts;

interface DatabaseDumperInterface
{
    /**
     * Get list of tables that are ALWAYS excluded from dump.
     * These are self-referential or contain transient/sensitive data:
     * backup_records, backup_cloud_accounts, sessions, cache, jobs, failed_jobs.
     *
     * @return string[]
     */
    public function getProtectedTables(): array;

    /**
     * Dump database to SQL file.
     * ALWAYS excludes protected tables automatically.
     *
     * @param  string  $outputPath  Absolute path for the output SQL file.
     * @param  array{
     *   excludedTables?: string[],
     *   chunkSize?: int,
     * }  $options
     * @return bool True on success.
     * @throws \RuntimeException On dump failure.
     */
    public function dump(string $outputPath, array $options = []): bool;

    /**
     * Restore database from SQL file.
     * Transaction-wrapped with FK handling. After restore, caller must
     * reconcile protected tables.
     *
     * @param  string  $inputPath  Absolute path to the SQL dump file.
     * @param  array{
     *   transaction?: bool,
     * }  $options
     * @return bool True on success.
     * @throws \RuntimeException On restore failure.
     */
    public function restore(string $inputPath, array $options = []): bool;

    /**
     * Get the database driver name (pgsql, mysql).
     */
    public function getDriverName(): string;

    /**
     * Get all database table names.
     *
     * @return string[]
     */
    public function getTables(): array;

    /**
     * Get the size of the database in bytes.
     */
    public function getDatabaseSize(): int;
}
