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
        Schema::table('product_coupons', function (Blueprint $table) {
            $table->boolean('is_exclusive')->default(false)->after('max_discount_value');
            $table->boolean('is_public')->default(false)->after('is_exclusive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_coupons', function (Blueprint $table) {
            $table->dropColumn(['is_exclusive', 'is_public']);
        });
    }
};
