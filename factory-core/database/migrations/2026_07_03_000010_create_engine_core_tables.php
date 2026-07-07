<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engine_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('engines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engine_type_id')->nullable()->constrained('engine_types')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 80)->unique();
            $table->string('status', 30)->default('planned');
            $table->string('version', 30)->default('0.1.0');
            $table->text('description')->nullable();
            $table->json('config')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_active']);
            $table->index(['engine_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engines');
        Schema::dropIfExists('engine_types');
    }
};
