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
        if (!Schema::hasColumn('banner_sliders', 'countdown_enabled')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->boolean('countdown_enabled')->default(false)->after('gradient_degree');
                $table->timestamp('countdown_date')->nullable()->after('countdown_enabled');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('banner_sliders', 'countdown_date')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->dropColumn(['countdown_enabled', 'countdown_date']);
            });
        }
    }
};
