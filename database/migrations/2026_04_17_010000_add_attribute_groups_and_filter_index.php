<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create product attribute groups
        Schema::create('product_attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            
            $table->index('position');
        });

        // 2. Add group_id to product_attributes
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('id')->constrained('product_attribute_groups')->nullOnDelete();
        });

        // 3. Create high-performance flat index table for filtering
        Schema::create('product_attribute_value_index', function (Blueprint $table) {
            // Using a simple flat table for highest performance
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('product_attribute_values')->cascadeOnDelete();

            // Covering indexed queries: "WHERE attribute_id = X AND attribute_value_id = Y"
            $table->unique(['product_id', 'attribute_value_id'], 'prod_attr_val_unique');
            $table->index(['attribute_id', 'attribute_value_id'], 'attr_val_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_value_index');

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        Schema::dropIfExists('product_attribute_groups');
    }
};
