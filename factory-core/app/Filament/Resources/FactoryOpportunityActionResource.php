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
                ->schema([
                    Forms\Components\Textarea::make('dependencies')->label('Dependências')->rows(5)->columnSpanFull(),
                    Forms\Components\Textarea::make('required_evidence')->label('Evidências exigidas')->rows(6)->columnSpanFull(),
                    Forms\Components\Textarea::make('completion_evidence')->label('Evidências de conclusão')->rows(6)->columnSpanFull(),
                    Forms\Components\Textarea::make('action_dna')->label('DNA da ação')->rows(6)->columnSpanFull(),
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
