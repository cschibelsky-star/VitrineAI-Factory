<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryHomologationResource\Pages;
use App\Models\FactoryHomologation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryHomologationResource extends Resource
{
    protected static ?string $model = FactoryHomologation::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Homologação';
    protected static ?int $navigationSort = 70;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')->relationship('product', 'name')->label('Projeto')->required(),
            Forms\Components\Select::make('build_id')->relationship('build', 'id')->label('Build'),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pendente', 'testing' => 'Em teste', 'approved' => 'Aprovado', 'rejected' => 'Reprovado'
            ])->default('pending')->required(),
            Forms\Components\TextInput::make('url')->label('URL HML')->url(),
            Forms\Components\Select::make('health_status')->options([
                'unknown' => 'Não verificado', 'healthy' => 'Saudável', 'degraded' => 'Degradado', 'unhealthy' => 'Indisponível'
            ])->default('unknown'),
            Forms\Components\Textarea::make('acceptance_notes')->label('Notas de validação')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')->label('Projeto')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('build.version')->label('Build'),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
            Tables\Columns\TextColumn::make('health_status')->label('Health')->badge(),
            Tables\Columns\TextColumn::make('url')->label('HML')->limit(45),
            Tables\Columns\TextColumn::make('accepted_at')->label('Aceite')->dateTime('d/m/Y H:i'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryHomologations::route('/'),
            'create' => Pages\CreateFactoryHomologation::route('/create'),
            'edit' => Pages\EditFactoryHomologation::route('/{record}/edit'),
        ];
    }
}
