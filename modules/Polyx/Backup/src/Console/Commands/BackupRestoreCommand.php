<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Polyx\Backup\Services\BackupManager;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore
        {id : Backup record ID to restore from}
        {--no-maintenance : Skip maintenance mode}
        {--no-snapshot : Skip creating pre-restore snapshot}';

    protected $description = 'Restore site from a backup';

    public function handle(BackupManager $manager): int
    {
        $id = (int) $this->argument('id');

        if (!$this->confirm("⚠️  This will overwrite current data. Continue?")) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        $this->info("Starting restore from backup #{$id}...");

        try {
            $record = $manager->restore($id, [
                'enableMaintenance' => !$this->option('no-maintenance'),
                'createSnapshot' => !$this->option('no-snapshot'),
            ]);

            $this->info("✅ Restore completed successfully!");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Restore failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
