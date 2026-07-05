<?php

namespace App\Filament\Pages;

use App\Factory\Services\BackupService;
use App\Factory\Services\DeploymentService;
use App\Factory\Services\HealthCheckService;
use App\Models\FactoryProject;
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

        $backupOk = app(BackupService::class)->create($project);

        if (! $backupOk) {
            Notification::make()
                ->title('Backup falhou. Atualização cancelada.')
                ->danger()
                ->send();

            return;
        }

        $ok = app(DeploymentService::class)->update($project);

        if ($ok) {
            app(HealthCheckService::class)->check($project);
        }

        Notification::make()
            ->title($ok ? 'Projeto atualizado com sucesso' : 'Falha ao atualizar projeto')
            ->color($ok ? 'success' : 'danger')
            ->send();
    }
}
