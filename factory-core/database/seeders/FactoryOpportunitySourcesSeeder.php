<?php

namespace Database\Seeders;

use App\Models\FactoryOpportunitySource;
use Illuminate\Database\Seeder;

class FactoryOpportunitySourcesSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Portal Nacional de Contratações Públicas (PNCP)',
                'slug' => 'pncp',
                'category' => 'public_procurement',
                'scope' => 'national',
                'status' => 'planned',
                'connector_type' => 'api',
                'supported_profile_types' => ['public_supplier', 'government_funding'],
                'supported_opportunity_types' => ['procurement', 'price_registry'],
            ],
            [
                'name' => 'Transferegov.br',
                'slug' => 'transferegov',
                'category' => 'government_funding',
                'scope' => 'national',
                'status' => 'planned',
                'connector_type' => 'api',
                'supported_profile_types' => ['government_funding', 'nonprofit_funding'],
                'supported_opportunity_types' => ['program', 'agreement', 'funding', 'public_call'],
            ],
            [
                'name' => 'Emendas Parlamentares',
                'slug' => 'parliamentary-amendments',
                'category' => 'government_funding',
                'scope' => 'national',
                'status' => 'planned',
                'connector_type' => 'multi_source',
                'supported_profile_types' => ['government_funding', 'nonprofit_funding'],
                'supported_opportunity_types' => ['amendment'],
            ],
            [
                'name' => 'Editais e Chamamentos do Terceiro Setor',
                'slug' => 'nonprofit-calls',
                'category' => 'nonprofit_funding',
                'scope' => 'multi_source',
                'status' => 'manual',
                'connector_type' => 'manual',
                'supported_profile_types' => ['nonprofit_funding'],
                'supported_opportunity_types' => ['public_call', 'private_notice', 'funding'],
            ],
            [
                'name' => 'Editais e Oportunidades Culturais',
                'slug' => 'culture-opportunities',
                'category' => 'culture',
                'scope' => 'multi_source',
                'status' => 'manual',
                'connector_type' => 'manual',
                'supported_profile_types' => ['culture'],
                'supported_opportunity_types' => ['cultural_notice', 'award', 'sponsorship', 'public_call'],
            ],
            [
                'name' => 'Programas e Oportunidades Estaduais',
                'slug' => 'state-programs',
                'category' => 'government_funding',
                'scope' => 'state',
                'status' => 'manual',
                'connector_type' => 'manual',
                'supported_profile_types' => ['government_funding', 'nonprofit_funding', 'culture'],
                'supported_opportunity_types' => ['program', 'funding', 'public_call', 'cultural_notice'],
            ],
        ];

        foreach ($sources as $source) {
            FactoryOpportunitySource::updateOrCreate(
                ['slug' => $source['slug']],
                $source + [
                    'mapping_contract' => [
                        'version' => '1',
                        'status' => 'awaiting_source_specific_mapping',
                    ],
                    'source_dna' => [
                        'official_or_curated_source' => true,
                        'evidence_required' => true,
                        'do_not_treat_planned_as_connected' => true,
                    ],
                ],
            );
        }
    }
}
