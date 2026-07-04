<?php

namespace App\Filament\Pages;

use App\Factory\Services\ProvisioningService;
use App\Models\FactoryProject;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ExecutarFila extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'Executar Fila';
    protected static ?string $title = 'Executar Fila de Provisionamento';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static string $view = 'filament.pages.executar-fila';

    public function executarPendentes(): void
    {
        $projects = FactoryProject::where('provisioning_status', 'pending')->get();

        foreach ($projects as $project) {
            app(ProvisioningService::class)->run($project);
        }

        Notification::make()
            ->title($projects->count() . ' projeto(s) enviado(s) para provisionamento')
            ->success()
            ->send();
    }
}
