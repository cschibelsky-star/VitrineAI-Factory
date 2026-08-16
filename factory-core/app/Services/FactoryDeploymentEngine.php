<?php

namespace App\Services;

use App\Models\FactoryProduct;

class FactoryDeploymentEngine
{
    public function plan(FactoryProduct $product): array
    {
        $github = app(FactoryGitHubEngine::class)->buildContext($product);
        $dna = $product->product_dna ?? [];
        $issues = $github['issues'];

        $deployPath = $dna['deploy_path'] ?? null;
        $domain = $dna['domain'] ?? null;
        $composeFile = $dna['compose_file'] ?? 'docker-compose.yml';

        if (! $deployPath) {
            $issues[] = 'Caminho de deploy não definido no product_dna.';
        }

        return [
            'ready' => $issues === [],
            'product' => $product->name,
            'repository' => $github['repository_slug'],
            'branch' => $github['branch'],
            'deploy_path' => $deployPath,
            'domain' => $domain,
            'compose_file' => $composeFile,
            'strategy' => $dna['deploy_strategy'] ?? 'controlled-compose',
            'issues' => array_values(array_unique($issues)),
            'steps' => [
                'repository_status',
                'workspace_validation',
                'compose_status',
                'build_or_pull',
                'migrations_check',
                'service_restart',
                'health_check',
            ],
        ];
    }
}
