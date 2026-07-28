<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('layout_assets')) {
            return;
        }

        Schema::create('layout_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 32);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('key')->nullable()->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('layout', 32)->default('landing');
            $table->json('content_raw')->nullable();
            $table->longText('content_html')->nullable();
            $table->string('preview_image')->nullable();
            $table->json('meta')->nullable();
            $table->json('applies_to')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('source_type', 32)->nullable();
            $table->string('source_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kind', 'category']);
            $table->index(['kind', 'is_system']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layout_assets');
    }
};
