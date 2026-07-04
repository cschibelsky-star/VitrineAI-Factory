<?php

namespace App\Filament\Resources\FactoryProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProvisioningLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'provisioningLogs';
    protected static ?string $title = 'Logs do Provisionamento';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('step')->label('Etapa')->required(),
            Forms\Components\TextInput::make('status')->label('Status')->required(),
            Forms\Components\Textarea::make('message')->label('Mensagem')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('step')->label('Etapa')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('message')->label('Mensagem')->limit(60),
                Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
