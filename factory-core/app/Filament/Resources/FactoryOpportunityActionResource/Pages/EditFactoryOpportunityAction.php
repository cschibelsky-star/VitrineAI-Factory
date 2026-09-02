<?php

namespace App\Filament\Resources\FactoryOpportunityActionResource\Pages;

use App\Factory\Services\FactoryOpportunityActionEngine;
use App\Filament\Resources\FactoryOpportunityActionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFactoryOpportunityAction extends EditRecord
{
    protected static string $resource = FactoryOpportunityActionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'completed') {
            $candidate = $this->record->replicate();
            $candidate->fill($data);
            $check = app(FactoryOpportunityActionEngine::class)->canComplete($candidate);

            if (! $check['ready']) {
                $data['status'] = $this->record->status === 'completed' ? 'completed' : 'in_progress';

                Notification::make()
                    ->title('Conclusão bloqueada')
                    ->body('Faltam evidências: '.implode(', ', $check['missing']).'.')
                    ->danger()
                    ->persistent()
                    ->send();
            } elseif ($this->record->status !== 'completed') {
                $data['completed_at'] = now();
            }
        }

        if (($data['status'] ?? null) !== 'completed' && $this->record->status === 'completed') {
            $data['completed_at'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
