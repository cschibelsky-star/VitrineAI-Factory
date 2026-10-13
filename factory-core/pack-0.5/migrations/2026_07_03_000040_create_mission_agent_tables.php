<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role', 80)->nullable();
            $table->string('status', 30)->default('active');
            $table->json('skills')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_mission_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('factory_missions')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('factory_agents')->nullOnDelete();
            $table->string('name');
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('order_column')->default(0);
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('factory_mission_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('factory_missions')->cascadeOnDelete();
            $table->foreignId('step_id')->nullable()->constrained('factory_mission_steps')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('factory_agents')->nullOnDelete();
            $table->string('level', 30)->default('info');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_mission_logs');
        Schema::dropIfExists('factory_mission_steps');
        Schema::dropIfExists('factory_agents');
    }
};
