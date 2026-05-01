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
        // Check if old tags table exists
        if (!Schema::hasTable('tags')) {
            return;
        }

        // Migrate post tags (type = 'post')
        $postTags = DB::table('tags')
            ->where('type', 'post')
            ->get();

        foreach ($postTags as $tag) {
            $newId = DB::table('post_tags')->insertGetId([
                'name' => $tag->name,
                'slug' => $tag->slug . '-post', // Add suffix to avoid conflicts
                'description' => $tag->description,
                'created_at' => $tag->created_at,
                'updated_at' => $tag->updated_at,
                'deleted_at' => $tag->deleted_at ?? null,
            ]);

            // Update post_tag pivot table if exists
            if (Schema::hasTable('post_tag')) {
                DB::table('post_tag')
                    ->where('tag_id', $tag->id)
                    ->update(['tag_id' => $newId]);
            }
        }

        // Migrate product tags (type = 'product')
        $productTags = DB::table('tags')
            ->where('type', 'product')
            ->get();

        foreach ($productTags as $tag) {
            $newId = DB::table('product_tags')->insertGetId([
                'name' => $tag->name,
                'slug' => $tag->slug . '-product', // Add suffix to avoid conflicts
                'description' => $tag->description,
                'created_at' => $tag->created_at,
                'updated_at' => $tag->updated_at,
                'deleted_at' => $tag->deleted_at ?? null,
            ]);

            // Update product_tag pivot table if exists
            if (Schema::hasTable('product_tag')) {
                DB::table('product_tag')
                    ->where('tag_id', $tag->id)
                    ->update(['tag_id' => $newId]);
            }
        }

        // Update pivot tables to use new foreign keys
        // Rename columns if needed
        if (Schema::hasTable('post_tag')) {
            Schema::table('post_tag', function (Blueprint $table) {
                // Drop old foreign key
                $table->dropForeign(['tag_id']);
            });

            Schema::table('post_tag', function (Blueprint $table) {
                // Add new foreign key to post_tags
                $table->foreign('tag_id')
                    ->references('id')
                    ->on('post_tags')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('product_tag')) {
            Schema::table('product_tag', function (Blueprint $table) {
                // Drop old foreign key
                $table->dropForeign(['tag_id']);
            });

            Schema::table('product_tag', function (Blueprint $table) {
                // Add new foreign key to product_tags
                $table->foreign('tag_id')
                    ->references('id')
                    ->on('product_tags')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore foreign keys to old tags table if needed
        if (Schema::hasTable('post_tag') && Schema::hasTable('tags')) {
            Schema::table('post_tag', function (Blueprint $table) {
                $table->dropForeign(['tag_id']);
            });

            Schema::table('post_tag', function (Blueprint $table) {
                $table->foreign('tag_id')
                    ->references('id')
                    ->on('tags')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('product_tag') && Schema::hasTable('tags')) {
            Schema::table('product_tag', function (Blueprint $table) {
                $table->dropForeign(['tag_id']);
            });

            Schema::table('product_tag', function (Blueprint $table) {
                $table->foreign('tag_id')
                    ->references('id')
                    ->on('tags')
                    ->onDelete('cascade');
            });
        }
    }
};
