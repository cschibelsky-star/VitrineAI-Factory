<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\FactoryCapabilityResource\Pages;
use App\Models\FactoryCapability;
use Filament\Forms;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FactoryCapabilityResource extends Resource
{
    protected static ?string $model = FactoryCapability::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string|UnitEnum|null $navigationGroup = 'Vitrine IA Factory';

    protected static ?string $navigationLabel = 'Capabilities';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state))),

            Forms\Components\TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('category')
                ->label('Categoria')
                ->default('core'),

            Forms\Components\TextInput::make('type')
                ->label('Tipo')
                ->default('reusable'),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Rascunho',
                    'active' => 'Ativa',
                    'paused' => 'Pausada',
                    'archived' => 'Arquivada',
                ])
                ->default('active'),

            Forms\Components\TextInput::make('version')
                ->label('Versão')
                ->default('0.1.0'),

            Forms\Components\Textarea::make('description')
                ->label('Descrição')
                ->columnSpanFull(),

            Forms\Components\KeyValue::make('capability_dna')
                ->label('Capability DNA')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Capability')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label('Categoria')->badge()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Tipo')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('version')->label('Versão'),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Rascunho',
                    'active' => 'Ativa',
                    'paused' => 'Pausada',
                    'archived' => 'Arquivada',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCapabilities::route('/'),
            'create' => Pages\CreateCapability::route('/create'),
            'edit' => Pages\UpdateCapability::route('/{record}/edit'),
        ];
    }
}
