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
        Schema::create('product_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Upper case automatically');
            $table->string('title', 255);
            $table->text('description')->nullable();
            
            // Discount Value
            $table->enum('type', ['percent', 'fixed_amount'])->default('percent');
            $table->decimal('value', 12, 2);
            
            // Constraints
            $table->decimal('min_order_value', 12, 2)->nullable();
            $table->decimal('max_discount_value', 12, 2)->nullable();
            
            // Usage Limits
            $table->integer('usage_limit')->nullable()->comment('Total usage limit');
            $table->integer('usage_limit_per_user')->default(1);
            $table->integer('usage_count')->default(0);
            
            // Scope & Validity
            $table->json('scope_config')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['billing', 'shipping'])->default('billing');
            $table->boolean('is_default')->default(false);
            
            $table->string('full_name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('tax_id', 50)->nullable();
            
            $table->string('country', 2)->default('US');
            $table->string('province', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('address_line', 255);
            
            $table->timestamps();
        });

        Schema::create('order_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('invoice_number', 50)->unique();
            
            $table->json('billing_snapshot');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            
            $table->string('file_url', 255)->nullable();
            $table->json('attachments')->nullable();
            $table->string('template_name', 100)->default('default');
            
            $table->enum('status', ['draft', 'issued', 'void'])->default('issued');
            $table->dateTime('issued_at')->nullable();
            
            $table->timestamps();
        });

        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->string('code', 50)->primary();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            
            $table->json('config')->nullable()->comment('Encrypted API keys');
            $table->string('handler_class', 255);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('order_invoices');
        Schema::dropIfExists('user_addresses');
        Schema::dropIfExists('product_coupons');
    }
};
