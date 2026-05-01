<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'product_id', 'variant_id'], 'wishlists_user_product_variant');
            $table->index('user_id', 'idx_wishlists_user');
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->boolean('verified_purchase')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // SECURITY: One review per user per product
            $table->unique(['product_id', 'user_id'], 'product_reviews_user_product');
            // PERFORMANCE: Aggregation queries
            $table->index(['product_id', 'status', 'rating'], 'idx_reviews_product');
            $table->index('user_id', 'idx_reviews_user');
        });

        // Denormalized rating on products for fast sorting
        if (!Schema::hasColumn('products', 'avg_rating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('avg_rating', 3, 2)->default(0)->after('views');
                $table->unsignedInteger('review_count')->default(0)->after('avg_rating');

                $table->index('avg_rating', 'idx_products_rating');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'avg_rating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_rating');
                $table->dropColumn(['avg_rating', 'review_count']);
            });
        }

        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('wishlists');
    }
};
