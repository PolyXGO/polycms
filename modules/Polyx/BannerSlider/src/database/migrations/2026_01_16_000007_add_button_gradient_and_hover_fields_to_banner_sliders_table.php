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
            if (!Schema::hasColumn('banner_sliders', 'button_gradient_color')) {
                $table->string('button_gradient_color', 7)->nullable()->after('button_bg_color');
            }
            if (!Schema::hasColumn('banner_sliders', 'button_gradient_degree')) {
                $table->integer('button_gradient_degree')->default(135)->after('button_gradient_color');
            }
            if (!Schema::hasColumn('banner_sliders', 'button_hover_bg_color')) {
                $table->string('button_hover_bg_color', 7)->nullable()->after('button_gradient_degree');
            }
            if (!Schema::hasColumn('banner_sliders', 'button_hover_text_color')) {
                $table->string('button_hover_text_color', 7)->nullable()->after('button_hover_bg_color');
            }
            if (!Schema::hasColumn('banner_sliders', 'button_hover_gradient_color')) {
                $table->string('button_hover_gradient_color', 7)->nullable()->after('button_hover_text_color');
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
                'button_gradient_color',
                'button_gradient_degree',
                'button_hover_bg_color',
                'button_hover_text_color',
                'button_hover_gradient_color',
            ]);
        });
    }
};
