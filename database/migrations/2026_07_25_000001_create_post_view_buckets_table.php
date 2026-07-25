<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('post_view_buckets')) {
            Schema::create('post_view_buckets', function (Blueprint $table) {
                $table->id();
                $table->string('entity_type', 50)->default('post');
                $table->unsignedBigInteger('entity_id');
                $table->timestamp('bucket_at')->useCurrent();
                $table->unsignedInteger('count')->default(1);

                $table->index(['entity_type', 'entity_id', 'bucket_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_view_buckets');
    }
};
