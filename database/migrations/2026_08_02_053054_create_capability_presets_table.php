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
        Schema::create('capability_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('Preset name e.g., Free installation');
            $table->string('group', 100)->nullable()->comment('Group name e.g., Support, Updates');
            $table->json('translations')->nullable()->comment('JSON for multi-language');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capability_presets');
    }
};
