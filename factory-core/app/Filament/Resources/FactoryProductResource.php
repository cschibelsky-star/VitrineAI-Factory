<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\FactoryProductResource\Pages;
use App\Models\FactoryProduct;
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

class FactoryProductResource extends Resource
{
    protected static ?string $model = FactoryProduct::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Factory Core';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state))),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('category')
                ->label('Categoria')
                ->options([
                    'portal' => 'Portal',
                    'tv' => 'TV Digital',
                    'guide' => 'Guia Digital',
                    'institutional' => 'Institucional',
                    'crm' => 'CRM',
                    'erp' => 'ERP',
                    'builder' => 'Builder',
                ]),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Rascunho',
                    'foundation' => 'Fundador',
                    'active' => 'Ativo',
                    'paused' => 'Pausado',
                    'archived' => 'Arquivado',
                ])
                ->default('draft'),

            Forms\Components\TextInput::make('version')
                ->label('Versão')
                ->default('0.1'),

            Forms\Components\TextInput::make('github_repository')
                ->label('Repositório GitHub')
                ->url()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('Descrição')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Produto')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label('Categoria')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('version')->label('Versão'),
                Tables\Columns\TextColumn::make('github_repository')->label('GitHub')->limit(40),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
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
            'index' => Pages\ListFactoryProducts::route('/'),
            'create' => Pages\CreateFactoryProduct::route('/create'),
            'edit' => Pages\EditFactoryProduct::route('/{record}/edit'),
        ];
    }
}
