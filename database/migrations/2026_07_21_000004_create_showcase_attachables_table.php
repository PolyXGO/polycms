<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('showcase_attachables')) {
            Schema::create('showcase_attachables', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('showcase_package_id')->index();
                $table->string('attachable_type')->index();
                $table->unsignedBigInteger('attachable_id')->index();
                $table->timestamps();

                $table->unique(['showcase_package_id', 'attachable_type', 'attachable_id'], 'showcase_attachable_unique');
            });
        }

        // Migrate existing attachable records from showcase_packages table if any
        if (Schema::hasTable('showcase_packages') && Schema::hasColumn('showcase_packages', 'attachable_type')) {
            $existing = DB::table('showcase_packages')
                ->whereNotNull('attachable_type')
                ->whereNotNull('attachable_id')
                ->get();

            foreach ($existing as $row) {
                DB::table('showcase_attachables')->insertOrIgnore([
                    'showcase_package_id' => $row->id,
                    'attachable_type' => $row->attachable_type,
                    'attachable_id' => $row->attachable_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('showcase_attachables');
    }
};
