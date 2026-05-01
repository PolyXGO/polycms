<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');          // "Color", "Size"
            $table->string('slug');          // "color", "size"
            $table->json('values');          // ["Red","Blue","Green"] or [{"value":"Red","hex":"#FF0000"}]
            $table->string('display_type')->default('select'); // select, color_swatch, image_swatch, button
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'slug']);
            $table->index('product_id');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->nullable()->unique();
            $table->json('attribute_values'); // {"color": "Red", "size": "XL"}
            $table->decimal('price', 12, 2)->nullable(); // null = inherit from parent
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('stock_status')->default('in_stock'); // in_stock, out_of_stock, on_backorder
            $table->boolean('manage_stock')->default(true);
            $table->decimal('weight', 8, 2)->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // PERFORMANCE: Composite indexes for common queries
            $table->index(['product_id', 'is_active', 'position'], 'idx_variants_product_active');
            $table->index('stock_status', 'idx_variants_stock_status');
        });

        // PostgreSQL bonus: GIN index on JSONB for fast attribute filtering
        if (DB::getDriverName() === 'pgsql') {
            // Laravel json() creates json type; cast to jsonb for GIN index
            DB::statement('ALTER TABLE product_variants ALTER COLUMN attribute_values TYPE jsonb USING attribute_values::jsonb');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_variant_attrs ON product_variants USING GIN (attribute_values jsonb_path_ops)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_attributes');
    }
};
