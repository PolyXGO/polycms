<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('product_services')) {
            try {
                DB::statement("ALTER TABLE product_services MODIFY duration_unit VARCHAR(50) NULL");
            } catch (\Throwable $e) {
                try {
                    Schema::table('product_services', function (Blueprint $table) {
                        $table->string('duration_unit', 50)->nullable()->change();
                    });
                } catch (\Throwable $ex) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_services')) {
            try {
                DB::statement("ALTER TABLE product_services MODIFY duration_unit VARCHAR(20) NULL");
            } catch (\Throwable $e) {}
        }
    }
};
