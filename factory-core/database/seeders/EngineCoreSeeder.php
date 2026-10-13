<?php

namespace Database\Seeders;

use App\Models\Engine;
use App\Models\EngineType;
use Illuminate\Database\Seeder;

class EngineCoreSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Core', 'slug' => 'core', 'category' => 'core'],
            ['name' => 'Generation', 'slug' => 'generation', 'category' => 'generation'],
            ['name' => 'Integration', 'slug' => 'integration', 'category' => 'integration'],
            ['name' => 'Operation', 'slug' => 'operation', 'category' => 'operation'],
        ];

        foreach ($types as $type) {
            EngineType::updateOrCreate(
                ['slug' => $type['slug']],
                [
                    'name' => $type['name'],
                    'category' => $type['category'],
                    'description' => 'Tipo de engine da Vitrine IA Factory.',
                    'is_active' => true,
                ]
            );
        }

        $generationType = EngineType::where('slug', 'generation')->first();
        $coreType = EngineType::where('slug', 'core')->first();
        $integrationType = EngineType::where('slug', 'integration')->first();
        $operationType = EngineType::where('slug', 'operation')->first();

        $engines = [
            ['Builder Engine', 'builder-engine', 'ENG-BUILDER', $generationType?->id],
            ['Blueprint Engine', 'blueprint-engine', 'ENG-BLUEPRINT', $coreType?->id],
            ['Capability Engine', 'capability-engine', 'ENG-CAPABILITY', $coreType?->id],
            ['Mission Engine', 'mission-engine', 'ENG-MISSION', $operationType?->id],
            ['Agent Engine', 'agent-engine', 'ENG-AGENT', $operationType?->id],
            ['GitHub Engine', 'github-engine', 'ENG-GITHUB', $integrationType?->id],
            ['Dashboard Engine', 'dashboard-engine', 'ENG-DASHBOARD', $coreType?->id],
            ['Deployment Engine', 'deployment-engine', 'ENG-DEPLOYMENT', $operationType?->id],
            ['Análise IA Engine', 'analise-ia-engine', 'ENG-ANALISE-IA', $coreType?->id],
        ];

        foreach ($engines as [$name, $slug, $code, $typeId]) {
            Engine::updateOrCreate(
                ['code' => $code],
                [
                    'engine_type_id' => $typeId,
                    'name' => $name,
                    'slug' => $slug,
                    'status' => Engine::STATUS_ACTIVE,
                    'version' => '0.1.0',
                    'description' => 'Engine inicial da Vitrine IA Factory.',
                    'config' => [],
                    'metadata' => ['seeded' => true],
                    'is_core' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
