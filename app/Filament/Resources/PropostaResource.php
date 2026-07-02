<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PropostaResource\Pages;
use App\Models\Proposta;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropostaResource extends Resource
{
    protected static ?string $model = Proposta::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $navigationLabel = 'Propostas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lead_id')->label('Lead Id')->relationship('lead', 'nome')->searchable()->preload(),
                    Forms\Components\TextInput::make('titulo')->label('Titulo')->required(),
                    Forms\Components\TextInput::make('valor')->numeric()->label('Valor'),
                    Forms\Components\TextInput::make('status')->label('Status'),
                    Forms\Components\DatePicker::make('validade')->label('Validade')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lead.nome')->label('Lead')->searchable()->toggleable(),
                    Tables\Columns\TextColumn::make('titulo')->label('Titulo')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('valor')->label('Valor')->money('BRL')->sortable(),
                    Tables\Columns\TextColumn::make('status')->label('Status')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('validade')->label('Validade')->date('d/m/Y')->sortable()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPropostas::route('/'),
            'create' => Pages\CreateProposta::route('/create'),
            'view' => Pages\ViewProposta::route('/{record}'),
            'edit' => Pages\EditProposta::route('/{record}/edit'),
        ];
    }
}
