<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factory_projects', function (Blueprint $table) {
            $table->string('admin_email')->nullable()->after('domain');
            $table->string('admin_name')->nullable()->after('admin_email');
            $table->string('provisioning_status')->default('pending')->after('status');
            $table->text('provisioning_log')->nullable()->after('provisioning_status');
            $table->timestamp('provisioned_at')->nullable()->after('provisioning_log');
        });
    }

    public function down(): void
    {
        Schema::table('factory_projects', function (Blueprint $table) {
            $table->dropColumn([
                'admin_email',
                'admin_name',
                'provisioning_status',
                'provisioning_log',
                'provisioned_at',
            ]);
        });
    }
};
