<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists but has wrong structure
        if (Schema::hasTable('banner_slider_settings')) {
            // Check if 'key' column exists
            if (!Schema::hasColumn('banner_slider_settings', 'key')) {
                // Drop and recreate the table
                Schema::dropIfExists('banner_slider_settings');
            }
        }

        // Create the table if it doesn't exist
        if (!Schema::hasTable('banner_slider_settings')) {
            Schema::create('banner_slider_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            // Insert default settings
            \Illuminate\Support\Facades\DB::table('banner_slider_settings')->insert([
                ['key' => 'auto_slide', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'auto_slide_interval', 'value' => '5000', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'transition_effect', 'value' => 'slide', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'show_navigation', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'show_indicators', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ]);
        } else {
            // Table exists, just ensure columns are correct
            if (!Schema::hasColumn('banner_slider_settings', 'key')) {
                Schema::table('banner_slider_settings', function (Blueprint $table) {
                    $table->string('key')->unique()->after('id');
                });
            }
            if (!Schema::hasColumn('banner_slider_settings', 'value')) {
                Schema::table('banner_slider_settings', function (Blueprint $table) {
                    $table->text('value')->nullable()->after('key');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the table in down migration to preserve data
    }
};
