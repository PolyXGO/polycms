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
            $table->text('restricted_emails')->nullable()->after('scope_config')->comment('Comma-separated emails for restriction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_coupons', function (Blueprint $table) {
            $table->dropColumn('restricted_emails');
        });
    }
};
