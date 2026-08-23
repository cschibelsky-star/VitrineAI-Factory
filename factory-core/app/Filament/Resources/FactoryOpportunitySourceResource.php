<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryOpportunitySourceResource\Pages;
use App\Models\FactoryOpportunitySource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryOpportunitySourceResource extends Resource
{
    protected static ?string $model = FactoryOpportunitySource::class;
    protected static ?string $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Fontes de Oportunidades';
    protected static ?string $modelLabel = 'Fonte de oportunidade';
    protected static ?string $pluralModelLabel = 'Fontes de oportunidades';
    protected static ?int $navigationSort = 72;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Fonte')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nome')->required(),
                    Forms\Components\TextInput::make('slug')->label('Slug')->required(),
                    Forms\Components\TextInput::make('category')->label('Categoria')->required(),
                    Forms\Components\TextInput::make('scope')->label('Escopo')->required(),
                    Forms\Components\Select::make('status')->label('Status')->options([
                        'planned' => 'Planejada',
                        'manual' => 'Manual',
                        'active' => 'Ativa',
                        'paused' => 'Pausada',
                        'error' => 'Erro',
                    ])->required(),
                    Forms\Components\Select::make('connector_type')->label('Tipo de conector')->options([
                        'manual' => 'Manual',
                        'api' => 'API',
                        'rss' => 'RSS',
                        'scraper' => 'Coletor web',
                        'multi_source' => 'Múltiplas fontes',
                    ])->required(),
                    Forms\Components\TextInput::make('base_url')->label('URL base')->url()->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Cobertura e contrato')
                ->schema([
                    Forms\Components\TagsInput::make('supported_profile_types')->label('Perfis atendidos'),
                    Forms\Components\TagsInput::make('supported_opportunity_types')->label('Tipos de oportunidade'),
                    Forms\Components\Textarea::make('mapping_contract')
                        ->label('Contrato de mapeamento')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: ['raw' => $state]) : $state)
                        ->rows(10)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Sincronização')
                ->schema([
                    Forms\Components\DateTimePicker::make('last_sync_at')->label('Última sincronização'),
                    Forms\Components\TextInput::make('last_sync_status')->label('Status da última sincronização'),
                    Forms\Components\Textarea::make('last_sync_evidence')
                        ->label('Evidência')
                        ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?: ['raw' => $state]) : $state)
                        ->rows(8)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Fonte')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('category')->label('Categoria')->badge(),
            Tables\Columns\TextColumn::make('scope')->label('Escopo')->badge(),
            Tables\Columns\TextColumn::make('connector_type')->label('Conector')->badge(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
            Tables\Columns\TextColumn::make('last_sync_at')->label('Última sincronização')->dateTime('d/m/Y H:i')->placeholder('Nunca'),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryOpportunitySources::route('/'),
            'create' => Pages\CreateFactoryOpportunitySource::route('/create'),
            'edit' => Pages\EditFactoryOpportunitySource::route('/{record}/edit'),
        ];
    }
}
