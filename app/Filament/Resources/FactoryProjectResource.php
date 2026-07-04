<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactoryProjectResource\Pages;
use App\Filament\Resources\FactoryProjectResource\RelationManagers;
use App\Models\FactoryProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactoryProjectResource extends Resource
{
    protected static ?string $model = FactoryProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('client_name'),
                Forms\Components\TextInput::make('product'),
                Forms\Components\TextInput::make('domain'),
                Forms\Components\TextInput::make('github_repository'),
                Forms\Components\TextInput::make('branch')
                    ->required(),
                Forms\Components\TextInput::make('deploy_path'),
                Forms\Components\TextInput::make('environment')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('admin_email')
                    ->email(),
                Forms\Components\TextInput::make('admin_name'),
                Forms\Components\TextInput::make('provisioning_status')
                    ->required(),
                Forms\Components\Textarea::make('provisioning_log')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('provisioned_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('github_repository')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deploy_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('environment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('admin_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('admin_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provisioning_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provisioned_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('provisionar')
                    ->label('Provisionar')
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (FactoryProject $record) {
                        $record->update([
                            'status' => 'active',
                            'provisioning_status' => 'provisioned',
                            'provisioning_log' => trim(($record->provisioning_log ?? '') . "\n[" . now() . "] Provisionamento marcado como concluído pela Factory."),
                            'provisioned_at' => now(),
                        ]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFactoryProjects::route('/'),
            'create' => Pages\CreateFactoryProject::route('/create'),
            'edit' => Pages\EditFactoryProject::route('/{record}/edit'),
        ];
    }
}
