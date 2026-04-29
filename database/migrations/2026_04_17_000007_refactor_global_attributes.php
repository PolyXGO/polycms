<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop old per-product attributes table (no data to migrate)
        Schema::dropIfExists('product_attributes');

        // 2. Recreate as GLOBAL attributes (no product_id)
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // "Color", "Size", "Material"
            $table->string('slug')->unique();          // "color", "size", "material"
            $table->string('display_type')->default('select'); // select, color_swatch, image_swatch, button
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index('position');
        });

        // 3. Attribute values table
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->string('value');                   // "Red", "S", "Cotton"
            $table->string('slug');                    // "red", "s", "cotton"
            $table->string('hex_color', 7)->nullable(); // "#ef4444" (for color_swatch)
            $table->string('image_url', 500)->nullable(); // for image_swatch
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['attribute_id', 'slug']);
            $table->index(['attribute_id', 'position']);
        });

        // 4. Pivot: which attributes does a product use
        Schema::create('product_attribute_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->json('selected_value_ids')->nullable(); // [1,3,5] or null = all
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_product');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');

        // Restore original per-product attributes table
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->json('values');
            $table->string('display_type')->default('select');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'slug']);
            $table->index('product_id');
        });
    }
};
