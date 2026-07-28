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
        Schema::create('contact_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->json('fields')->nullable()->comment('Field schemas: name, type, required, etc.');
            $table->string('type', 50)->default('contact')->comment('newsletter, contact, feedback, etc.');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->nullable()->constrained('contact_forms')->onDelete('set null');
            $table->string('type', 50)->default('contact');
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->json('data')->nullable()->comment('Raw submitted values');
            $table->enum('status', ['unread', 'read'])->default('unread');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('contact_forms');
    }
};
