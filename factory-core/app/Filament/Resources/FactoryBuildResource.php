<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryBuildResource\Pages;
use App\Models\FactoryBuild;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryBuildResource extends Resource
{
    protected static ?string $model = FactoryBuild::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Builds';
    protected static ?int $navigationSort = 60;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')->relationship('product', 'name')->label('Projeto')->required(),
            Forms\Components\Select::make('mission_id')->relationship('mission', 'title')->label('Missão'),
            Forms\Components\Select::make('environment')->options(['hml' => 'HML', 'production' => 'Produção'])->default('hml')->required(),
            Forms\Components\Select::make('status')->options([
                'planned' => 'Planejado', 'running' => 'Executando', 'success' => 'Sucesso', 'failed' => 'Falhou', 'cancelled' => 'Cancelado'
            ])->default('planned')->required(),
            Forms\Components\TextInput::make('version')->label('Versão'),
            Forms\Components\TextInput::make('image')->label('Imagem Docker'),
            Forms\Components\TextInput::make('commit_sha')->label('Commit SHA'),
            Forms\Components\TextInput::make('log_location')->label('Local dos logs')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')->label('Projeto')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('version')->label('Versão'),
            Tables\Columns\TextColumn::make('environment')->label('Ambiente')->badge(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
            Tables\Columns\TextColumn::make('commit_sha')->label('Commit')->limit(12),
            Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryBuilds::route('/'),
            'create' => Pages\CreateFactoryBuild::route('/create'),
            'edit' => Pages\EditFactoryBuild::route('/{record}/edit'),
        ];
    }
}
