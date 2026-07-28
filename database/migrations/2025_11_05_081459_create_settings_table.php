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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, reading, writing, discussion, etc.
            $table->string('type')->default('string'); // string, number, boolean, json, etc.
            $table->text('label')->nullable(); // Human-readable label
            $table->text('description')->nullable(); // Description/help text
            $table->boolean('autoload')->default(true); // Whether to autoload this setting
            $table->timestamps();

            $table->index(['group', 'key']);
            $table->index('autoload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};