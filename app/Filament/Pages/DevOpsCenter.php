<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use Filament\Pages\Page;

class DevOpsCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'DevOps Center';
    protected static ?string $title = 'Vitrine AI DevOps Center';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static string $view = 'filament.pages.devops-center';

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('created_at')->limit(20)->get();
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => FactoryProject::count(),
            'completed' => FactoryProject::where('provisioning_status', 'completed')->count(),
            'running' => FactoryProject::where('provisioning_status', 'running')->count(),
            'failed' => FactoryProject::where('provisioning_status', 'failed')->count(),
            'online' => FactoryProject::where('health_status', 'online')->count(),
            'templates' => \App\Models\FactoryTemplate::count(),
        ];
    }
}
