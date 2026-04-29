<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('regions'); // [{"country":"VN","provinces":["HCM","HN"]}, {"country":"US"}]
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(0); // Lower = matched first
            $table->timestamps();

            $table->index(['is_active', 'priority'], 'idx_shipping_zones_active');
        });

        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('shipping_zones')->cascadeOnDelete();
            $table->string('name');       // "Standard Shipping", "Express"
            $table->string('type');       // flat_rate, free_shipping, weight_based, price_based
            $table->decimal('cost', 12, 2)->default(0);
            $table->json('rules')->nullable(); // {"min_weight":0,"max_weight":30,"cost_per_kg":1.5}
            $table->decimal('free_threshold', 12, 2)->nullable();
            $table->string('estimated_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['zone_id', 'is_active', 'position'], 'idx_shipping_methods_zone');
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country', 2);  // ISO 3166-1 alpha-2
            $table->string('state')->nullable();
            $table->decimal('rate', 5, 4); // 0.1000 = 10%
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamps();

            $table->index(['country', 'state', 'is_active'], 'idx_tax_rates_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
    }
};
