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
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'refunded_qty')) {
                $table->unsignedInteger('refunded_qty')->default(0)->after('quantity');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'refunded_total')) {
                $table->decimal('refunded_total', 12, 2)->default(0)->after('tax_amount');
            }

            if (!Schema::hasColumn('orders', 'refund_status')) {
                $table->enum('refund_status', ['none', 'partial', 'full'])->default('none')->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'last_refunded_at')) {
                $table->dateTime('last_refunded_at')->nullable()->after('refunded_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('orders', 'refunded_total')) {
                $drop[] = 'refunded_total';
            }
            if (Schema::hasColumn('orders', 'refund_status')) {
                $drop[] = 'refund_status';
            }
            if (Schema::hasColumn('orders', 'last_refunded_at')) {
                $drop[] = 'last_refunded_at';
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'refunded_qty')) {
                $table->dropColumn('refunded_qty');
            }
        });
    }
};
