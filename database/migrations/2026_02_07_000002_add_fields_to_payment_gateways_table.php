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
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_gateways', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('payment_gateways', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('sort_order');
            }
            if (!Schema::hasColumn('payment_gateways', 'logo')) {
                $table->string('logo', 500)->nullable()->after('is_default');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'is_default', 'logo']);
        });
    }
};
