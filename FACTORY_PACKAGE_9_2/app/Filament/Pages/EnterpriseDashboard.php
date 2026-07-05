<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use App\Models\FactoryTemplate;
use Filament\Pages\Page;

class EnterpriseDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Enterprise Dashboard';
    protected static ?string $title = 'VitrineAI Factory Enterprise';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.enterprise-dashboard';

    public function getStatsProperty(): array
    {
        return [
            'projects' => FactoryProject::count(),
            'completed' => FactoryProject::where('provisioning_status', 'completed')->count(),
            'running' => FactoryProject::where('provisioning_status', 'running')->count(),
            'failed' => FactoryProject::where('provisioning_status', 'failed')->count(),
            'online' => FactoryProject::where('health_status', 'online')->count(),
            'templates' => FactoryTemplate::count(),
        ];
    }

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('updated_at')->limit(8)->get();
    }
}
