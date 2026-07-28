<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (!Schema::hasColumn('posts', 'layout_template_id')) {
                $table->foreignId('layout_template_id')
                    ->nullable()
                    ->after('layout')
                    ->constrained('layout_assets')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'layout_template_id')) {
                $table->dropConstrainedForeignId('layout_template_id');
            }
        });
    }
};
