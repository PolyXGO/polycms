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
        if (!Schema::hasColumn('banner_sliders', 'type')) {
            Schema::table('banner_sliders', function (Blueprint $table) {
                // Banner type: 'image' or 'text'
                $table->enum('type', ['image', 'text'])->default('image')->after('title');

            // Text content for text banners
            $table->text('content')->nullable()->after('description');

            // CTA Button settings
            $table->string('button_text')->nullable()->after('content');
            $table->string('button_link')->nullable()->after('button_text');
            $table->enum('button_target', ['_self', '_blank'])->default('_self')->after('button_link');
            $table->string('button_rel')->nullable()->after('button_target');

            // Colors and styling
            $table->string('background_color')->nullable()->after('button_rel');
            $table->string('button_bg_color')->nullable()->after('background_color');
            $table->string('button_text_color')->nullable()->after('button_bg_color');
            $table->string('text_color')->nullable()->after('button_text_color');

            // Gradient settings
            $table->string('gradient_color')->nullable()->after('text_color');
            $table->integer('gradient_degree')->default(135)->after('gradient_color'); // 0-360 degrees

            // Link settings (if no button link, use banner link)
            $table->string('link_rel')->nullable()->after('link_target');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner_sliders', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'content',
                'button_text',
                'button_link',
                'button_target',
                'button_rel',
                'background_color',
                'button_bg_color',
                'button_text_color',
                'text_color',
                'gradient_color',
                'gradient_degree',
                'link_rel',
            ]);
        });
    }
};
