<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factory_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('factory_projects', 'plan')) $table->string('plan')->nullable()->after('product');
            if (! Schema::hasColumn('factory_projects', 'dns_status')) $table->string('dns_status')->default('unknown')->after('domain_configured_at');
            if (! Schema::hasColumn('factory_projects', 'ssl_status')) $table->string('ssl_status')->default('unknown')->after('dns_status');
            if (! Schema::hasColumn('factory_projects', 'deployment_status')) $table->string('deployment_status')->default('idle')->after('ssl_status');
            if (! Schema::hasColumn('factory_projects', 'last_deploy_at')) $table->timestamp('last_deploy_at')->nullable()->after('deployment_status');
            if (! Schema::hasColumn('factory_projects', 'update_available')) $table->boolean('update_available')->default(false)->after('last_deploy_at');
            if (! Schema::hasColumn('factory_projects', 'last_error')) $table->text('last_error')->nullable()->after('update_available');
        });
    }

    public function down(): void
    {
        // Mantido sem drop para evitar perda de configuração em produção.
    }
};
