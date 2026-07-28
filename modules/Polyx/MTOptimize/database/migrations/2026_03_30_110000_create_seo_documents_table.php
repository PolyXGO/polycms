<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('site_id', 64)->default('default');
            $table->string('locale', 16)->default('en');
            $table->string('object_type', 64)->nullable();
            $table->string('object_id', 64)->nullable();
            $table->string('route_fingerprint', 128);
            $table->json('payload_json');
            $table->string('checksum', 128)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'locale', 'route_fingerprint'], 'seo_documents_site_locale_fingerprint_unique');
            $table->index(['site_id', 'locale', 'object_type', 'object_id'], 'seo_documents_site_locale_object_index');
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_documents');
    }
};
