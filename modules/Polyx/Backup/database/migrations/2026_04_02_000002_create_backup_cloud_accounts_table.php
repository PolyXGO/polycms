<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_cloud_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider', 50); // google_drive, onedrive
            $table->string('client_id', 500)->nullable();
            $table->text('client_secret')->nullable(); // Encrypted
            $table->text('access_token')->nullable(); // Encrypted
            $table->text('refresh_token')->nullable(); // Encrypted
            $table->timestamp('expires_at')->nullable();
            $table->string('base_path', 1000)->nullable();
            $table->string('base_path_name', 500)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('provider');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_cloud_accounts');
    }
};
