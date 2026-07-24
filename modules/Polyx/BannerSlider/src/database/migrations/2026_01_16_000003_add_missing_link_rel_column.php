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
        if (!Schema::hasColumn('banner_sliders', 'link_rel')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->string('link_rel')->nullable()->after('link_target');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('banner_sliders', 'link_rel')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->dropColumn('link_rel');
            });
        }
    }
};
