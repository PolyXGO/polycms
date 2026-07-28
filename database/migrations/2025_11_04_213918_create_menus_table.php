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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location')->nullable(); // header, footer, sidebar, etc.
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('location');
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('type')->default('custom'); // custom, post, page, category, product
            $table->unsignedBigInteger('linkable_id')->nullable(); // ID of linked entity
            $table->string('linkable_type')->nullable(); // Type of linked entity
            $table->string('target')->default('_self'); // _self, _blank
            $table->string('icon')->nullable();
            $table->string('css_class')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['menu_id', 'order']);
            $table->index('parent_id');
            $table->index(['linkable_id', 'linkable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
