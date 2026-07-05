<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use App\Models\FactoryTemplate;
use Filament\Pages\Page;

class OperationsCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-command-line';
    protected static ?string $navigationLabel = 'Operations Center';
    protected static ?string $title = 'Operations Center';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.operations-center';

    public function getStatsProperty(): array
    {
        return [
            'projects' => FactoryProject::count(),
            'completed' => FactoryProject::where('provisioning_status', 'completed')->count(),
            'failed' => FactoryProject::where('provisioning_status', 'failed')->count(),
            'online' => FactoryProject::where('health_status', 'online')->count(),
            'templates' => FactoryTemplate::count(),
        ];
    }

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('updated_at')->limit(12)->get();
    }
}
