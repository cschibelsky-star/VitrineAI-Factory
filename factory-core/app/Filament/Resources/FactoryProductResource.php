<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryProductResource\Pages;
use App\Models\FactoryProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FactoryProductResource extends Resource
{
    protected static ?string $model = FactoryProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Projetos';
    protected static ?string $modelLabel = 'Projeto';
    protected static ?string $pluralModelLabel = 'Projetos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nome do projeto')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('category')->label('Tipo de solução')->options([
                'portal' => 'Portal',
                'tv' => 'TV Digital',
                'guide' => 'Guia Digital',
                'institutional' => 'Institucional',
                'crm' => 'CRM',
                'erp' => 'ERP',
                'builder' => 'Builder',
                'real_estate' => 'Imobiliária',
                'government' => 'Governo / Gabinete',
                'other' => 'Outro',
            ]),
            Forms\Components\Select::make('status')->label('Estágio atual')->options([
                'draft' => 'Intake / Rascunho',
                'architecture' => 'Arquitetura',
                'development' => 'Desenvolvimento',
                'qa' => 'QA',
                'documentation' => 'Documentação',
                'build' => 'Build',
                'hml' => 'Homologação',
                'release' => 'Release',
                'deployed' => 'Publicado',
                'paused' => 'Pausado',
                'archived' => 'Arquivado',
            ])->default('draft'),
            Forms\Components\TextInput::make('version')->label('Versão')->default('0.1'),
            Forms\Components\TextInput::make('github_repository')->label('Repositório GitHub')->maxLength(255),
            Forms\Components\Textarea::make('description')->label('Escopo / Descrição')->columnSpanFull(),
            Forms\Components\KeyValue::make('product_dna')->label('DNA técnico')->keyLabel('Campo')->valueLabel('Valor')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Projeto')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->label('Tipo')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Estágio')->badge()->sortable(),
                Tables\Columns\TextColumn::make('version')->label('Versão'),
                Tables\Columns\TextColumn::make('github_repository')->label('GitHub')->limit(40),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
