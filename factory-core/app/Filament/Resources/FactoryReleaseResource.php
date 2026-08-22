<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryReleaseResource\Pages;
use App\Models\FactoryRelease;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryReleaseResource extends Resource
{
    protected static ?string $model = FactoryRelease::class;
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Releases';
    protected static ?int $navigationSort = 80;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')->relationship('product', 'name')->label('Projeto')->required(),
            Forms\Components\Select::make('build_id')->relationship('build', 'id')->label('Build'),
            Forms\Components\Select::make('homologation_id')->relationship('homologation', 'id')->label('Homologação'),
            Forms\Components\TextInput::make('version')->label('Versão')->required(),
            Forms\Components\Select::make('status')->options([
                'draft' => 'Rascunho', 'approved' => 'Aprovada', 'ready_to_deploy' => 'Pronta para deploy', 'deployed' => 'Publicada', 'rolled_back' => 'Revertida'
            ])->default('draft')->required(),
            Forms\Components\Textarea::make('changelog')->label('Changelog')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')->label('Projeto')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('version')->label('Versão')->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
            Tables\Columns\TextColumn::make('approved_at')->label('Aprovada')->dateTime('d/m/Y H:i'),
            Tables\Columns\TextColumn::make('deployed_at')->label('Deploy')->dateTime('d/m/Y H:i'),
            Tables\Columns\TextColumn::make('updated_at')->label('Atualizada')->dateTime('d/m/Y H:i')->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryReleases::route('/'),
            'create' => Pages\CreateFactoryRelease::route('/create'),
            'edit' => Pages\EditFactoryRelease::route('/{record}/edit'),
        ];
    }
}
