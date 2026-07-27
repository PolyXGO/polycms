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
        if (!Schema::hasTable('products')) {
            return;
        }

        try {
            $schemaManager = Schema::getConnection()->getSchemaBuilder();
            $indexes = method_exists($schemaManager, 'getIndexes') ? $schemaManager->getIndexes('products') : [];
            $indexNameToDrop = null;

            foreach ($indexes as $index) {
                if (!empty($index['unique']) && in_array('sku', $index['columns'] ?? [], true)) {
                    $indexNameToDrop = $index['name'] ?? 'products_sku_unique';
                    break;
                }
            }

            if ($indexNameToDrop) {
                Schema::table('products', function (Blueprint $table) use ($indexNameToDrop) {
                    $table->dropUnique($indexNameToDrop);
                });
            }
        } catch (\Throwable $e) {
            // Silently catch to keep safe across all database drivers
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op
    }
};
