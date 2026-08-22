<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factory_intakes', function (Blueprint $table) {
            $table->string('origin')->default('new_idea')->after('type');
            $table->json('references')->nullable()->after('request');
            $table->json('profile_dna')->nullable()->after('references');
            $table->longText('master_prompt')->nullable()->after('profile_dna');
            $table->json('ai_analysis')->nullable()->after('master_prompt');
            $table->string('analysis_status')->default('pending')->after('ai_analysis');
            $table->timestamp('analyzed_at')->nullable()->after('analysis_status');
        });
    }

    public function down(): void
    {
        Schema::table('factory_intakes', function (Blueprint $table) {
            $table->dropColumn([
                'origin',
                'references',
                'profile_dna',
                'master_prompt',
                'ai_analysis',
                'analysis_status',
                'analyzed_at',
            ]);
        });
    }
};
