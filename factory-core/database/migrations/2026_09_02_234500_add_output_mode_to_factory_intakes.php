<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('factory_intakes', 'output_mode')) {
            Schema::table('factory_intakes', function (Blueprint $table) {
                $table->string('output_mode')->default('product')->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('factory_intakes', 'output_mode')) {
            Schema::table('factory_intakes', function (Blueprint $table) {
                $table->dropColumn('output_mode');
            });
        }
    }
};
