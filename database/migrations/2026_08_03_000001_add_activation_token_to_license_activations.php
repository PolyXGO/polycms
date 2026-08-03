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
        Schema::table('license_activations', function (Blueprint $table) {
            $table->string('activation_token', 64)->nullable()->unique()->after('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('license_activations', function (Blueprint $table) {
            $table->dropColumn('activation_token');
        });
    }
};
