<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EngineResource\Pages;
use App\Models\Engine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EngineResource extends Resource
{
    protected static ?string $model = Engine::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Vitrine IA Factory';

    protected static ?string $navigationLabel = 'Engines';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('engine_type_id')
                ->label('Tipo')
                ->relationship('engineType', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('code')
                ->label('Código')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(Engine::statuses())
                ->default(Engine::STATUS_PLANNED)
                ->required(),

            Forms\Components\TextInput::make('version')
                ->label('Versão')
                ->default('0.1.0'),

            Forms\Components\Textarea::make('description')
                ->label('Descrição')
                ->columnSpanFull(),

            Forms\Components\KeyValue::make('config')
                ->label('Configuração')
                ->columnSpanFull(),

            Forms\Components\KeyValue::make('metadata')
                ->label('Metadados')
                ->columnSpanFull(),

            Forms\Components\Toggle::make('is_core')
                ->label('Core')
                ->default(false),

            Forms\Components\Toggle::make('is_active')
                ->label('Ativo')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Engine')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Código')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('engineType.name')->label('Tipo')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('version')->label('Versão'),
                Tables\Columns\IconColumn::make('is_core')->label('Core')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(Engine::statuses()),
                Tables\Filters\SelectFilter::make('engine_type_id')->relationship('engineType', 'name')->label('Tipo'),
                Tables\Filters\TernaryFilter::make('is_core')->label('Core'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
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
            'index' => Pages\ListEngines::route('/'),
            'create' => Pages\CreateEngine::route('/create'),
            'edit' => Pages\EditEngine::route('/{record}/edit'),
        ];
    }
}
