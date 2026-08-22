<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryIntakeResource\Pages;
use App\Models\FactoryIntake;
use App\Models\FactoryProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryIntakeResource extends Resource
{
    protected static ?string $model = FactoryIntake::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Intake / Solicitações';
    protected static ?string $modelLabel = 'Solicitação';
    protected static ?string $pluralModelLabel = 'Solicitações';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(255),
            Forms\Components\Select::make('type')->label('Tipo')->options([
                'new_project' => 'Novo projeto',
                'evolution' => 'Evolução',
                'correction' => 'Correção',
                'integration' => 'Integração',
            ])->default('new_project')->required(),
            Forms\Components\Select::make('priority')->label('Prioridade')->options([
                'low' => 'Baixa', 'normal' => 'Normal', 'high' => 'Alta', 'critical' => 'Crítica',
            ])->default('normal')->required(),
            Forms\Components\Select::make('status')->label('Status')->options([
                'new' => 'Nova',
                'triage' => 'Em triagem',
                'approved' => 'Aprovada',
                'rejected' => 'Descartada',
                'converted' => 'Convertida em projeto',
            ])->default('new')->required(),
            Forms\Components\Select::make('product_id')
                ->label('Projeto vinculado')
                ->options(fn () => FactoryProduct::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->preload(),
            Forms\Components\Textarea::make('request')->label('Demanda / Objetivo')->rows(8)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Solicitação')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->badge(),
                Tables\Columns\TextColumn::make('priority')->label('Prioridade')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('product.name')->label('Projeto')->placeholder('—'),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryIntakes::route('/'),
            'create' => Pages\CreateFactoryIntake::route('/create'),
            'edit' => Pages\EditFactoryIntake::route('/{record}/edit'),
        ];
    }
}
