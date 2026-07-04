<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factory_projects', function (Blueprint $table) {
            $table->string('cpanel_status')->default('pending')->after('last_health_check_at');
            $table->string('document_root')->nullable()->after('cpanel_status');
            $table->timestamp('domain_configured_at')->nullable()->after('document_root');
        });
    }

    public function down(): void
    {
        Schema::table('factory_projects', function (Blueprint $table) {
            $table->dropColumn(['cpanel_status', 'document_root', 'domain_configured_at']);
        });
    }
};
