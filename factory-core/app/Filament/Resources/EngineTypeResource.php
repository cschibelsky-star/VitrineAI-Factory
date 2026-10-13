<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EngineTypeResource\Pages;
use App\Models\EngineType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EngineTypeResource extends Resource
{
    protected static ?string $model = EngineType::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Vitrine IA Factory';

    protected static ?string $navigationLabel = 'Tipos de Engine';

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

            Forms\Components\Textarea::make('description')
                ->label('Descrição')
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_active')
                ->label('Ativo')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label('Categoria')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
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
            'index' => Pages\ListEngineTypes::route('/'),
            'create' => Pages\CreateEngineType::route('/create'),
            'edit' => Pages\EditEngineType::route('/{record}/edit'),
        ];
    }
}
