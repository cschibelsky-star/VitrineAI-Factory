<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $navigationLabel = 'Leads';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')->label('Nome')->required(),
                    Forms\Components\TextInput::make('email')->label('Email'),
                    Forms\Components\TextInput::make('telefone')->label('Telefone'),
                    Forms\Components\TextInput::make('origem')->label('Origem'),
                    Forms\Components\TextInput::make('status')->label('Status')->required(),
                    Forms\Components\TextInput::make('valor_estimado')->numeric()->label('Valor Estimado')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->label('Nome')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('email')->label('Email')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('telefone')->label('Telefone')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('origem')->label('Origem')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('status')->label('Status')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('valor_estimado')->label('Valor Estimado')->money('BRL')->sortable()
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
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view' => Pages\ViewLead::route('/{record}'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
