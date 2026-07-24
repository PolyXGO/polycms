<?php

declare(strict_types=1);

namespace Modules\Polyx\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Polyx\Backup\Models\BackupRecord;

class BackupListCommand extends Command
{
    protected $signature = 'backup:list
        {--limit=20 : Number of records to show}';

    protected $description = 'List all backup records';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $backups = BackupRecord::latest()->limit($limit)->get();

        if ($backups->isEmpty()) {
            $this->info('No backups found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Type', 'Status', 'Size', 'Created'],
            $backups->map(fn ($b) => [
                $b->id,
                $b->name,
                $b->type,
                $b->status,
                $b->size_formatted,
                $b->created_at?->format('Y-m-d H:i'),
            ])->toArray()
        );

        return self::SUCCESS;
    }
}
