<?php

namespace App\Filament\Resources\FactoryIntakeResource\Pages;

use App\Factory\Services\FactoryAIOrchestrator;
use App\Factory\Services\FactoryOpportunityService;
use App\Filament\Resources\FactoryIntakeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFactoryIntake extends EditRecord
{
    protected static string $resource = FactoryIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('prepareAiAnalysis')
                ->label('Preparar análise com IA')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->visible(fn (): bool => in_array($this->record->analysis_status, ['pending', 'failed'], true))
                ->requiresConfirmation()
                ->modalHeading('Preparar análise da Factory')
                ->modalDescription('A Factory montará o contrato estruturado para análise pelo Roteia, respeitando o modo de saída escolhido.')
                ->action(function (FactoryAIOrchestrator $orchestrator): void {
                    $request = $orchestrator->buildAnalysisRequest($this->record->fresh(['product']));

                    $this->record->forceFill([
                        'analysis_status' => 'analyzing',
                        'intake_dna' => array_merge($this->record->intake_dna ?? [], [
                            'ai_request' => $request,
                            'ai_contract' => $request['contract'],
                            'ai_provider' => 'roteia',
                            'provider_execution_status' => 'awaiting_adapter',
                        ]),
                    ])->save();

                    $this->refreshFormData(['analysis_status', 'intake_dna']);

                    Notification::make()
                        ->title('Contrato de análise preparado')
                        ->body('O Intake está pronto para o adaptador real do Roteia.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('approveAnalysis')
                ->label('Aprovar análise')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (): bool => $this->record->analysis_status === 'ready')
                ->requiresConfirmation()
                ->modalHeading('Aprovar Perfil/DNA e Prompt Mestre?')
                ->modalDescription('A aprovação libera a materialização no pipeline correspondente: produto ou oportunidade.')
                ->action(function (): void {
                    $this->record->forceFill([
                        'analysis_status' => 'approved',
                        'status' => 'approved',
                    ])->save();

                    $this->refreshFormData(['analysis_status', 'status']);

                    Notification::make()
                        ->title('Análise aprovada')
                        ->body('O resultado está autorizado para materialização na Factory.')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('materializeFactory')
                ->label(fn (): string => $this->record->output_mode === 'opportunity' ? 'Criar oportunidades' : 'Iniciar construção')
                ->icon(fn (): string => $this->record->output_mode === 'opportunity' ? 'heroicon-o-magnifying-glass-circle' : 'heroicon-o-rocket-launch')
                ->color('primary')
                ->visible(fn (): bool => $this->record->analysis_status === 'approved' && $this->record->status !== 'converted')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => $this->record->output_mode === 'opportunity' ? 'Materializar oportunidades?' : 'Iniciar construção na Factory?')
                ->modalDescription(fn (): string => $this->record->output_mode === 'opportunity'
                    ? 'A Factory criará as oportunidades aprovadas no Radar, com aderência, requisitos, lacunas, prazos e plano de ação.'
                    : 'A Factory criará ou atualizará Projeto → Blueprint → Capabilities → Missions. Deploy continua sob execução controlada.')
                ->action(function (FactoryAIOrchestrator $orchestrator, FactoryOpportunityService $opportunities): void {
                    $record = $this->record->fresh(['product']);

                    if ($record->output_mode === 'opportunity') {
                        $created = $opportunities->materializeApprovedAnalysis($record);

                        $this->record->refresh();
                        $this->refreshFormData(['status', 'intake_dna']);

                        Notification::make()
                            ->title('Oportunidades criadas')
                            ->body(count($created).' oportunidade(s) materializada(s) no Radar da Factory.')
                            ->success()
                            ->send();

                        return;
                    }

                    $product = $orchestrator->materializeApprovedAnalysis($record);

                    $this->record->refresh();
                    $this->refreshFormData(['product_id', 'status', 'intake_dna']);

                    Notification::make()
                        ->title('Construção iniciada')
                        ->body("Projeto {$product->name} materializado no pipeline da Factory.")
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
