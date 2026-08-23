<?php

namespace App\Factory\Services;

class FactoryProfileSchemaRegistry
{
    public function all(): array
    {
        return [
            'business_digital' => [
                'label' => 'Perfil Digital e de Negócio',
                'purpose' => 'Entender um negócio e transformar sua necessidade em solução digital, sistema, CRM, site, portal ou automação.',
                'fields' => [
                    'brand', 'segment', 'region', 'audience', 'business_model', 'products_services',
                    'current_channels', 'visual_identity', 'communication_tone', 'digital_maturity',
                    'operational_pains', 'desired_outcomes', 'integrations', 'constraints',
                    'preserve', 'improve', 'discard', 'create',
                ],
                'default_solution_modes' => ['software', 'provisioning', 'automation'],
            ],

            'culture' => [
                'label' => 'Perfil Cultural',
                'purpose' => 'Estruturar identidade, trajetória e capacidade de um fazedor de cultura, coletivo ou projeto cultural para portfólio, projetos e oportunidades.',
                'fields' => [
                    'agent_or_collective', 'cultural_area', 'languages', 'territory', 'audience',
                    'trajectory', 'cultural_identity', 'portfolio', 'projects_delivered', 'awards',
                    'partners', 'social_impact', 'objectives', 'available_evidence', 'documentation_gaps',
                    'preserve', 'improve', 'create',
                ],
                'default_solution_modes' => ['opportunity', 'project', 'portfolio'],
            ],

            'public_supplier' => [
                'label' => 'Perfil de Possibilidades de Negócio Público',
                'purpose' => 'Mapear o que uma empresa pode vender ao poder público e quais oportunidades, órgãos e modalidades são aderentes à sua capacidade.',
                'fields' => [
                    'company', 'segment', 'products_services', 'regions', 'delivery_capacity',
                    'technical_capacity', 'certifications', 'cnaes', 'past_performance', 'catalog',
                    'potential_public_buyers', 'contracting_categories', 'recurring_opportunities',
                    'documentation_ready', 'documentation_gaps', 'commercial_differentials', 'risk_flags',
                ],
                'default_solution_modes' => ['opportunity', 'matching', 'procurement'],
            ],

            'nonprofit_funding' => [
                'label' => 'Perfil de Captação e Impacto',
                'purpose' => 'Mapear a capacidade institucional de uma organização do terceiro setor e conectá-la a editais, fundos, emendas, chamamentos e parceiros.',
                'fields' => [
                    'organization', 'causes', 'target_public', 'territory', 'projects_delivered',
                    'technical_capacity', 'operational_capacity', 'budget_size', 'certifications',
                    'registrations', 'partners', 'impact_indicators', 'funding_history', 'available_documents',
                    'documentation_gaps', 'eligible_funding_sources', 'priority_projects',
                ],
                'default_solution_modes' => ['opportunity', 'funding', 'project'],
            ],

            'government_funding' => [
                'label' => 'Perfil de Captação Governamental',
                'purpose' => 'Mapear prioridades e capacidade de um município ou órgão público para buscar recursos, programas, emendas, convênios, fundos e atas aderentes.',
                'fields' => [
                    'government_entity', 'population_context', 'territory', 'departments', 'priority_areas',
                    'mapped_needs', 'existing_projects', 'technical_team', 'counterpart_capacity',
                    'current_agreements', 'amendment_history', 'available_documents', 'documentation_gaps',
                    'eligible_programs', 'eligible_funds', 'eligible_amendments', 'eligible_price_registries',
                    'execution_capacity', 'risk_flags',
                ],
                'default_solution_modes' => ['opportunity', 'funding', 'government_project'],
            ],

            'generic' => [
                'label' => 'Perfil de Contexto Geral',
                'purpose' => 'Estruturar uma necessidade que ainda não se enquadra em uma vertical especializada.',
                'fields' => [
                    'subject', 'context', 'audience', 'territory', 'current_state', 'desired_state',
                    'available_assets', 'constraints', 'opportunities', 'risks', 'preserve', 'improve', 'create',
                ],
                'default_solution_modes' => ['project'],
            ],
        ];
    }

    public function get(string $type): array
    {
        return $this->all()[$type] ?? $this->all()['generic'];
    }

    public function types(): array
    {
        return array_keys($this->all());
    }
}
