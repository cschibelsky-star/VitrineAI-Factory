<?php

namespace App\Services;

use App\Models\FactoryProduct;

class FactoryProvisioningEngine
{
    public function plan(FactoryProduct $product): array
    {
        $dna = $product->product_dna ?? [];
        $deployment = app(FactoryDeploymentEngine::class)->plan($product);
        $health = app(FactoryHealthEngine::class)->plan($product);

        $issues = array_values(array_unique(array_merge(
            $deployment['issues'] ?? [],
            $health['issues'] ?? [],
        )));

        return [
            'ready' => $issues === [],
            'product' => $product->name,
            'environment' => $dna['environment'] ?? 'hml',
            'strategy' => $dna['provisioning_strategy'] ?? 'controlled-compose',
            'executor' => 'centro-operacional',
            'confirmation_required' => true,
            'steps' => [
                'workspace_validation',
                'repository_validation',
                'configuration_validation',
                'compose_validation',
                'build',
                'start_services',
                'health_checks',
                'collect_evidence',
                'mark_homologation_ready',
            ],
            'deployment' => $deployment,
            'health' => $health,
            'issues' => $issues,
        ];
    }
}
