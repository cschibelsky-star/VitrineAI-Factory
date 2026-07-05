<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use App\Models\FactoryTemplate;
use App\Models\FactoryProvisioningLog;
use Filament\Pages\Page;

class EnterpriseWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-command-line';
    protected static ?string $navigationLabel = 'Workspace Enterprise';
    protected static ?string $title = 'Workspace Enterprise';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.enterprise-workspace';

    public function getStatsProperty(): array
    {
        return [
            'projects' => FactoryProject::count(),
            'online' => FactoryProject::where('health_status', 'online')->count(),
            'completed' => FactoryProject::where('provisioning_status', 'completed')->count(),
            'running' => FactoryProject::where('provisioning_status', 'running')->count(),
            'failed' => FactoryProject::where('provisioning_status', 'failed')->count(),
            'templates' => FactoryTemplate::count(),
        ];
    }

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('updated_at')->limit(8)->get();
    }

    public function getLogsProperty()
    {
        return FactoryProvisioningLog::orderByDesc('created_at')->limit(10)->get();
    }
}
