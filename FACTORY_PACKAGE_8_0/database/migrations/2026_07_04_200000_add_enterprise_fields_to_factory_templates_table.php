<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factory_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('factory_templates', 'version')) $table->string('version')->default('1.0.0')->after('product_type');
            if (! Schema::hasColumn('factory_templates', 'category')) $table->string('category')->nullable()->after('version');
            if (! Schema::hasColumn('factory_templates', 'database_driver')) $table->string('database_driver')->default('sqlite')->after('default_branch');
            if (! Schema::hasColumn('factory_templates', 'seeders')) $table->text('seeders')->nullable()->after('install_commands');
            if (! Schema::hasColumn('factory_templates', 'post_install_commands')) $table->text('post_install_commands')->nullable()->after('seeders');
            if (! Schema::hasColumn('factory_templates', 'env_defaults')) $table->json('env_defaults')->nullable()->after('post_install_commands');
            if (! Schema::hasColumn('factory_templates', 'dependencies')) $table->json('dependencies')->nullable()->after('env_defaults');
            if (! Schema::hasColumn('factory_templates', 'compatible_plans')) $table->json('compatible_plans')->nullable()->after('dependencies');
            if (! Schema::hasColumn('factory_templates', 'icon')) $table->string('icon')->nullable()->after('compatible_plans');
            if (! Schema::hasColumn('factory_templates', 'sort_order')) $table->integer('sort_order')->default(0)->after('icon');
        });
    }

    public function down(): void
    {
        // Mantido sem drop para evitar perda de configuração em produção.
    }
};
