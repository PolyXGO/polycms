<?php

declare(strict_types=1);

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
        Schema::table('banner_sliders', function (Blueprint $table) {
            if (!Schema::hasColumn('banner_sliders', 'padding_x')) {
                $table->integer('padding_x')->nullable()->after('countdown_duration');
            }
            if (!Schema::hasColumn('banner_sliders', 'padding_y')) {
                $table->integer('padding_y')->nullable()->after('padding_x');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner_sliders', function (Blueprint $table) {
            $table->dropColumn([
                'padding_x',
                'padding_y',
            ]);
        });
    }
};
