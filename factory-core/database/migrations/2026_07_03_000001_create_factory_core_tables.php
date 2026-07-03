<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('status')->default('draft');
            $table->string('version')->default('0.1');
            $table->string('github_repository')->nullable();
            $table->text('description')->nullable();
            $table->json('product_dna')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_blueprints', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->string('version')->default('0.1');
            $table->foreignId('source_product_id')->nullable()->constrained('factory_products')->nullOnDelete();
            $table->text('description')->nullable();
            $table->json('blueprint_dna')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('type')->default('core');
            $table->string('status')->default('active');
            $table->string('version')->default('0.1');
            $table->text('description')->nullable();
            $table->json('capability_dna')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('specialty')->nullable();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->json('agent_dna')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_engines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('status')->default('planned');
            $table->string('version')->default('0.1');
            $table->text('description')->nullable();
            $table->json('engine_dna')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('status')->default('planned');
            $table->string('priority')->default('normal');
            $table->foreignId('product_id')->nullable()->constrained('factory_products')->nullOnDelete();
            $table->foreignId('blueprint_id')->nullable()->constrained('factory_blueprints')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('factory_agents')->nullOnDelete();
            $table->string('github_issue_url')->nullable();
            $table->text('objective')->nullable();
            $table->json('mission_dna')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_blueprint_capability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('factory_blueprints')->cascadeOnDelete();
            $table->foreignId('capability_id')->constrained('factory_capabilities')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['blueprint_id', 'capability_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_blueprint_capability');
        Schema::dropIfExists('factory_missions');
        Schema::dropIfExists('factory_engines');
        Schema::dropIfExists('factory_agents');
        Schema::dropIfExists('factory_capabilities');
        Schema::dropIfExists('factory_blueprints');
        Schema::dropIfExists('factory_products');
    }
};
