<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'allow_refund')) {
                $table->boolean('allow_refund')->default(true)->after('featured');
            }

            if (!Schema::hasColumn('products', 'refund_window_days')) {
                $table->unsignedInteger('refund_window_days')->nullable()->after('allow_refund');
            }

            if (!Schema::hasColumn('products', 'refund_policy_note')) {
                $table->string('refund_policy_note', 1000)->nullable()->after('refund_window_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $drop = [];

            if (Schema::hasColumn('products', 'refund_policy_note')) {
                $drop[] = 'refund_policy_note';
            }
            if (Schema::hasColumn('products', 'refund_window_days')) {
                $drop[] = 'refund_window_days';
            }
            if (Schema::hasColumn('products', 'allow_refund')) {
                $drop[] = 'allow_refund';
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};

