<?php

namespace Database\Seeders;

use App\Models\FactoryArtifact;
use App\Models\FactoryIntake;
use App\Models\FactoryMission;
use App\Models\FactoryProduct;
use Illuminate\Database\Seeder;

class FactoryPilotProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Projeto Imobiliárias',
                'slug' => 'projeto-imobiliarias',
                'category' => 'real_estate',
                'description' => 'Projeto-piloto da Factory para validar o pipeline ponta a ponta em uma solução digital para imobiliárias.',
                'objective' => 'Definir a arquitetura funcional e técnica mínima do Projeto Imobiliárias e produzir evidências suficientes para avançar para desenvolvimento controlado.',
                'architecture_document' => 'docs/pilotos/PROJETO_IMOBILIARIAS_ARQUITETURA_V0.1.md',
            ],
            [
                'name' => 'Gabinete Online',
                'slug' => 'gabinete-online',
                'category' => 'government',
                'description' => 'Projeto-piloto da Factory para validar o pipeline ponta a ponta em uma solução digital para gabinete.',
                'objective' => 'Definir a arquitetura funcional e técnica mínima do Gabinete Online e produzir evidências suficientes para avançar para desenvolvimento controlado.',
                'architecture_document' => 'docs/pilotos/GABINETE_ONLINE_ARQUITETURA_V0.1.md',
            ],
        ];

        foreach ($projects as $projectData) {
            $project = FactoryProduct::updateOrCreate(
                ['slug' => $projectData['slug']],
                [
                    'name' => $projectData['name'],
                    'category' => $projectData['category'],
                    'status' => 'development',
                    'version' => '0.1',
                    'description' => $projectData['description'],
                    'product_dna' => [
                        'factory_pilot' => true,
                        'pipeline_validation' => true,
                        'infrastructure_defined' => false,
                        'architecture_baseline' => $projectData['architecture_document'],
                        'architecture_approved' => true,
                    ],
                ]
            );

            FactoryIntake::updateOrCreate(
                ['title' => 'Implantar '.$projectData['name'].' na Factory'],
                [
                    'type' => 'new_project',
                    'status' => 'converted',
                    'priority' => 'high',
                    'request' => 'Validar o fluxo completo da Factory desde Intake até Homologação, Release e Deploy controlado.',
                    'product_id' => $project->id,
                    'intake_dna' => [
                        'factory_pilot' => true,
                        'converted_to_project' => true,
                    ],
                ]
            );

            $mission = FactoryMission::updateOrCreate(
                ['title' => 'Arquitetura piloto — '.$projectData['name']],
                [
                    'product_id' => $project->id,
                    'status' => 'planned',
                    'priority' => 'high',
                    'objective' => $projectData['objective'],
                    'mission_dna' => [
                        'factory_pilot' => true,
                        'stage' => 'architecture',
                        'execution_mode' => 'controlled',
                        'ai_provider' => 'roteia',
                        'required_outputs' => [
                            'functional_scope',
                            'technical_architecture',
                            'data_model',
                            'integration_map',
                            'qa_checklist',
                            'documentation_baseline',
                            'build_plan',
                            'hml_acceptance_criteria',
                        ],
                        'gates' => [
                            'architecture_approved_before_development',
                            'qa_and_documentation_before_build',
                            'successful_build_before_hml',
                            'approved_hml_before_release',
                            'approved_release_before_deploy',
                        ],
                    ],
                ]
            );

            FactoryArtifact::updateOrCreate(
                [
                    'product_id' => $project->id,
                    'stage' => 'architecture',
                    'type' => 'specification',
                    'title' => 'Baseline de arquitetura — '.$projectData['name'],
                    'version' => '0.1',
                ],
                [
                    'mission_id' => $mission->id,
                    'status' => 'approved',
                    'location' => $projectData['architecture_document'],
                    'evidence' => [
                        'documented' => true,
                        'versioned' => true,
                        'review_status' => 'approved_for_pilot_development',
                    ],
                    'metadata' => [
                        'factory_pilot' => true,
                        'gate' => 'architecture_approved_before_development',
                    ],
                ]
            );
        }
    }
}
