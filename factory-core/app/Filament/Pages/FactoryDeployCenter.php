<?php

namespace App\Filament\Pages;

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
        return [
            'readyReleases' => FactoryRelease::query()
                ->with(['product', 'build', 'homologation'])
                ->where('status', 'ready_to_deploy')
                ->latest()
                ->get(),
            'recentDeploys' => FactoryRelease::query()
                ->with('product')
                ->where('status', 'deployed')
                ->latest('deployed_at')
                ->limit(10)
                ->get(),
        ];
    }
}
