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
        if (Schema::hasTable('tags')) {
            $hasSlugUnique = Schema::hasIndex('tags', 'tags_slug_unique') || 
                            (config('database.default') === 'pgsql' && DB::selectOne("SELECT 1 FROM pg_constraint WHERE conname = 'tags_slug_unique'"));
            $hasSlugIndex = Schema::hasIndex('tags', 'slug') || 
                           (config('database.default') === 'pgsql' && DB::selectOne("SELECT 1 FROM pg_constraint WHERE conname = 'slug' OR conname = 'tags_slug_unique'"));

            Schema::table('tags', function (Blueprint $table) use ($hasSlugUnique, $hasSlugIndex) {
                if ($hasSlugUnique) {
                    $table->dropUnique('tags_slug_unique');
                } elseif ($hasSlugIndex) {
                    $table->dropUnique(['slug']);
                }
            });
        }

        if (Schema::hasTable('tags')) {
            $hasTypeSlugUnique = Schema::hasIndex('tags', 'idx_type_slug_unique') || 
                               (config('database.default') === 'pgsql' && DB::selectOne("SELECT 1 FROM pg_constraint WHERE conname = 'idx_type_slug_unique'"));
            if (!$hasTypeSlugUnique) {
                Schema::table('tags', function (Blueprint $table) {
                    // Index for slug uniqueness per type (allows same slug in different types)
                    $table->unique(['type', 'slug'], 'idx_type_slug_unique');
                });
            }
        }

        if (Schema::hasTable('post_tag')) {
            Schema::table('post_tag', function (Blueprint $table) {
                try { $table->index('post_id', 'idx_post_tag_post_id'); } catch (\Exception $e) {}
                try { $table->index('tag_id', 'idx_post_tag_tag_id'); } catch (\Exception $e) {}
            });
        }

        if (Schema::hasTable('product_tag')) {
            Schema::table('product_tag', function (Blueprint $table) {
                try { $table->index('product_id', 'idx_product_tag_product_id'); } catch (\Exception $e) {}
                try { $table->index('tag_id', 'idx_product_tag_tag_id'); } catch (\Exception $e) {}
            });
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
