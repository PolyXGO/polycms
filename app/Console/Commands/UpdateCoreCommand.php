<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CoreUpdateService;
use Illuminate\Console\Command;

class UpdateCoreCommand extends Command
{
    protected $signature = 'polycms:update
        {package? : Path to the .zip update package}
        {--rollback= : Path to a backup .zip for rollback}
        {--check : Check for available updates from polycms.org}
        {--list-backups : List available backups}';

    protected $description = 'Update PolyCMS core from a .zip package or rollback to a backup';

    public function __construct(
        private readonly CoreUpdateService $updateService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Check for updates
        if ($this->option('check')) {
            return $this->handleCheckUpdate();
        }

        // List backups
        if ($this->option('list-backups')) {
            return $this->handleListBackups();
        }

        // Rollback
        $rollbackPath = $this->option('rollback');
        if ($rollbackPath) {
            return $this->handleRollback($rollbackPath);
        }

        // Update from package
        $packagePath = $this->argument('package');
        if (!$packagePath) {
            $this->error('Please provide a path to the .zip update package.');
            $this->line('Usage: php artisan polycms:update /path/to/polycms-v1.2.0.zip');
            $this->line('       php artisan polycms:update --rollback=/path/to/backup.zip');
            $this->line('       php artisan polycms:update --check');
            $this->line('       php artisan polycms:update --list-backups');
            return Command::FAILURE;
        }

        return $this->handleUpdate($packagePath);
    }

    private function handleUpdate(string $packagePath): int
    {
        if (!file_exists($packagePath)) {
            $this->error("Package file not found: {$packagePath}");
            return Command::FAILURE;
        }

        // Validate
        $this->info('Validating package...');
        $validation = $this->updateService->validatePackage($packagePath);

        if (!$validation['valid']) {
            $this->error('Validation failed: ' . $validation['error']);
            return Command::FAILURE;
        }

        $this->table(['Property', 'Value'], [
            ['Current Version', $validation['current_version']],
            ['Package Version', $validation['package_version']],
            ['Type', $validation['is_upgrade'] ? '⬆️ Upgrade' : ($validation['is_downgrade'] ? '⬇️ Downgrade' : '🔄 Reinstall')],
            ['Includes vendor/', $validation['has_vendor'] ? '✅ Yes' : '❌ No'],
            ['Includes public/build/', $validation['has_build'] ? '✅ Yes' : '❌ No'],
            ['File Count', (string)$validation['file_count']],
            ['Package Size', $this->formatBytes($validation['file_size'])],
        ]);

        if (!$this->confirm('Proceed with the update?')) {
            $this->info('Update cancelled.');
            return Command::SUCCESS;
        }

        try {
            // Use user ID 1 (typically the primary admin) for CLI updates
            $log = $this->updateService->performUpdate($packagePath, 1);

            $this->newLine();
            $this->info('✅ Update completed successfully!');
            $this->line("Version: v{$log->from_version} → v{$log->to_version}");

            if ($log->steps) {
                $this->newLine();
                $this->info('Update Steps:');
                foreach ($log->steps as $step) {
                    $icon = match ($step['status']) {
                        'success' => '✅',
                        'failed' => '❌',
                        default => '⏳',
                    };
                    $this->line("  {$icon} {$step['step']}: {$step['message']}");
                }
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Update failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function handleRollback(string $backupPath): int
    {
        if (!file_exists($backupPath)) {
            $this->error("Backup file not found: {$backupPath}");
            return Command::FAILURE;
        }

        $this->warn('⚠️  Rolling back will restore core files from the backup.');
        if (!$this->confirm('Proceed with rollback?')) {
            $this->info('Rollback cancelled.');
            return Command::SUCCESS;
        }

        try {
            $log = $this->updateService->rollback($backupPath, 1);
            $this->info('✅ Rollback completed successfully!');
            $this->line("Restored to: v{$log->to_version}");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Rollback failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function handleCheckUpdate(): int
    {
        $this->info('Checking for updates...');
        $result = $this->updateService->checkForUpdates();

        $this->line("Current version: v{$result['current_version']}");

        if ($result['available'] ?? false) {
            $this->info("🆕 New version available: v{$result['latest_version']}");
            if (!empty($result['release_notes'])) {
                $this->line("Release notes: {$result['release_notes']}");
            }
        } else {
            $this->info('✅ You are running the latest version.');
            if (!empty($result['message'])) {
                $this->line($result['message']);
            }
        }

        return Command::SUCCESS;
    }

    private function handleListBackups(): int
    {
        $backups = $this->updateService->getBackups();

        if (empty($backups)) {
            $this->info('No backups found.');
            return Command::SUCCESS;
        }

        $rows = array_map(fn($b) => [
            $b['filename'],
            'v' . $b['version'],
            $this->formatBytes($b['size']),
            $b['created_at'],
        ], $backups);

        $this->table(['Filename', 'Version', 'Size', 'Created'], $rows);
        return Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $value = (float) $bytes;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }
        return round($value, 2) . ' ' . $units[$i];
    }
}
