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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('frontend'); // frontend, admin
            $table->boolean('is_active')->default(false);
            $table->enum('status', ['installed', 'disabled', 'broken'])->default('installed');
            $table->string('path'); // Base path to theme directory
            $table->string('screenshot')->nullable(); // Relative path to screenshot
            $table->json('meta')->nullable(); // Extra metadata
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
