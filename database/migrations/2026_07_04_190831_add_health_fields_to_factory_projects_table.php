<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factory_projects', function (Blueprint $table) {
            $table->string('health_status')->default('unknown')->after('provisioned_at');
            $table->timestamp('last_health_check_at')->nullable()->after('health_status');
        });
    }

    public function down(): void
    {
        Schema::table('factory_projects', function (Blueprint $table) {
            $table->dropColumn(['health_status', 'last_health_check_at']);
        });
    }
};
