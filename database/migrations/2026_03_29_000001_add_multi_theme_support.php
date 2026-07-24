<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Multi-Theme Template System:
     * - Adds role/priority/template_registry to themes for Main/Sub support
     * - Adds template_theme to content tables for per-entity template selection
     */
    public function up(): void
    {
        // --- themes table: add multi-theme support columns ---
        Schema::table('themes', function (Blueprint $table) {
            $table->string('role', 10)->nullable()->after('is_active');   // 'main', 'sub', or null (inactive)
            $table->json('template_registry')->nullable()->after('meta'); // cached templates this theme provides
            $table->integer('priority')->default(0)->after('meta');       // sub-theme loading priority

            $table->index(['role', 'is_active'], 'themes_role_active_idx');
        });

        // --- posts table: add template_theme ---
        Schema::table('posts', function (Blueprint $table) {
            $table->string('template_theme', 100)->nullable()->after('layout_template_id');
        });

        // --- products table: add template_theme ---
        Schema::table('products', function (Blueprint $table) {
            $table->string('template_theme', 100)->nullable()->after('layout');
        });

        // --- categories table: add template_theme ---
        Schema::table('categories', function (Blueprint $table) {
            $table->string('template_theme', 100)->nullable()->after('products_count');
        });

        // --- product_categories table: add template_theme ---
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('template_theme', 100)->nullable()->after('products_count');
        });

        // --- Set current active theme as 'main' role ---
        $activeTheme = \App\Models\Theme::where('is_active', true)->first();
        if ($activeTheme) {
            $activeTheme->update(['role' => 'main']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropIndex('themes_role_active_idx');
            $table->dropColumn(['role', 'template_registry', 'priority']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('template_theme');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('template_theme');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('template_theme');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('template_theme');
        });
    }
};
