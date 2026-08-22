<?php

namespace App\Services;

use App\Models\FactoryProduct;

class FactoryHealthEngine
{
    public function plan(FactoryProduct $product): array
    {
        $dna = $product->product_dna ?? [];
        $checks = [];
        $issues = [];

        $repository = $product->github_repository ?: ($dna['repository'] ?? null);
        $branch = $dna['branch'] ?? 'main';
        $domain = $dna['domain'] ?? null;
        $composeFile = $dna['compose_file'] ?? null;
        $healthUrl = $dna['health_url'] ?? null;

        $checks[] = ['name' => 'repository', 'target' => $repository, 'required' => true];
        $checks[] = ['name' => 'branch', 'target' => $branch, 'required' => true];
        $checks[] = ['name' => 'compose', 'target' => $composeFile, 'required' => true];
        $checks[] = ['name' => 'domain', 'target' => $domain, 'required' => false];
        $checks[] = ['name' => 'health_url', 'target' => $healthUrl, 'required' => false];

        foreach ($checks as $check) {
            if ($check['required'] && blank($check['target'])) {
                $issues[] = 'Health check obrigatório sem alvo: ' . $check['name'] . '.';
            }
        }

        return [
            'ready' => $issues === [],
            'product' => $product->name,
            'checks' => $checks,
            'issues' => $issues,
            'execution' => [
                'mode' => 'external-controlled',
                'executor' => 'centro-operacional',
                'evidence_required' => true,
            ],
        ];
    }
}
