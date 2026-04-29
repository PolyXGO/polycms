<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Polyx\Backup\Services\BackupManager;
use Modules\Polyx\Backup\Models\BackupRecord;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run
        {--type=full : Backup type (full, database, files)}
        {--name= : Custom backup name}
        {--maintenance : Enable maintenance mode}
        {--scheduled : Mark as scheduled backup}';

    protected $description = 'Create a new site backup';

    public function handle(BackupManager $manager): int
    {
        $type = $this->option('type');
        $name = $this->option('name');

        $this->info("Starting {$type} backup...");

        try {
            $record = $manager->createBackup([
                'type' => $type,
                'name' => $name,
                'enableMaintenance' => $this->option('maintenance'),
                'isScheduled' => $this->option('scheduled'),
            ]);

            if ($record->isCompleted()) {
                $this->info("✅ Backup completed: {$record->name}");
                $this->info("   Size: {$record->size_formatted}");
                $this->info("   File: {$record->filename}");
                return self::SUCCESS;
            }

            $this->error("❌ Backup failed: {$record->error_message}");
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ Backup error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
