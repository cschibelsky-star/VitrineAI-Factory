<?php

namespace Database\Seeders;

use App\Models\FactoryIntake;
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
            ],
            [
                'name' => 'Gabinete Online',
                'slug' => 'gabinete-online',
                'category' => 'government',
                'description' => 'Projeto-piloto da Factory para validar o pipeline ponta a ponta em uma solução digital para gabinete.',
            ],
        ];

        foreach ($projects as $projectData) {
            $project = FactoryProduct::updateOrCreate(
                ['slug' => $projectData['slug']],
                [
                    'name' => $projectData['name'],
                    'category' => $projectData['category'],
                    'status' => 'architecture',
                    'version' => '0.1',
                    'description' => $projectData['description'],
                    'product_dna' => [
                        'factory_pilot' => true,
                        'pipeline_validation' => true,
                        'infrastructure_defined' => false,
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
        }
    }
}
