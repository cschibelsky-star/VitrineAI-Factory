<?php

namespace App\Filament\Pages;

use App\Factory\Services\DeploymentService;
use App\Factory\Services\HealthCheckService;
use App\Models\FactoryProject;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DeployCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';
    protected static ?string $navigationLabel = 'Deploy Center';
    protected static ?string $title = 'Deploy Center';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.deploy-center';

    public function getProjectsProperty()
    {
        return FactoryProject::latest()->get();
    }

    public function atualizar(int $projectId): void
    {
        $project = FactoryProject::findOrFail($projectId);
        $ok = app(DeploymentService::class)->update($project);

        Notification::make()->title($ok ? 'Deploy concluído' : 'Deploy falhou')->{$ok ? 'success' : 'danger'}()->send();
    }

    public function health(int $projectId): void
    {
        $project = FactoryProject::findOrFail($projectId);
        app(HealthCheckService::class)->check($project);
        Notification::make()->title('Health check executado')->success()->send();
    }
}
