<?php

namespace Database\Seeders;

use App\Models\FactoryTemplate;
use Illuminate\Database\Seeder;

class EnhancedFactoryTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['name' => 'TV Digital Enterprise', 'product_type' => 'Portal/TV', 'category' => 'Mídia', 'version' => '2.1.0', 'base_repository' => 'cschibelsky-star/tvsumare-enterprise', 'default_branch' => 'main', 'database_driver' => 'sqlite', 'compatible_plans' => ['Start','Enterprise','Governo'], 'icon' => 'tv'],
            ['name' => 'Portal News Enterprise', 'product_type' => 'Notícias', 'category' => 'Mídia', 'version' => '1.0.0', 'base_repository' => 'cschibelsky-star/portal-news-enterprise', 'default_branch' => 'main', 'database_driver' => 'sqlite', 'compatible_plans' => ['Start','Enterprise'], 'icon' => 'newspaper'],
            ['name' => 'Social Enterprise', 'product_type' => 'Marketing', 'category' => 'Marketing IA', 'version' => '1.0.0', 'base_repository' => 'cschibelsky-star/vitrine-ai-social-enterprise', 'default_branch' => 'main', 'database_driver' => 'sqlite', 'compatible_plans' => ['Pro','Enterprise'], 'icon' => 'megaphone'],
            ['name' => 'Guia Digital da Cidade', 'product_type' => 'Turismo/Cidade', 'category' => 'Cidade Inteligente', 'version' => '1.0.0', 'base_repository' => 'cschibelsky-star/visite-sumare', 'default_branch' => 'main', 'database_driver' => 'sqlite', 'compatible_plans' => ['Cidade','Governo'], 'icon' => 'map'],
            ['name' => 'AssessorGov IA', 'product_type' => 'GovTech', 'category' => 'Governo', 'version' => '1.0.0', 'base_repository' => 'cschibelsky-star/assessorgov-ia', 'default_branch' => 'main', 'database_driver' => 'sqlite', 'compatible_plans' => ['Governo'], 'icon' => 'building-office'],
            ['name' => 'CRM Comercial', 'product_type' => 'CRM', 'category' => 'Operação', 'version' => '1.0.0', 'base_repository' => 'cschibelsky-star/VitrineAI-Factory', 'default_branch' => 'hostgator-baseline', 'database_driver' => 'sqlite', 'compatible_plans' => ['Interno','Enterprise'], 'icon' => 'briefcase'],
        ];

        foreach ($templates as $index => $template) {
            FactoryTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template + [
                    'status' => 'active',
                    'sort_order' => $index + 1,
                    'env_defaults' => ['CACHE_STORE' => 'file', 'SESSION_DRIVER' => 'file', 'QUEUE_CONNECTION' => 'sync'],
                    'dependencies' => ['laravel' => true, 'filament' => true],
                ]
            );
        }
    }
}
