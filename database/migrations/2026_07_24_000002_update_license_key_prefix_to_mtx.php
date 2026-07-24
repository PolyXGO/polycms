<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update product_licenses table
        if (Schema::hasTable('product_licenses') && Schema::hasColumn('product_licenses', 'license_key')) {
            DB::table('product_licenses')
                ->where('license_key', 'like', 'KEY-%')
                ->chunkById(100, function ($licenses) {
                    foreach ($licenses as $lic) {
                        DB::table('product_licenses')
                            ->where('id', $lic->id)
                            ->update(['license_key' => str_replace('KEY-', 'MTX-', $lic->license_key)]);
                    }
                });
        }

        // 2. Update settings table (using value column)
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'value')) {
            DB::table('settings')
                ->where('value', 'like', '%KEY-%')
                ->chunkById(100, function ($settings) {
                    foreach ($settings as $setting) {
                        DB::table('settings')
                            ->where('id', $setting->id)
                            ->update(['value' => str_replace('KEY-', 'MTX-', $setting->value)]);
                    }
                });
        }

        // 3. Update order_items table metadata if license keys are stored in metadata
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'metadata')) {
            DB::table('order_items')
                ->chunkById(100, function ($items) {
                    foreach ($items as $item) {
                        if (!empty($item->metadata) && str_contains((string) $item->metadata, 'KEY-')) {
                            $newMetadata = str_replace('KEY-', 'MTX-', (string) $item->metadata);
                            DB::table('order_items')
                                ->where('id', $item->id)
                                ->update(['metadata' => $newMetadata]);
                        }
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_licenses') && Schema::hasColumn('product_licenses', 'license_key')) {
            DB::table('product_licenses')
                ->where('license_key', 'like', 'MTX-%')
                ->chunkById(100, function ($licenses) {
                    foreach ($licenses as $lic) {
                        DB::table('product_licenses')
                            ->where('id', $lic->id)
                            ->update(['license_key' => str_replace('MTX-', 'KEY-', $lic->license_key)]);
                    }
                });
        }

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'value')) {
            DB::table('settings')
                ->where('value', 'like', '%MTX-%')
                ->chunkById(100, function ($settings) {
                    foreach ($settings as $setting) {
                        DB::table('settings')
                            ->where('id', $setting->id)
                            ->update(['value' => str_replace('MTX-', 'KEY-', $setting->value)]);
                    }
                });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'metadata')) {
            DB::table('order_items')
                ->chunkById(100, function ($items) {
                    foreach ($items as $item) {
                        if (!empty($item->metadata) && str_contains((string) $item->metadata, 'MTX-')) {
                            $newMetadata = str_replace('MTX-', 'KEY-', (string) $item->metadata);
                            DB::table('order_items')
                                ->where('id', $item->id)
                                ->update(['metadata' => $newMetadata]);
                        }
                    }
                });
        }
    }
};
