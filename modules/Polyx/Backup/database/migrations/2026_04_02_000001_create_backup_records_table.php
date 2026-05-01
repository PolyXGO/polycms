<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_records', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 50)->default('full'); // full, database, files
            $table->string('status', 50)->default('pending'); // pending, processing, completed, failed, restoring
            $table->string('filename', 500)->nullable();
            $table->string('disk', 50)->default('local'); // local, google_drive, onedrive
            $table->string('remote_path', 1000)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedBigInteger('database_size')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->jsonb('manifest')->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('disk');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_records');
    }
};
