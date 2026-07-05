<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;
use Filament\Pages\Page;

class ProjetoWorkspace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Workspace Projetos';
    protected static ?string $title = 'Workspace do Projeto';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 9;
    protected static string $view = 'filament.pages.projeto-workspace';

    public ?int $projectId = null;

    public function mount(): void
    {
        $this->projectId = request()->integer('project') ?: FactoryProject::latest('updated_at')->value('id');
    }

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('updated_at')->get();
    }

    public function getProjectProperty(): ?FactoryProject
    {
        return $this->projectId ? FactoryProject::find($this->projectId) : null;
    }

    public function getLogsProperty()
    {
        if (! $this->project) {
            return collect();
        }

        return FactoryProvisioningLog::where('factory_project_id', $this->project->id)
            ->latest()
            ->take(12)
            ->get();
    }
}
