<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_intakes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('new_project');
            $table->string('status')->default('new');
            $table->string('priority')->default('normal');
            $table->text('request')->nullable();
            $table->json('intake_dna')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('factory_products')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('factory_artifacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('factory_products')->cascadeOnDelete();
            $table->foreignId('mission_id')->nullable()->constrained('factory_missions')->nullOnDelete();
            $table->string('stage');
            $table->string('type');
            $table->string('status')->default('draft');
            $table->string('title');
            $table->string('version')->nullable();
            $table->text('location')->nullable();
            $table->json('evidence')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('factory_products')->cascadeOnDelete();
            $table->foreignId('mission_id')->nullable()->constrained('factory_missions')->nullOnDelete();
            $table->string('environment')->default('hml');
            $table->string('status')->default('planned');
            $table->string('version')->nullable();
            $table->string('image')->nullable();
            $table->string('commit_sha')->nullable();
            $table->text('log_location')->nullable();
            $table->json('evidence')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_homologations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('factory_products')->cascadeOnDelete();
            $table->foreignId('build_id')->nullable()->constrained('factory_builds')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('url')->nullable();
            $table->string('health_status')->nullable();
            $table->json('checks')->nullable();
            $table->json('evidence')->nullable();
            $table->text('acceptance_notes')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('factory_products')->cascadeOnDelete();
            $table->foreignId('build_id')->nullable()->constrained('factory_builds')->nullOnDelete();
            $table->foreignId('homologation_id')->nullable()->constrained('factory_homologations')->nullOnDelete();
            $table->string('version');
            $table->string('status')->default('draft');
            $table->text('changelog')->nullable();
            $table->json('release_dna')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_releases');
        Schema::dropIfExists('factory_homologations');
        Schema::dropIfExists('factory_builds');
        Schema::dropIfExists('factory_artifacts');
        Schema::dropIfExists('factory_intakes');
    }
};
