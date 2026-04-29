<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Database;

use Modules\Polyx\Backup\Contracts\DatabaseDumperInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class PostgresDumper implements DatabaseDumperInterface
{
    protected function resolveBinaryPath(array $config, string $binary): string
    {
        $path = $config['dump']['dump_binary_path'] ?? '';

        if ($path) {
            return rtrim($path, '\\/') . DIRECTORY_SEPARATOR . $binary;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            for ($version = 22; $version >= 10; $version--) {
                $testPath = "C:\\Program Files\\PostgreSQL\\{$version}\\bin\\{$binary}.exe";
                if (file_exists($testPath)) {
                    return $testPath;
                }
            }
            
            $laragonPath = "C:\\laragon\\bin\\postgresql";
            if (is_dir($laragonPath)) {
                $dirs = glob($laragonPath . '\\postgresql-*');
                if (!empty($dirs)) {
                    rsort($dirs);
                    $testPath = $dirs[0] . "\\bin\\{$binary}.exe";
                    if (file_exists($testPath)) {
                        return $testPath;
                    }
                }
            }
        }

        return $binary;
    }

    public function getProtectedTables(): array
    {
        return [
            'backup_records',
            'backup_cloud_accounts',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'failed_jobs',
            'job_batches',
        ];
    }

    public function dump(string $outputPath, array $options = []): bool
    {
        $config = config('database.connections.' . config('database.default'));

        $excludedTables = array_unique(array_merge(
            $this->getProtectedTables(),
            $options['excludedTables'] ?? []
        ));

        $pgDump = $this->resolveBinaryPath($config, 'pg_dump');

        $command = sprintf(
            '%s -h %s -p %s -U %s -d %s --format=plain --no-owner --no-privileges --no-acl',
            escapeshellarg($pgDump),
            escapeshellarg($config['host'] ?? '127.0.0.1'),
            escapeshellarg((string) ($config['port'] ?? 5432)),
            escapeshellarg($config['username'] ?? 'postgres'),
            escapeshellarg($config['database'] ?? 'polycms')
        );

        foreach ($excludedTables as $table) {
            $command .= ' --exclude-table=' . escapeshellarg($table);
        }

        $command .= ' > ' . escapeshellarg($outputPath);

        Log::info('Backup: Starting PostgreSQL dump', [
            'tables_excluded' => $excludedTables,
        ]);

        $result = Process::timeout(600)->env(array_merge(getenv(), [
            'PGPASSWORD' => $config['password'] ?? ''
        ]))->run($command);

        if (!$result->successful()) {
            $error = $result->errorOutput();
            Log::error('Backup: PostgreSQL dump failed', ['error' => $error]);
            throw new \RuntimeException('pg_dump failed: ' . $error);
        }

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('pg_dump produced empty output');
        }

        Log::info('Backup: PostgreSQL dump completed', [
            'size' => filesize($outputPath),
        ]);

        return true;
    }

    public function restore(string $inputPath, array $options = []): bool
    {
        if (!file_exists($inputPath)) {
            throw new \RuntimeException('SQL dump file not found: ' . $inputPath);
        }

        $config = config('database.connections.' . config('database.default'));
        $useTransaction = $options['transaction'] ?? true;

        $psql = $this->resolveBinaryPath($config, 'psql');

        $command = sprintf(
            '%s -h %s -p %s -U %s -d %s %s -f %s',
            escapeshellarg($psql),
            escapeshellarg($config['host'] ?? '127.0.0.1'),
            escapeshellarg((string) ($config['port'] ?? 5432)),
            escapeshellarg($config['username'] ?? 'postgres'),
            escapeshellarg($config['database'] ?? 'polycms'),
            $useTransaction ? '--single-transaction' : '',
            escapeshellarg($inputPath)
        );

        Log::info('Backup: Starting PostgreSQL restore');

        $result = Process::timeout(1200)->env(array_merge(getenv(), [
            'PGPASSWORD' => $config['password'] ?? ''
        ]))->run($command);

        if (!$result->successful()) {
            $error = $result->errorOutput();
            Log::error('Backup: PostgreSQL restore failed', ['error' => $error]);
            throw new \RuntimeException('psql restore failed: ' . $error);
        }

        Log::info('Backup: PostgreSQL restore completed');

        return true;
    }

    public function getDriverName(): string
    {
        return 'pgsql';
    }

    public function getTables(): array
    {
        $tables = DB::select(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
        );

        return array_map(fn ($t) => $t->tablename, $tables);
    }

    public function getDatabaseSize(): int
    {
        $result = DB::selectOne(
            'SELECT pg_database_size(current_database()) as size'
        );

        return (int) ($result->size ?? 0);
    }
}
