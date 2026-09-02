<?php

namespace App\Filament\Resources\FactoryOpportunityResource\Pages;

use App\Factory\Services\FactoryOpportunityActionEngine;
use App\Factory\Services\FactoryOpportunityMatchingEngine;
use App\Factory\Services\FactoryOpportunityService;
use App\Filament\Resources\FactoryOpportunityResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditFactoryOpportunity extends EditRecord
{
    protected static string $resource = FactoryOpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recalculateMatching')
                ->label('Recalcular Matching')
                ->icon('heroicon-o-scale')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Recalcular aderência da oportunidade?')
                ->modalDescription('A Factory solicitará uma nova avaliação de evidências ao Roteia e o score final será recalculado pelo motor determinístico ponderado por perfil.')
                ->action(function (FactoryOpportunityService $service, FactoryOpportunityMatchingEngine $matching): void {
                    try {
                        $service->recalculateMatching($this->record->fresh(['intake', 'product']), $matching);
                        $this->record->refresh();
                        $this->refreshFormData(['match_score', 'match_analysis', 'gaps', 'action_plan', 'status', 'qualified_at', 'opportunity_dna']);

                        Notification::make()
                            ->title('Matching recalculado')
                            ->body('Aderência: '.$this->record->match_score.'% · Nível: '.data_get($this->record->match_analysis, 'match_level', '—'))
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        report($e);
                        Notification::make()
                            ->title('Falha ao recalcular Matching')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('generateActions')
                ->label('Gerar Plano de Ação')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->visible(fn (): bool => is_array($this->record->match_analysis) && $this->record->match_analysis !== [])
                ->requiresConfirmation()
                ->modalHeading('Gerar ações para esta oportunidade?')
                ->modalDescription('A Factory transformará gaps, requisitos não atendidos, riscos e bloqueios em ações rastreáveis. Conclusões continuarão condicionadas às evidências exigidas.')
                ->action(function (FactoryOpportunityService $service, FactoryOpportunityActionEngine $engine): void {
                    try {
                        $created = $service->generateActions($this->record->fresh(), $engine);
                        $this->record->refresh();

                        Notification::make()
                            ->title('Plano de ação materializado')
                            ->body(count($created).' ação(ões) criada(s) ou atualizada(s) para esta oportunidade.')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        report($e);
                        Notification::make()
                            ->title('Falha ao gerar Plano de Ação')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
