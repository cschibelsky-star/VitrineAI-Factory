<?php

namespace App\Filament\Widgets;

use App\Models\FactoryMission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class FactoryLatestMissionsWidget extends TableWidget
{
    protected static ?string $heading = 'Últimas Missões';

    public function table(Table $table): Table
    {
        return $table
            ->query(FactoryMission::query()->latest()->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Missão')->searchable(),
                Tables\Columns\TextColumn::make('priority')->label('Prioridade')->badge(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizada')->dateTime('d/m/Y H:i'),
            ]);
    }
}
