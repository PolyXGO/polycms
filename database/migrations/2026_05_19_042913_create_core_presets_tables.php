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
        Schema::create('polycms_preset_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->index()->comment('button_style, macro, snippet, etc.');
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('polycms_preset_categories')
                  ->onDelete('set null');
        });

        Schema::create('polycms_presets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type')->index()->comment('button_style, text_snippet, macro, etc.');
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('payload')->comment('Flexible JSON payload for the preset data');
            $table->boolean('is_global')->default(true)->comment('True if available to all admins, false if scoped to creator');
            $table->boolean('is_system')->default(false)->comment('System presets cannot be deleted');
            $table->timestamps();

            $table->foreign('category_id')
                  ->references('id')
                  ->on('polycms_preset_categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polycms_presets');
        Schema::dropIfExists('polycms_preset_categories');
    }
};
