<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitPresetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polycms:init-presets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize default presets for PolyCMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing default presets...');
        
        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\CorePresetSeeder',
                '--force' => true,
            ]);
            
            $this->info('Default presets initialized successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to initialize presets: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
