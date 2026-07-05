<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use Filament\Pages\Page;

class ProjetosEnterprise extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Projetos Enterprise';
    protected static ?string $title = 'Projetos Enterprise';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.projetos-enterprise';

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('updated_at')->get();
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => FactoryProject::count(),
            'completed' => FactoryProject::where('provisioning_status', 'completed')->count(),
            'failed' => FactoryProject::where('provisioning_status', 'failed')->count(),
            'online' => FactoryProject::where('health_status', 'online')->count(),
        ];
    }
}
