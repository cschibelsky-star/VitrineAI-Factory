<?php

namespace Database\Seeders;

use App\Models\FactoryAgent;
use App\Models\FactoryBlueprint;
use App\Models\FactoryCapability;
use App\Models\FactoryMission;
use App\Models\FactoryProduct;
use App\Models\FactoryRuntimeEngine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FactoryCoreSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['TV Digital Enterprise', 'tv-digital-enterprise', 'tv', 'Base real para TV, notícias, vídeos, RSS, banners e IA editorial.'],
            ['Guia Digital', 'guia-digital', 'guide', 'Base real para cidade, turismo, eventos, atrativos e comércio local.'],
            ['Portal News', 'portal-news', 'portal', 'Base de portal CMS com notícias, RSS, publicidade e IA.'],
            ['Portal Câmara', 'portal-camara', 'institutional', 'Base de portal institucional legislativo com páginas, vereadores, sessões e documentos.'],
        ];

        foreach ($products as [$name, $slug, $category, $description]) {
            FactoryProduct::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'category' => $category,
                    'status' => 'foundation',
                    'version' => '0.1',
                    'description' => $description,
                    'product_dna' => ['foundation_product' => true],
                ]
            );
        }

        $blueprints = [
            ['Portal CMS', 'portal-cms', 'portal', 'Portal News'],
            ['TV Digital', 'tv-digital', 'tv', 'TV Digital Enterprise'],
            ['Guia Digital de Cidade', 'guia-digital-cidade', 'guide', 'Guia Digital'],
            ['Portal Institucional Legislativo', 'portal-institucional-legislativo', 'institutional', 'Portal Câmara'],
        ];

        foreach ($blueprints as [$name, $slug, $category, $sourceProductName]) {
            $sourceProduct = FactoryProduct::where('name', $sourceProductName)->first();

            FactoryBlueprint::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'category' => $category,
                    'status' => 'foundation',
                    'version' => '0.1',
                    'source_product_id' => $sourceProduct?->id,
                    'description' => 'Foundation Blueprint extraído de produto real do ecossistema.',
                    'blueprint_dna' => ['foundation_blueprint' => true],
                ]
            );
        }

        $capabilities = [
            'Login', 'Multiempresa', 'CMS', 'Notícias', 'RSS', 'IA Editorial', 'Vídeos', 'Banners',
            'Upload', 'Dashboard', 'Cidades', 'Eventos', 'Atrativos', 'Categorias', 'Páginas',
            'Documentos', 'Vereadores', 'Sessões', 'Projetos de Lei', 'Transparência', 'Agenda',
            'API', 'ACL', 'GitHub Integration', 'Builder Laravel',
        ];

        foreach ($capabilities as $capability) {
            FactoryCapability::updateOrCreate(
                ['slug' => Str::slug($capability)],
                [
                    'name' => $capability,
                    'category' => 'core',
                    'type' => 'reusable',
                    'status' => 'active',
                    'version' => '0.1',
                    'description' => 'Capability reutilizável do Factory Core.',
                    'capability_dna' => ['reusable' => true],
                ]
            );
        }

        $agents = [
            ['Builder IA', 'builder-ia', 'Geração de estrutura Laravel e Filament'],
            ['Architect IA', 'architect-ia', 'Arquitetura e Blueprints'],
            ['QA IA', 'qa-ia', 'Validação e checklist'],
            ['Research IA', 'research-ia', 'Pesquisa e documentação'],
        ];

        foreach ($agents as [$name, $slug, $specialty]) {
            FactoryAgent::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'specialty' => $specialty,
                    'status' => 'active',
                    'description' => 'Agente inicial da Factory Core 0.1.',
                    'agent_dna' => ['core_agent' => true],
                ]
            );
        }

        $engines = ['Builder Engine', 'Blueprint Engine', 'Capability Engine', 'GitHub Engine', 'Mission Engine'];

        foreach ($engines as $engine) {
            FactoryRuntimeEngine::updateOrCreate(
                ['slug' => Str::slug($engine)],
                [
                    'name' => $engine,
                    'category' => 'core',
                    'status' => 'planned',
                    'version' => '0.1',
                    'description' => 'Engine inicial necessário para a Factory Core 0.1.',
                    'engine_dna' => ['core_engine' => true],
                ]
            );
        }

        FactoryMission::updateOrCreate(
            ['title' => 'Construir Factory Core 0.1'],
            [
                'status' => 'in_progress',
                'priority' => 'high',
                'objective' => 'Criar base funcional mínima da Factory para cadastrar produtos, blueprints, capabilities, agents, engines e missions.',
                'mission_dna' => ['build' => '0.1'],
            ]
        );
    }
}
