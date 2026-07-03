<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FactoryCorePackSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [
            'Database\\Seeders\\FactoryCoreSeeder',
            'Database\\Seeders\\EngineCoreSeeder',
            'Database\\Seeders\\BlueprintEngineSeeder',
            'Database\\Seeders\\CapabilityEngineSeeder',
        ];

        foreach ($seeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}
