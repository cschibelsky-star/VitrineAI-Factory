<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factory_intakes', function (Blueprint $table) {
            $table->string('output_mode')->default('product')->after('origin');
        });

        Schema::create('factory_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intake_id')->nullable()->constrained('factory_intakes')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('factory_products')->nullOnDelete();
            $table->string('profile_type')->nullable();
            $table->string('opportunity_type');
            $table->string('status')->default('identified');
            $table->string('title');
            $table->string('organization')->nullable();
            $table->string('territory')->nullable();
            $table->string('source')->nullable();
            $table->text('source_url')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->decimal('match_score', 5, 2)->nullable();
            $table->json('match_analysis')->nullable();
            $table->json('requirements')->nullable();
            $table->json('gaps')->nullable();
            $table->json('action_plan')->nullable();
            $table->json('evidence')->nullable();
            $table->json('opportunity_dna')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['profile_type', 'status']);
            $table->index(['opportunity_type', 'deadline_at']);
            $table->index('match_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_opportunities');

        Schema::table('factory_intakes', function (Blueprint $table) {
            $table->dropColumn('output_mode');
        });
    }
};
