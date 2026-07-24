<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Polyx\Backup\Services\BackupManager;

class BackupCleanCommand extends Command
{
    protected $signature = 'backup:clean
        {--max-backups=10 : Maximum backups to keep}
        {--max-days=30 : Maximum age in days}';

    protected $description = 'Clean old backups based on retention policy';

    public function handle(BackupManager $manager): int
    {
        $maxBackups = (int) $this->option('max-backups');
        $maxDays = (int) $this->option('max-days');

        $this->info("Cleaning backups (max: {$maxBackups}, age: {$maxDays} days)...");

        $deleted = $manager->cleanOldBackups($maxBackups, $maxDays);

        $this->info("✅ Cleaned {$deleted} backup(s)");
        return self::SUCCESS;
    }
}
