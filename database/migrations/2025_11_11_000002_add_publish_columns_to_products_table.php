<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('featured');
            $table->timestamp('scheduled_at')->nullable()->after('published_at');
            $table->index(['status', 'published_at'], 'products_status_published_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_published_at_index');
            $table->dropColumn(['published_at', 'scheduled_at']);
        });
    }
};

