<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // status_change, note, refund, shipping_update, system
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->boolean('is_customer_visible')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at'], 'idx_order_notes_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notes');
    }
};
