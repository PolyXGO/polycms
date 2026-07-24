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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('code', 50)->unique()->comment('Order Code (e.g., ORD-2026-001)');
            $table->decimal('total_amount', 12, 2)->comment('Total after discount');
            $table->decimal('subtotal_amount', 12, 2)->comment('Total before discount');
            $table->char('currency', 3)->default('USD');
            
            // Discount Info
            $table->string('discount_code', 50)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0); 
            
            // Order Status
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'failed', 'cancellation_requested'])->default('pending');
            
            // Payment Info
            $table->string('payment_method', 50)->nullable();
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            
            // Refund Info
            $table->text('refund_reason')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->string('refund_transaction_ref', 100)->nullable();
            
            $table->text('customer_note')->nullable();
            $table->json('billing_address')->nullable();
            
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('service_id')->nullable()->constrained('product_services'); 
            
            $table->string('name', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2);
            $table->decimal('total', 12, 2);
            
            $table->json('metadata')->nullable();
        });

        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('order_id')->constrained('orders');
            $table->string('gateway', 50);
            $table->string('transaction_ref', 100)->nullable();
            
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->json('payload')->nullable();
            
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
