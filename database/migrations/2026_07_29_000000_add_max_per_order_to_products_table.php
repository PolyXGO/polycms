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
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'max_per_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('max_per_order')->nullable()->after('stock_low_threshold');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'max_per_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('max_per_order');
            });
        }
    }
};
