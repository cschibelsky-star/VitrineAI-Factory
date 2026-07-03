<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_capability_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capability_id')->constrained('factory_capabilities')->cascadeOnDelete();
            $table->string('version', 40)->default('0.1.0');
            $table->string('status', 30)->default('draft');
            $table->json('schema')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['capability_id', 'version']);
        });

        Schema::create('factory_capability_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capability_id')->constrained('factory_capabilities')->cascadeOnDelete();
            $table->foreignId('related_capability_id')->nullable()->constrained('factory_capabilities')->nullOnDelete();
            $table->foreignId('blueprint_id')->nullable()->constrained('factory_blueprints')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('factory_products')->nullOnDelete();
            $table->string('link_type', 30)->default('related');
            $table->string('status', 30)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_capability_links');
        Schema::dropIfExists('factory_capability_versions');
    }
};
