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
            ['QA IA', 'qa', ['testes', 'validacao', 'revisao']],
            ['Deploy IA', 'deploy', ['compose', 'deploy', 'publicacao']],
            ['GitHub IA', 'github', ['branch', 'commit', 'pull request']],
        ];

        foreach ($agents as [$name, $role, $skills]) {
            FactoryAgent::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'role' => $role,
                    'specialty' => $role,
                    'status' => 'active',
                    'skills' => $skills,
                    'agent_dna' => ['foundation' => true],
                    'metadata' => ['seeded' => true],
                ]
            );
        }

        $mission = FactoryMission::updateOrCreate(
            ['title' => 'Construir primeiro projeto pela Factory'],
            [
                'status' => 'planned',
                'priority' => 'high',
                'objective' => 'Validar o pipeline Builder, Blueprint, Capabilities, QA e Deployment.',
                'mission_dna' => [
                    'code' => 'MISSION-BUILD-001',
                    'type' => 'build',
                    'target' => 'Laravel 12 + Filament 4',
                ],
            ]
        );

        app(FactoryMissionRunner::class)->createDefaultSteps($mission);
    }
}
