<?php

namespace App\Filament\Resources\FactoryOpportunitySourceResource\Pages;

use App\Factory\Services\FactoryOpportunitySourceReadinessService;
use App\Filament\Resources\FactoryOpportunitySourceResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFactoryOpportunitySource extends EditRecord
{
    protected static string $resource = FactoryOpportunitySourceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'active') {
            $candidate = $this->record->replicate();
            $candidate->fill($data);

            $readiness = app(FactoryOpportunitySourceReadinessService::class)->evaluate($candidate);

            if (! $readiness['ready']) {
                $data['status'] = $this->record->status === 'active' ? 'inactive' : $this->record->status;

                Notification::make()
                    ->title('Fonte não pode ser ativada')
                    ->body('Faltam evidências mínimas: '.implode(', ', $readiness['blockers']).'.')
                    ->danger()
                    ->persistent()
                    ->send();
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('checkReadiness')
                ->label('Validar prontidão')
                ->icon('heroicon-o-shield-check')
                ->action(function (FactoryOpportunitySourceReadinessService $service): void {
                    $result = $service->evaluate($this->record->fresh());

                    $notification = Notification::make()
                        ->title($result['ready'] ? 'Fonte pronta para ativação' : 'Fonte ainda não está pronta')
                        ->body($result['ready']
                            ? 'Contrato, sincronização e evidências mínimas estão presentes.'
                            : 'Bloqueios: '.implode(', ', $result['blockers']));

                    if ($result['ready']) {
                        $notification->success();
                    } else {
                        $notification->warning();
                    }

                    $notification->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
