<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create sample_notes table
 *
 * This migration demonstrates the PolyCMS module migration pattern:
 * - Place migrations in: src/database/migrations/
 * - Use the module prefix in table names (e.g., sample_)
 * - Always include up() and down() methods
 * - The ServiceProvider auto-discovers and runs these
 *
 * Naming convention: YYYY_MM_DD_HHMMSS_create_tablename_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_notes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content')->nullable();
            $table->string('color', 20)->default('blue');
            $table->boolean('is_pinned')->default(false);
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();

            // Indexes for common queries
            $table->index('user_id');
            $table->index('is_pinned');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_notes');
    }
};
