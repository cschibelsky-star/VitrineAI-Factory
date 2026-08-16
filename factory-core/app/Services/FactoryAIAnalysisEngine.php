<?php

namespace App\Services;

use App\Models\FactoryMission;
use App\Models\FactoryProduct;

class FactoryAIAnalysisEngine
{
    public function analyzeProduct(FactoryProduct $product): array
    {
        $deployment = app(FactoryDeploymentEngine::class)->plan($product);
        $recommendations = [];

        if (! $deployment['ready']) {
            foreach ($deployment['issues'] as $issue) {
                $recommendations[] = 'Resolver: ' . $issue;
            }
        }

        if (blank($product->description)) {
            $recommendations[] = 'Adicionar descrição técnica do produto.';
        }

        if (empty($product->product_dna)) {
            $recommendations[] = 'Definir product_dna com infraestrutura, branch, domínio e estratégia de deploy.';
        }

        return [
            'status' => $deployment['ready'] ? 'ready' : 'attention',
            'product' => $product->name,
            'deployment' => $deployment,
            'recommendations' => array_values(array_unique($recommendations)),
        ];
    }

    public function analyzeMission(FactoryMission $mission): array
    {
        $issues = [];

        if (blank($mission->objective)) {
            $issues[] = 'Objetivo da missão não definido.';
        }

        if (! $mission->product_id) {
            $issues[] = 'Missão sem produto associado.';
        }

        if ($mission->steps()->count() === 0) {
            $issues[] = 'Missão sem etapas cadastradas.';
        }

        return [
            'status' => $issues === [] ? 'ready' : 'attention',
            'mission' => $mission->title,
            'issues' => $issues,
            'next_action' => $issues[0] ?? 'Missão pronta para execução controlada.',
        ];
    }
}
