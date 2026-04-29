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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('product_services')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products'); // Denormalized
            
            $table->dateTime('starts_at');
            $table->dateTime('expires_at')->nullable();
            
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended', 'trial'])->default('active');
            
            // Recurring Logic
            $table->boolean('is_auto_renew')->default(false);
            $table->string('gateway_profile_id', 255)->nullable();
            $table->foreignId('renewed_from_subscription_id')->nullable()->constrained('user_subscriptions');
            
            $table->timestamps();
        });

        Schema::create('product_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('user_subscriptions')->onDelete('cascade');
            $table->string('license_key', 100)->unique();
            
            $table->integer('max_activations')->default(1);
            $table->integer('activation_count')->default(0);
            
            $table->enum('status', ['active', 'revoked', 'suspended'])->default('active');
            
            $table->timestamps();
        });

        Schema::create('license_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('product_licenses')->onDelete('cascade');
            
            $table->string('domain', 255)->nullable();
            $table->string('hardware_id', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamp('activated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_activations');
        Schema::dropIfExists('product_licenses');
        Schema::dropIfExists('user_subscriptions');
    }
};
