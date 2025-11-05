<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, try to drop old unique slug constraint if exists
        try {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }

        // Add unique constraint for type + slug combination
        try {
            Schema::table('tags', function (Blueprint $table) {
                // Index for slug uniqueness per type (allows same slug in different types)
                $table->unique(['type', 'slug'], 'idx_type_slug_unique');
            });
        } catch (\Exception $e) {
            // Unique constraint might already exist, check if it's the right one
            // If old unique on slug only exists, we need to handle it differently
            // For now, just continue
        }

        // Optimize pivot tables with indexes (only if they don't exist)
        try {
            Schema::table('post_tag', function (Blueprint $table) {
                // Check if indexes exist before creating
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_post_tag_post_id'")) {
                    $table->index('post_id', 'idx_post_tag_post_id');
                }
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_post_tag_tag_id'")) {
                    $table->index('tag_id', 'idx_post_tag_tag_id');
                }
            });
        } catch (\Exception $e) {
            // Indexes might already exist or table structure differs
        }

        try {
            Schema::table('product_tag', function (Blueprint $table) {
                // Check if indexes exist before creating
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_product_tag_product_id'")) {
                    $table->index('product_id', 'idx_product_tag_product_id');
                }
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_product_tag_tag_id'")) {
                    $table->index('tag_id', 'idx_product_tag_tag_id');
                }
            });
        } catch (\Exception $e) {
            // Indexes might already exist or table structure differs
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('idx_type_slug_unique');

            // Restore old unique slug constraint
            $table->unique('slug', 'tags_slug_unique');
        });

        try {
            Schema::table('post_tag', function (Blueprint $table) {
                $table->dropIndex('idx_post_tag_post_id');
                $table->dropIndex('idx_post_tag_tag_id');
            });
        } catch (\Exception $e) {
            // Indexes might not exist
        }

        try {
            Schema::table('product_tag', function (Blueprint $table) {
                $table->dropIndex('idx_product_tag_product_id');
                $table->dropIndex('idx_product_tag_tag_id');
            });
        } catch (\Exception $e) {
            // Indexes might not exist
        }
    }
};
