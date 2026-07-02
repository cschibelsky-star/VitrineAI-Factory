<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FollowupResource\Pages;
use App\Models\Followup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FollowupResource extends Resource
{
    protected static ?string $model = Followup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $navigationLabel = 'Follow-ups';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lead_id')->label('Lead Id')->relationship('lead', 'nome')->searchable()->preload(),
                    Forms\Components\DatePicker::make('data')->label('Data'),
                    Forms\Components\TextInput::make('canal')->label('Canal'),
                    Forms\Components\Textarea::make('observacao')->label('Observacao')->columnSpanFull(),
                    Forms\Components\TextInput::make('status')->label('Status')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lead.nome')->label('Lead')->searchable()->toggleable(),
                    Tables\Columns\TextColumn::make('data')->label('Data')->date('d/m/Y')->sortable(),
                    Tables\Columns\TextColumn::make('canal')->label('Canal')->searchable()->sortable()->toggleable(),
                    Tables\Columns\TextColumn::make('observacao')->label('Observacao')->limit(40)->toggleable(),
                    Tables\Columns\TextColumn::make('status')->label('Status')->searchable()->sortable()->toggleable()
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
            'index' => Pages\ListFollowups::route('/'),
            'create' => Pages\CreateFollowup::route('/create'),
            'view' => Pages\ViewFollowup::route('/{record}'),
            'edit' => Pages\EditFollowup::route('/{record}/edit'),
        ];
    }
}
