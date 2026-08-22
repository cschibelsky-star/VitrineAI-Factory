<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryArtifactResource\Pages;
use App\Models\FactoryArtifact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FactoryArtifactResource extends Resource
{
    protected static ?string $model = FactoryArtifact::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'QA e Documentação';
    protected static ?int $navigationSort = 50;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')->relationship('product', 'name')->label('Projeto')->required(),
            Forms\Components\Select::make('mission_id')->relationship('mission', 'title')->label('Missão'),
            Forms\Components\Select::make('stage')->options([
                'architecture' => 'Arquitetura', 'development' => 'Desenvolvimento', 'qa' => 'QA', 'documentation' => 'Documentação', 'release' => 'Release'
            ])->required(),
            Forms\Components\Select::make('type')->options([
                'test_report' => 'Relatório de testes', 'evidence' => 'Evidência', 'document' => 'Documento', 'checklist' => 'Checklist', 'specification' => 'Especificação'
            ])->required(),
            Forms\Components\Select::make('status')->options([
                'draft' => 'Rascunho', 'review' => 'Em revisão', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado'
            ])->default('draft')->required(),
            Forms\Components\TextInput::make('title')->label('Título')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('version')->label('Versão'),
            Forms\Components\TextInput::make('location')->label('Local / URL')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name')->label('Projeto')->searchable(),
            Tables\Columns\TextColumn::make('stage')->label('Etapa')->badge(),
            Tables\Columns\TextColumn::make('type')->label('Tipo')->badge(),
            Tables\Columns\TextColumn::make('title')->label('Artefato')->searchable(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
            Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryArtifacts::route('/'),
            'create' => Pages\CreateFactoryArtifact::route('/create'),
            'edit' => Pages\EditFactoryArtifact::route('/{record}/edit'),
        ];
    }
}
