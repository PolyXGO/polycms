<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_attribute_product', function (Blueprint $table) {
            $table->boolean('is_specification')->default(true)->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('product_attribute_product', function (Blueprint $table) {
            $table->dropColumn('is_specification');
        });
    }
};
