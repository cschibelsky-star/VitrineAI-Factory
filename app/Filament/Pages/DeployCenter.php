<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use App\Models\FactoryProvisioningLog;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DeployCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationLabel = 'Deploy Center';
    protected static ?string $title = 'Deploy Center';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.deploy-center';

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('updated_at')->get();
    }

    public function atualizarProjeto(int $projectId): void
    {
        $project = FactoryProject::findOrFail($projectId);

        FactoryProvisioningLog::create([
            'factory_project_id' => $project->id,
            'step' => 'Atualização',
            'status' => 'info',
            'message' => 'Atualização solicitada no Deploy Center. Execução automática será ativada após validação do fluxo seguro.',
        ]);

        Notification::make()
            ->title('Atualização registrada nos logs')
            ->success()
            ->send();
    }
}
