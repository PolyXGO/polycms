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
            if (!Schema::hasColumn('banner_sliders', 'countdown_repeat')) {
                $table->string('countdown_repeat', 50)->default('none')->after('countdown_date');
            }
            if (!Schema::hasColumn('banner_sliders', 'countdown_duration')) {
                $table->integer('countdown_duration')->default(0)->after('countdown_repeat');
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
                'countdown_repeat',
                'countdown_duration',
            ]);
        });
    }
};
