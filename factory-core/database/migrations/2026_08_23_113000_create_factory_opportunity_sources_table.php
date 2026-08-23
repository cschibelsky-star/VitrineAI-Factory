<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_opportunity_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('scope')->default('national');
            $table->string('status')->default('planned');
            $table->string('connector_type')->default('manual');
            $table->text('base_url')->nullable();
            $table->json('supported_profile_types')->nullable();
            $table->json('supported_opportunity_types')->nullable();
            $table->json('mapping_contract')->nullable();
            $table->json('source_dna')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->json('last_sync_evidence')->nullable();
            $table->timestamps();
        });

        Schema::table('factory_opportunities', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->after('product_id')->constrained('factory_opportunity_sources')->nullOnDelete();
            $table->string('external_id')->nullable()->after('source_url');
            $table->string('ingestion_status')->default('normalized')->after('external_id');
            $table->json('raw_payload')->nullable()->after('opportunity_dna');
            $table->unique(['source_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('factory_opportunities', function (Blueprint $table) {
            $table->dropUnique(['source_id', 'external_id']);
            $table->dropConstrainedForeignId('source_id');
            $table->dropColumn(['external_id', 'ingestion_status', 'raw_payload']);
        });

        Schema::dropIfExists('factory_opportunity_sources');
    }
};
