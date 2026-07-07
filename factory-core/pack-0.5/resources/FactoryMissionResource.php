<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryMissionResource\Pages;
use App\Models\FactoryMission;
use App\Services\FactoryMissionRunner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FactoryMissionResource extends Resource
{
    protected static ?string $model = FactoryMission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'Vitrine IA Factory';

    protected static ?string $navigationLabel = 'Missões';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nome')->required()->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('code')->label('Código')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('type')->label('Tipo')->default('build'),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pendente',
                'running' => 'Executando',
                'completed' => 'Concluída',
                'failed' => 'Falhou',
            ])->default('pending'),
            Forms\Components\Select::make('priority')->label('Prioridade')->options([
                'low' => 'Baixa',
                'normal' => 'Normal',
                'high' => 'Alta',
                'critical' => 'Crítica',
            ])->default('normal'),
            Forms\Components\KeyValue::make('payload')->label('Payload')->columnSpanFull(),
            Forms\Components\KeyValue::make('result')->label('Resultado')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->label('Código')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Missão')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('type')->label('Tipo')->badge(),
            Tables\Columns\TextColumn::make('priority')->label('Prioridade')->badge(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
            Tables\Columns\TextColumn::make('updated_at')->label('Atualizada')->dateTime('d/m/Y H:i')->sortable(),
        ])->actions([
            Tables\Actions\Action::make('run')->label('Executar')->icon('heroicon-o-play')->requiresConfirmation()
                ->action(fn (FactoryMission $record) => app(FactoryMissionRunner::class)->run($record)),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMissions::route('/'),
            'create' => Pages\CreateMission::route('/create'),
            'edit' => Pages\UpdateMission::route('/{record}/edit'),
        ];
    }
}
