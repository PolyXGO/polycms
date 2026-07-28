<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropUnique('product_reviews_user_product');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('product_reviews', 'source')) {
                $table->string('source')->default('site')->after('metadata');
            }
            if (!Schema::hasColumn('product_reviews', 'source_platform')) {
                $table->string('source_platform')->nullable()->after('source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('product_reviews', 'source_platform')) {
                $table->dropColumn('source_platform');
            }
        });
    }
};
