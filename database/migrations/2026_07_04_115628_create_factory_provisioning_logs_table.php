<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_provisioning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('step')->nullable();
            $table->string('status')->default('info');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_provisioning_logs');
    }
};
