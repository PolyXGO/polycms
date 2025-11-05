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
            Schema::table('categories', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        } catch (\Exception $e) {
            // Ignore if constraint doesn't exist
        }

        Schema::table('categories', function (Blueprint $table) {
            // Add path column for quick ancestor queries (e.g., "1/5/12")
            $table->string('path', 500)->nullable()->after('parent_id');

            // Add depth/level column for hierarchy depth (0 = root, 1 = first level, etc.)
            $table->unsignedTinyInteger('depth')->default(0)->after('path');

            // Add usage_count columns for performance
            $table->unsignedInteger('posts_count')->default(0)->after('order');
            $table->unsignedInteger('products_count')->default(0)->after('posts_count');
        });

        // Create optimized indexes for hierarchical queries
        Schema::table('categories', function (Blueprint $table) {
            // Composite index for type + parent queries (common in hierarchical listings)
            $table->index(['type', 'parent_id', 'order'], 'idx_type_parent_order');

            // Index for path queries (finding descendants)
            $table->index('path', 'idx_path');

            // Index for depth queries
            $table->index(['type', 'depth'], 'idx_type_depth');

            // Index for slug uniqueness per type (allows same slug in different types)
            $table->unique(['type', 'slug'], 'idx_type_slug_unique');
        });

        // Optimize pivot tables with indexes (only if they don't exist)
        try {
            Schema::table('post_category', function (Blueprint $table) {
                // Check if indexes exist before creating
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_post_category_post_id'")) {
                    $table->index('post_id', 'idx_post_category_post_id');
                }
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_post_category_category_id'")) {
                    $table->index('category_id', 'idx_post_category_category_id');
                }
            });
        } catch (\Exception $e) {
            // Indexes might already exist or table structure differs
        }

        try {
            Schema::table('product_category', function (Blueprint $table) {
                // Check if indexes exist before creating
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_product_category_product_id'")) {
                    $table->index('product_id', 'idx_product_category_product_id');
                }
                if (!DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name='idx_product_category_category_id'")) {
                    $table->index('category_id', 'idx_product_category_category_id');
                }
            });
        } catch (\Exception $e) {
            // Indexes might already exist or table structure differs
        }

        // Update existing categories with path and depth
        $this->updateExistingCategories();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_type_parent_order');
            $table->dropIndex('idx_path');
            $table->dropIndex('idx_type_depth');
            $table->dropUnique('idx_type_slug_unique');

            // Restore old unique slug constraint
            $table->unique('slug', 'categories_slug_unique');

            // Drop columns
            $table->dropColumn(['path', 'depth', 'posts_count', 'products_count']);
        });

        try {
            Schema::table('post_category', function (Blueprint $table) {
                $table->dropIndex('idx_post_category_post_id');
                $table->dropIndex('idx_post_category_category_id');
            });
        } catch (\Exception $e) {
            // Indexes might not exist
        }

        try {
            Schema::table('product_category', function (Blueprint $table) {
                $table->dropIndex('idx_product_category_product_id');
                $table->dropIndex('idx_product_category_category_id');
            });
        } catch (\Exception $e) {
            // Indexes might not exist
        }
    }

    /**
     * Update existing categories with path and depth
     */
    protected function updateExistingCategories(): void
    {
        // Get all root categories
        $roots = DB::table('categories')->whereNull('parent_id')->get();

        foreach ($roots as $root) {
            $this->updateCategoryPathAndDepth($root->id, null, 0);
        }
    }

    /**
     * Recursively update category path and depth
     */
    protected function updateCategoryPathAndDepth(int $categoryId, ?string $parentPath, int $depth): void
    {
        $path = $parentPath === null ? (string)$categoryId : $parentPath . '/' . $categoryId;

        DB::table('categories')
            ->where('id', $categoryId)
            ->update([
                'path' => $path,
                'depth' => $depth,
            ]);

        // Update children
        $children = DB::table('categories')
            ->where('parent_id', $categoryId)
            ->get();

        foreach ($children as $child) {
            $this->updateCategoryPathAndDepth($child->id, $path, $depth + 1);
        }
    }
};
