<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Drivers\Database;

use Modules\Polyx\Backup\Contracts\DatabaseDumperInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class MySqlDumper implements DatabaseDumperInterface
{
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
        $database = $config['database'] ?? 'polycms';

        $excludedTables = array_unique(array_merge(
            $this->getProtectedTables(),
            $options['excludedTables'] ?? []
        ));

        $command = sprintf(
            'mysqldump -h %s -P %s -u %s %s --single-transaction --routines --triggers --set-charset',
            escapeshellarg($config['host'] ?? '127.0.0.1'),
            escapeshellarg((string) ($config['port'] ?? 3306)),
            escapeshellarg($config['username'] ?? 'root'),
            !empty($config['password']) ? '-p' . escapeshellarg($config['password']) : ''
        );

        foreach ($excludedTables as $table) {
            $command .= ' --ignore-table=' . escapeshellarg($database . '.' . $table);
        }

        $command .= ' ' . escapeshellarg($database);
        $command .= ' > ' . escapeshellarg($outputPath);

        Log::info('Backup: Starting MySQL dump', [
            'tables_excluded' => $excludedTables,
        ]);

        $result = Process::timeout(600)->run($command);

        if (!$result->successful()) {
            $error = $result->errorOutput();
            Log::error('Backup: MySQL dump failed', ['error' => $error]);
            throw new \RuntimeException('mysqldump failed: ' . $error);
        }

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('mysqldump produced empty output');
        }

        Log::info('Backup: MySQL dump completed', [
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

        // Chunked SQL execution for large files (inspired by demo_builder pattern)
        $handle = fopen($inputPath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Cannot open SQL dump file: ' . $inputPath);
        }

        Log::info('Backup: Starting MySQL restore (chunked)');

        // Use raw PDO for restore to avoid Eloquent overhead
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec("SET SQL_MODE = ''");
        $pdo->exec('SET AUTOCOMMIT = 0');
        $pdo->exec('SET UNIQUE_CHECKS = 0');

        $useTransaction = $options['transaction'] ?? true;
        if ($useTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $queryStr = '';
            $lineNum = 0;

            while (($line = fgets($handle)) !== false) {
                $lineNum++;
                $trimmed = trim($line);

                // Skip comments and empty lines
                if (empty($trimmed) ||
                    str_starts_with($trimmed, '--') ||
                    str_starts_with($trimmed, '#') ||
                    str_starts_with($trimmed, '/*')) {
                    continue;
                }

                $queryStr .= $line;

                // Execute when we hit a semicolon at end of line
                if (str_ends_with(rtrim($trimmed), ';')) {
                    $queryStr = trim($queryStr);
                    if (!empty($queryStr)) {
                        $pdo->exec($queryStr);
                    }
                    $queryStr = '';
                }
            }

            // Execute any remaining query
            $queryStr = trim($queryStr);
            if (!empty($queryStr)) {
                $pdo->exec($queryStr);
            }

            if ($useTransaction) {
                $pdo->commit();
            }

            Log::info('Backup: MySQL restore completed', ['lines' => $lineNum]);
        } catch (\Exception $e) {
            if ($useTransaction) {
                try {
                    $pdo->rollBack();
                } catch (\Exception $rollbackEx) {
                    Log::warning('Backup: Rollback failed', ['error' => $rollbackEx->getMessage()]);
                }
            }
            throw new \RuntimeException('MySQL restore failed at line ' . $lineNum . ': ' . $e->getMessage());
        } finally {
            fclose($handle);
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                $pdo->exec('SET UNIQUE_CHECKS = 1');
                $pdo->exec('SET AUTOCOMMIT = 1');
                $pdo->exec("SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            } catch (\Exception $cleanupEx) {
                // Silent cleanup
            }
        }

        return true;
    }

    public function getDriverName(): string
    {
        return 'mysql';
    }

    public function getTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . config('database.connections.' . config('database.default') . '.database');

        return array_map(fn ($t) => $t->$key, $tables);
    }

    public function getDatabaseSize(): int
    {
        $database = config('database.connections.' . config('database.default') . '.database');
        $result = DB::selectOne(
            "SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = ?",
            [$database]
        );

        return (int) ($result->size ?? 0);
    }
}
