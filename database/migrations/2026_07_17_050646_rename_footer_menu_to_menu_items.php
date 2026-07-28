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
        if (Schema::hasTable('widget_instances')) {
            \DB::table('widget_instances')
                ->where('widget_type', 'footer_menu')
                ->update(['widget_type' => 'menu_items']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('widget_instances')) {
            \DB::table('widget_instances')
                ->where('widget_type', 'menu_items')
                ->update(['widget_type' => 'footer_menu']);
        }
    }
};
