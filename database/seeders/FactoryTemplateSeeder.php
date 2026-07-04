<?php

namespace Database\Seeders;

use App\Models\FactoryTemplate;
use Illuminate\Database\Seeder;

class FactoryTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['name' => 'TV Digital Enterprise', 'product_type' => 'Portal/TV', 'base_repository' => 'cschibelsky-star/tvsumare-enterprise', 'default_branch' => 'main'],
            ['name' => 'Portal News Enterprise', 'product_type' => 'Notícias', 'base_repository' => 'cschibelsky-star/portal-news-enterprise', 'default_branch' => 'main'],
            ['name' => 'Social Enterprise', 'product_type' => 'Marketing', 'base_repository' => 'cschibelsky-star/vitrine-ai-social-enterprise', 'default_branch' => 'main'],
            ['name' => 'Guia Digital da Cidade', 'product_type' => 'Turismo/Cidade', 'base_repository' => 'cschibelsky-star/visite-sumare', 'default_branch' => 'main'],
            ['name' => 'AssessorGov IA', 'product_type' => 'GovTech', 'base_repository' => 'cschibelsky-star/assessorgov-ia', 'default_branch' => 'main'],
            ['name' => 'CRM Comercial', 'product_type' => 'CRM', 'base_repository' => 'cschibelsky-star/VitrineAI-Factory', 'default_branch' => 'hostgator-baseline'],
        ];

        foreach ($templates as $template) {
            FactoryTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template + ['status' => 'active']
            );
        }
    }
}
