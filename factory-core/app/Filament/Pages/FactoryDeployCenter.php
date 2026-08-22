<?php

namespace App\Filament\Pages;

use App\Factory\Services\FactoryPipelineReadinessService;
use App\Models\FactoryRelease;
use Filament\Pages\Page;

class FactoryDeployCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Deploy';
    protected static ?string $title = 'Deploy Center';
    protected static ?int $navigationSort = 90;
    protected static string $view = 'filament.pages.factory-deploy-center';

    protected function getViewData(): array
    {
        $readiness = app(FactoryPipelineReadinessService::class);

        $candidateReleases = FactoryRelease::query()
            ->with(['product.artifacts', 'product.builds', 'product.homologations', 'product.releases', 'build', 'homologation'])
            ->whereIn('status', ['approved', 'ready_to_deploy'])
            ->latest()
            ->get();

        $evaluatedReleases = $candidateReleases->map(function (FactoryRelease $release) use ($readiness) {
            return [
                'release' => $release,
                'readiness' => $readiness->evaluateRelease($release),
            ];
        });

        return [
            'readyReleases' => $evaluatedReleases
                ->filter(fn (array $item) => $item['readiness']['ready'])
                ->values(),
            'blockedReleases' => $evaluatedReleases
                ->reject(fn (array $item) => $item['readiness']['ready'])
                ->values(),
            'recentDeploys' => FactoryRelease::query()
                ->with('product')
                ->where('status', 'deployed')
                ->latest('deployed_at')
                ->limit(10)
                ->get(),
        ];
    }
}
