<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('polyfengshui_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('token', 80)->unique();
            $table->string('domain')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polyfengshui_tokens');
    }
};

