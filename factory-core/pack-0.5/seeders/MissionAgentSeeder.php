<?php

namespace Database\Seeders;

use App\Models\FactoryAgent;
use App\Models\FactoryMission;
use App\Services\FactoryMissionRunner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MissionAgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            ['Builder IA', 'builder', ['gerar projeto', 'criar estrutura', 'builder laravel']],
            ['Architect IA', 'architect', ['blueprint', 'arquitetura', 'modelagem']],
            ['QA IA', 'qa', ['testes', 'validação', 'revisão']],
            ['Deploy IA', 'deploy', ['hostgator', 'deploy', 'publicação']],
            ['GitHub IA', 'github', ['branch', 'commit', 'pull request']],
        ];

        foreach ($agents as [$name, $role, $skills]) {
            FactoryAgent::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'role' => $role, 'status' => 'active', 'skills' => $skills]
            );
        }

        $mission = FactoryMission::updateOrCreate(
            ['code' => 'MISSION-BUILD-001'],
            [
                'name' => 'Construir primeiro projeto pela Factory',
                'slug' => 'construir-primeiro-projeto-pela-factory',
                'type' => 'build',
                'status' => 'pending',
                'priority' => 'high',
                'payload' => ['target' => 'Laravel + Filament'],
            ]
        );

        app(FactoryMissionRunner::class)->createDefaultSteps($mission);
    }
}
