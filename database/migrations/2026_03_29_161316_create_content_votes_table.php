<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_votes', function (Blueprint $table) {
            $table->id();
            $table->string('voteable_type', 50);       // 'post', 'page', 'product'
            $table->unsignedBigInteger('voteable_id');  // ID of the content
            $table->string('type', 30);                 // 'helpful', 'not_helpful', 'like', 'dislike', etc.
            $table->string('ip_address', 45)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('source', 50)->nullable();   // 'flexidocs', 'flexiblog', etc.
            $table->timestamps();

            // Indexes
            $table->index(['voteable_type', 'voteable_id'], 'cv_voteable_index');
            $table->index(['voteable_type', 'voteable_id', 'type'], 'cv_voteable_type_index');
            $table->index(['ip_address', 'voteable_type', 'voteable_id'], 'cv_ip_voteable_index');

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_votes');
    }
};
