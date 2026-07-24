<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Media;
use App\Services\MediaService;

class RegenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:regenerate {--id= : Process a specific media ID} {--force : Re-generate even if thumbnails exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate proportional WebP thumbnails for media images';

    /**
     * Execute the console command.
     */
    public function handle(MediaService $mediaService)
    {
        $query = Media::where('type', 'image');

        if ($specificId = $this->option('id')) {
            $query->where('id', $specificId);
        }

        $count = $query->count();
        if ($count === 0) {
            $this->info('No image media found to process.');
            return 0;
        }

        $this->info("Processing {$count} image(s)...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $processed = 0;
        $failed = 0;

        $query->chunk(50, function ($mediaItems) use ($mediaService, $bar, &$processed, &$failed) {
            foreach ($mediaItems as $media) {
                try {
                    $mediaService->generateThumbnails($media);
                    $processed++;
                } catch (\Throwable $e) {
                    $failed++;
                }
                $bar->advance();
                // Free memory per iteration
                if ($processed % 10 === 0) {
                    gc_collect_cycles();
                }
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Complete! {$processed} thumbnails regenerated successfully, {$failed} failed.");

        return 0;
    }
}
