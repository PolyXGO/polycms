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
        // 1. Migrate Product Categories
        $categories = DB::table('categories')->where('type', 'product')->get();
        $catMap = [];
        
        // First pass: insert all without parents to getting new IDs
        foreach ($categories as $cat) {
            $newId = DB::table('product_categories')->insertGetId([
                'name' => $cat->name,
                'slug' => $cat->slug,
                'summary' => $cat->summary,
                'description' => $cat->description,
                'parent_id' => null, // Will update in second pass
                'order' => $cat->order,
                'image' => $cat->image,
                'path' => $cat->path,
                'depth' => $cat->depth,
                'products_count' => $cat->products_count,
                'created_at' => $cat->created_at,
                'updated_at' => $cat->updated_at,
                'deleted_at' => $cat->deleted_at,
            ]);
            $catMap[$cat->id] = $newId;
        }

        // Second pass: update parents
        foreach ($categories as $cat) {
            if ($cat->parent_id && isset($catMap[$cat->parent_id])) {
                DB::table('product_categories')
                    ->where('id', $catMap[$cat->id])
                    ->update(['parent_id' => $catMap[$cat->parent_id]]);
            }
        }

        // 2. Migrate Product Brands
        $brands = DB::table('categories')->where('type', 'product_brand')->get();
        $brandMap = [];
        foreach ($brands as $brand) {
            $newId = DB::table('product_brands')->insertGetId([
                'name' => $brand->name,
                'slug' => $brand->slug,
                'summary' => $brand->summary,
                'description' => $brand->description,
                'order' => $brand->order,
                'image' => $brand->image,
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
                'deleted_at' => $brand->deleted_at,
            ]);
            $brandMap[$brand->id] = $newId;
        }

        // 3. Update product_category pivot table
        if (Schema::hasTable('product_category')) {
            Schema::table('product_category', function (Blueprint $table) {
                // Drop old foreign key if it exists (might be just an index or actual FK)
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Exception $e) {
                    // Ignore if no foreign key
                }
            });

            foreach ($catMap as $oldId => $newId) {
                DB::table('product_category')
                    ->where('category_id', $oldId)
                    ->update(['category_id' => $newId]);
            }

            Schema::table('product_category', function (Blueprint $table) {
                $table->foreign('category_id')
                    ->references('id')
                    ->on('product_categories')
                    ->onDelete('cascade');
            });
        }

        // 4. Update product_brand pivot table
        if (Schema::hasTable('product_brand')) {
            Schema::table('product_brand', function (Blueprint $table) {
                try {
                    $table->dropForeign(['brand_id']);
                } catch (\Exception $e) {
                    // Ignore
                }
            });

            foreach ($brandMap as $oldId => $newId) {
                DB::table('product_brand')
                    ->where('brand_id', $oldId)
                    ->update(['brand_id' => $newId]);
            }

            Schema::table('product_brand', function (Blueprint $table) {
                $table->foreign('brand_id')
                    ->references('id')
                    ->on('product_brands')
                    ->onDelete('cascade');
            });
        }

        // 5. Cleanup categories table
        DB::table('categories')->whereIn('type', ['product', 'product_brand'])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversing is complex because of IDs, but we can attempt to move data back
        // For simplicity in this task, we assume rollback is not primary focus but we should be careful.
    }
};
