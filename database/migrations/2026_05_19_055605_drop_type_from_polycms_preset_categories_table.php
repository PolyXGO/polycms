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
        Schema::table('polycms_preset_categories', function (Blueprint $table) {
            try {
                $table->dropIndex('polycms_preset_categories_type_index');
            } catch (\Throwable $e) {
                // Ignore if index doesn't exist
            }
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polycms_preset_categories', function (Blueprint $table) {
            $table->string('type')->nullable()->index()->comment('button_style, macro, snippet, etc.');
        });
    }
};
