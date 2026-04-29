<?php

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
        Schema::create('product_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('code', 50)->comment('Unique code per product e.g., BASIC-TRIAL');
            $table->string('name', 255)->comment('Display name e.g., 7 Days Trial');
            $table->decimal('price', 12, 2)->default(0);
            
            // Access Control
            $table->enum('access_type', ['permanent', 'subscription'])->default('subscription');
            $table->integer('duration_value')->nullable()->comment('e.g., 7, 30, 365');
            $table->enum('duration_unit', ['day', 'month', 'year'])->nullable();
            $table->integer('trial_period_days')->default(0);
            $table->boolean('is_recurring')->default(false);
            
            // Capabilities Hook & License Policy
            $table->json('capabilities')->nullable()->comment('Feature flags');
            $table->json('license_policy')->nullable()->comment('License generation policy');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_services');
    }
};
