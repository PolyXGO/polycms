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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['post', 'page', 'news'])->default('post');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->text('excerpt')->nullable();
            $table->json('content_raw')->nullable(); // Block-based editor content
            $table->longText('content_html')->nullable(); // Rendered HTML
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();

            // Additional fields
            $table->string('featured_image')->nullable();
            $table->integer('views')->default(0);
            $table->integer('order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
            $table->index(['published_at', 'status']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
