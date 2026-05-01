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
        if (!Schema::hasColumn('banner_sliders', 'gradient_color')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->string('gradient_color')->nullable()->after('text_color');
            });
        }

        if (!Schema::hasColumn('banner_sliders', 'gradient_degree')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->integer('gradient_degree')->default(135)->after('gradient_color');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('banner_sliders', 'gradient_degree')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->dropColumn('gradient_degree');
            });
        }

        if (Schema::hasColumn('banner_sliders', 'gradient_color')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                $table->dropColumn('gradient_color');
            });
        }
    }
};
