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
        Schema::create('widget_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique(); // e.g., sidebar_main, footer_1
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('key');
        });

        Schema::create('widget_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_area_id')->constrained()->onDelete('cascade');
            $table->string('widget_type'); // e.g., recent_posts, menu, html_block
            $table->string('title')->nullable();
            $table->json('config')->nullable(); // Widget configuration/settings
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['widget_area_id', 'order']);
            $table->index('widget_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_instances');
        Schema::dropIfExists('widget_areas');
    }
};
