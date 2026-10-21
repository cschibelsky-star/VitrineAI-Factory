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

        $steps = $mission->steps()->get();
        if ($steps->isEmpty()) {
            $issues[] = 'Missão sem etapas cadastradas.';
        }

        $executionStatus = (string) $mission->status;
        if (in_array($executionStatus, ['blocked', 'failed', 'cancelled'], true)) {
            $reason = $mission->result['reason'] ?? $executionStatus;
            $issues[] = 'Missão bloqueada ou reprovada: ' . $reason;
        }

        // A completed flag alone is not independent proof of delivery.
        if ($executionStatus === 'completed') {
            if (($mission->result['ok'] ?? false) !== true
                || $steps->isEmpty()
                || $steps->contains(fn ($step) => $step->status !== 'completed'
                    || ($step->result['ok'] ?? false) !== true
                    || ($step->result['qa']['approved'] ?? false) !== true)) {
                $issues[] = 'Missão concluída sem evidência de QA independente para todas as etapas.';
            }
        }

        $verified = $executionStatus === 'completed' && $issues === [];

        return [
            'status' => $issues === [] ? 'ready' : 'attention',
            'execution_status' => $executionStatus,
            'verified' => $verified,
            'mission' => $mission->title,
            'issues' => $issues,
            'next_action' => $issues[0] ?? ($verified
                ? 'Missão concluída com evidências verificadas.'
                : 'Missão pronta para execução controlada.'),
        ];
    }
}
