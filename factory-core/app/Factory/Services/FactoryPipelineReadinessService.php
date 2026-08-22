<?php

namespace App\Factory\Services;

use App\Models\FactoryProduct;
use App\Models\FactoryRelease;

class FactoryPipelineReadinessService
{
    public function evaluateProduct(FactoryProduct $product): array
    {
        $product->loadMissing(['artifacts', 'builds', 'homologations', 'releases']);

        $qaApproved = $product->artifacts
            ->where('stage', 'qa')
            ->where('status', 'approved')
            ->isNotEmpty();

        $documentationApproved = $product->artifacts
            ->where('stage', 'documentation')
            ->where('status', 'approved')
            ->isNotEmpty();

        $successfulBuild = $product->builds
            ->where('status', 'success')
            ->sortByDesc('finished_at')
            ->first();

        $approvedHomologation = $product->homologations
            ->where('status', 'approved')
            ->where('health_status', 'healthy')
            ->sortByDesc('accepted_at')
            ->first();

        $approvedRelease = $product->releases
            ->whereIn('status', ['approved', 'ready_to_deploy', 'deployed'])
            ->sortByDesc('approved_at')
            ->first();

        $gates = [
            'qa' => [
                'ready' => $qaApproved,
                'label' => 'QA aprovado',
            ],
            'documentation' => [
                'ready' => $documentationApproved,
                'label' => 'Documentação aprovada',
            ],
            'build' => [
                'ready' => (bool) $successfulBuild,
                'label' => 'Build bem-sucedido',
                'evidence' => $successfulBuild?->evidence,
            ],
            'homologation' => [
                'ready' => (bool) $approvedHomologation,
                'label' => 'HML aprovada e saudável',
                'evidence' => $approvedHomologation?->evidence,
            ],
            'release' => [
                'ready' => (bool) $approvedRelease,
                'label' => 'Release aprovada',
                'evidence' => $approvedRelease?->release_dna,
            ],
        ];

        $blockers = collect($gates)
            ->filter(fn (array $gate) => ! $gate['ready'])
            ->map(fn (array $gate) => $gate['label'])
            ->values()
            ->all();

        return [
            'ready' => $blockers === [],
            'product_id' => $product->id,
            'product' => $product->name,
            'gates' => $gates,
            'blockers' => $blockers,
            'evidence' => [
                'build_id' => $successfulBuild?->id,
                'homologation_id' => $approvedHomologation?->id,
                'release_id' => $approvedRelease?->id,
            ],
        ];
    }

    public function evaluateRelease(FactoryRelease $release): array
    {
        $release->loadMissing(['product.artifacts', 'product.builds', 'product.homologations', 'product.releases', 'build', 'homologation']);

        $productReadiness = $this->evaluateProduct($release->product);

        $releaseStatusReady = in_array($release->status, ['ready_to_deploy', 'deployed'], true);
        $buildReady = $release->build?->status === 'success';
        $homologationReady = $release->homologation?->status === 'approved'
            && $release->homologation?->health_status === 'healthy';

        $blockers = $productReadiness['blockers'];

        if (! $releaseStatusReady) {
            $blockers[] = 'Release ainda não está pronta para deploy';
        }

        if (! $buildReady) {
            $blockers[] = 'Build vinculado à release não está com status success';
        }

        if (! $homologationReady) {
            $blockers[] = 'HML vinculada à release não está aprovada e healthy';
        }

        $blockers = array_values(array_unique($blockers));

        return [
            ...$productReadiness,
            'release_id' => $release->id,
            'release_version' => $release->version,
            'ready' => $blockers === [],
            'blockers' => $blockers,
        ];
    }
}
