<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_opportunity_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('factory_opportunities')->cascadeOnDelete();
            $table->string('type')->default('task');
            $table->string('status')->default('pending');
            $table->string('priority')->default('normal');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('owner_type')->default('human');
            $table->string('owner')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('required_evidence')->nullable();
            $table->json('completion_evidence')->nullable();
            $table->json('action_dna')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['opportunity_id', 'status']);
            $table->index(['priority', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_opportunity_actions');
    }
};
