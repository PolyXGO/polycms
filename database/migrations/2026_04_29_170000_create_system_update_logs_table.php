<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_update_logs', function (Blueprint $table) {
            $table->id();
            $table->string('from_version', 20);
            $table->string('to_version', 20);
            $table->string('status', 20)->default('pending'); // pending, running, success, failed, rolled_back
            $table->string('backup_path', 500)->nullable();
            $table->json('steps')->nullable(); // Array of {step, status, message, timestamp}
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_update_logs');
    }
};
