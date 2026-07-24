<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 2048)->index();
            $table->string('to_url', 2048);
            $table->unsignedSmallInteger('type')->default(301)->comment('301 or 302');
            $table->boolean('is_active')->default(true)->index();
            $table->string('note', 500)->nullable();
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();

            $table->unique('from_path');
        });

        Schema::create('seo_404_logs', function (Blueprint $table) {
            $table->id();
            $table->string('path', 2048)->index();
            $table->string('referrer', 2048)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedBigInteger('hits')->default(1);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();

            $table->unique('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_404_logs');
        Schema::dropIfExists('seo_redirects');
    }
};
