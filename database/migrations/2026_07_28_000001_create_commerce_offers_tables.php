<?php

declare(strict_types=1);

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
        // 1. Tiered Pricing Table (Escalation by sales_count)
        if (!Schema::hasTable('product_tiered_prices')) {
            Schema::create('product_tiered_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('min_sales')->default(0);
                $table->integer('max_sales')->nullable();
                $table->decimal('price', 10, 2);
                $table->string('label')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['product_id', 'min_sales', 'max_sales']);
            });
        }

        // 2. Volume Discounts Table (Bulk Quantity in Cart)
        if (!Schema::hasTable('product_volume_discounts')) {
            Schema::create('product_volume_discounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('min_qty')->default(1);
                $table->integer('max_qty')->nullable();
                $table->string('discount_type')->default('percentage'); // 'percentage', 'fixed_amount', 'fixed_price'
                $table->decimal('discount_value', 10, 2)->default(0);
                $table->string('label')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['product_id', 'min_qty', 'max_qty']);
            });
        }

        // 3. Product Bundles & Order Spend Threshold Table
        if (!Schema::hasTable('product_bundle_items')) {
            Schema::create('product_bundle_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('bundle_product_id')->constrained('products')->onDelete('cascade');
                $table->decimal('min_order_total', 10, 2)->default(0); // Cart / Order spend threshold
                $table->string('discount_type')->default('percentage'); // 'percentage', 'fixed_amount', 'fixed_price'
                $table->decimal('discount_value', 10, 2)->default(0);
                $table->boolean('is_optional')->default(true);
                $table->boolean('is_frequently_bought')->default(true);
                $table->string('label')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['product_id', 'bundle_product_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bundle_items');
        Schema::dropIfExists('product_volume_discounts');
        Schema::dropIfExists('product_tiered_prices');
    }
};
