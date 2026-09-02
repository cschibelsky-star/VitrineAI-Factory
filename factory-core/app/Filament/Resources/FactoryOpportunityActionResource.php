<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryOpportunityActionResource\Pages;
use App\Models\FactoryOpportunity;
use App\Models\FactoryOpportunityAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryOpportunityActionResource extends Resource
{
    protected static ?string $model = FactoryOpportunityAction::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Plano de Ação';
    protected static ?string $modelLabel = 'Ação';
    protected static ?string $pluralModelLabel = 'Ações';
    protected static ?int $navigationSort = 57;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Execução')
                ->schema([
                    Forms\Components\Select::make('opportunity_id')
                        ->label('Oportunidade')
                        ->options(fn () => FactoryOpportunity::query()->orderBy('title')->pluck('title', 'id')->all())
                        ->searchable()->preload()->required(),
                    Forms\Components\TextInput::make('title')->label('Ação')->required(),
                    Forms\Components\Select::make('status')->label('Status')->options([
                        'pending' => 'Pendente',
                        'in_progress' => 'Em andamento',
                        'blocked' => 'Bloqueada',
                        'completed' => 'Concluída',
                        'cancelled' => 'Cancelada',
                    ])->required(),
                    Forms\Components\Select::make('priority')->label('Prioridade')->options([
                        'low' => 'Baixa',
                        'normal' => 'Normal',
                        'high' => 'Alta',
                        'critical' => 'Crítica',
                    ])->required(),
                    Forms\Components\Select::make('owner_type')->label('Executor')->options([
                        'human' => 'Humano',
                        'agent' => 'Agente IA',
                        'external' => 'Externo',
                    ])->required(),
                    Forms\Components\TextInput::make('owner')->label('Responsável'),
                    Forms\Components\DateTimePicker::make('due_at')->label('Prazo da ação'),
                    Forms\Components\Textarea::make('description')->label('Descrição')->rows(5)->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Evidências e dependências')
                ->description('Use JSON válido. A conclusão só é liberada quando todas as evidências exigidas estiverem presentes nas evidências de conclusão.')
                ->schema([
                    Forms\Components\Placeholder::make('evidence_readiness')
                        ->label('Prontidão para conclusão')
                        ->content(function (?FactoryOpportunityAction $record): string {
                            if (! $record) {
                                return 'Salve a ação para validar as evidências.';
                            }

                            $check = app(\App\Factory\Services\FactoryOpportunityActionEngine::class)->canComplete($record);

                            return $check['ready']
                                ? 'Pronta para conclusão'
                                : 'Faltam: '.implode(', ', $check['missing']);
                        })
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('dependencies')
                        ->label('Dependências')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: []) : ($state ?? []))
                        ->rows(5)->columnSpanFull(),
                    Forms\Components\Textarea::make('required_evidence')
                        ->label('Evidências exigidas')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: []) : ($state ?? []))
                        ->rows(6)->columnSpanFull(),
                    Forms\Components\Textarea::make('completion_evidence')
                        ->label('Evidências de conclusão')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: []) : ($state ?? []))
                        ->rows(6)->columnSpanFull(),
                    Forms\Components\Textarea::make('action_dna')
                        ->label('DNA da ação')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: []) : ($state ?? []))
                        ->rows(6)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_at')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Ação')->searchable(),
                Tables\Columns\TextColumn::make('opportunity.title')->label('Oportunidade')->limit(40),
                Tables\Columns\TextColumn::make('priority')->label('Prioridade')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('owner')->label('Responsável')->placeholder('—'),
                Tables\Columns\TextColumn::make('due_at')->label('Prazo')->dateTime('d/m/Y H:i')->sortable()->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pendente',
                    'in_progress' => 'Em andamento',
                    'blocked' => 'Bloqueada',
                    'completed' => 'Concluída',
                    'cancelled' => 'Cancelada',
                ]),
                Tables\Filters\SelectFilter::make('priority')->options([
                    'low' => 'Baixa',
                    'normal' => 'Normal',
                    'high' => 'Alta',
                    'critical' => 'Crítica',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryOpportunityActions::route('/'),
            'create' => Pages\CreateFactoryOpportunityAction::route('/create'),
            'edit' => Pages\EditFactoryOpportunityAction::route('/{record}/edit'),
        ];
    }
}
