<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryBlueprintResource\Pages;
use App\Models\FactoryBlueprint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FactoryBlueprintResource extends Resource
{
    protected static ?string $model = FactoryBlueprint::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Vitrine IA Factory';

    protected static ?string $navigationLabel = 'Blueprints';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('category')
                ->label('Categoria'),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Rascunho',
                    'foundation' => 'Fundador',
                    'active' => 'Ativo',
                    'archived' => 'Arquivado',
                ])
                ->default('draft'),

            Forms\Components\TextInput::make('version')
                ->label('Versão')
                ->default('0.1.0'),

            Forms\Components\Select::make('source_product_id')
                ->label('Produto origem')
                ->relationship('sourceProduct', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\Textarea::make('description')
                ->label('Descrição')
                ->columnSpanFull(),

            Forms\Components\KeyValue::make('blueprint_dna')
                ->label('Blueprint DNA')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Blueprint')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label('Categoria')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('version')->label('Versão'),
                Tables\Columns\TextColumn::make('sourceProduct.name')->label('Origem')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlueprints::route('/'),
            'create' => Pages\CreateFactoryBlueprint::route('/create'),
            'edit' => Pages\UpdateBlueprintRecord::route('/{record}/edit'),
        ];
    }
}
