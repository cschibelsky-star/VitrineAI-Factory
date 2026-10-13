<?php

namespace Database\Seeders;

use App\Models\FactoryCapability;
use App\Services\FactoryCapabilityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CapabilityEngineSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(FactoryCapabilityService::class);

        $capabilities = [
            ['Login', 'Autenticação e acesso ao sistema'],
            ['Multiempresa', 'Separação operacional por cliente ou organização'],
            ['CMS', 'Gestão de conteúdo'],
            ['RSS', 'Captação e organização de feeds'],
            ['Vídeo', 'Gestão e publicação de vídeos'],
            ['Agenda', 'Eventos e compromissos'],
            ['Documentos', 'Gestão documental'],
            ['IA Editorial', 'Apoio editorial com IA'],
            ['Transparência', 'Publicação institucional e prestação de contas'],
            ['GitHub Integration', 'Integração com repositórios GitHub'],
            ['Builder Laravel', 'Geração de estrutura Laravel'],
        ];

        foreach ($capabilities as [$name, $description]) {
            $capability = FactoryCapability::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'category' => 'core',
                    'type' => 'reusable',
                    'status' => 'active',
                    'version' => '0.1.0',
                    'description' => $description,
                    'capability_dna' => ['foundation' => true],
                ]
            );

            $service->publishVersion($capability, '0.1.0', ['seeded' => true]);
        }
    }
}
