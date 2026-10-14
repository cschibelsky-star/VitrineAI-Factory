<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_blueprint_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('factory_blueprints')->cascadeOnDelete();
            $table->string('version', 40)->default('0.1.0');
            $table->string('status', 30)->default('draft');
            $table->json('schema')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['blueprint_id', 'version']);
        });

        Schema::create('factory_blueprint_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('factory_blueprints')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('table_name')->nullable();
            $table->string('model_name')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['blueprint_id', 'slug']);
        });

        Schema::create('factory_blueprint_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_entity_id')->constrained('factory_blueprint_entities')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 50)->default('string');
            $table->boolean('nullable')->default(true);
            $table->boolean('required')->default(false);
            $table->boolean('searchable')->default(false);
            $table->boolean('sortable')->default(false);
            $table->integer('order_column')->default(0);
            $table->json('rules')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['blueprint_entity_id', 'slug']);
        });

        Schema::create('factory_blueprint_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('factory_blueprints')->cascadeOnDelete();
            $table->foreignId('source_entity_id')->nullable()->constrained('factory_blueprint_entities')->nullOnDelete();
            $table->foreignId('target_entity_id')->nullable()->constrained('factory_blueprint_entities')->nullOnDelete();
            $table->string('name');
            $table->string('type', 50)->default('belongsTo');
            $table->string('foreign_key')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_blueprint_relations');
        Schema::dropIfExists('factory_blueprint_fields');
        Schema::dropIfExists('factory_blueprint_entities');
        Schema::dropIfExists('factory_blueprint_versions');
    }
};
