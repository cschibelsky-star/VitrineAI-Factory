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
    protected static ?string $navigationLabel = 'Projetos da Factory';
    protected static ?string $modelLabel = 'Projeto';
    protected static ?string $pluralModelLabel = 'Projetos';
    protected static ?string $navigationGroup = 'Factory Enterprise';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('Nome do Projeto')
                    ->required(),
                Forms\Components\TextInput::make('client_name')->label('Cliente'),
                Forms\Components\TextInput::make('product')->label('Produto'),
                Forms\Components\TextInput::make('domain')->label('Domínio'),
                Forms\Components\TextInput::make('github_repository')->label('Repositório GitHub'),
                Forms\Components\TextInput::make('branch')->label('Branch')
                    ->required(),
                Forms\Components\TextInput::make('deploy_path')->label('Caminho no Servidor'),
                Forms\Components\TextInput::make('environment')->label('Ambiente')
                    ->required(),
                Forms\Components\TextInput::make('status')->label('Status')
                    ->required(),
                Forms\Components\Textarea::make('notes')->label('Observações')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('admin_email')->label('E-mail do Administrador')
                    ->email(),
                Forms\Components\TextInput::make('admin_name')->label('Nome do Administrador'),
                Forms\Components\TextInput::make('provisioning_status')->label('Status do Provisionamento')
                    ->required(),
                Forms\Components\Textarea::make('provisioning_log')->label('Log do Provisionamento')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('provisioned_at')->label('Provisionado em'),
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
                Tables\Actions\EditAction::make()->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir selecionados'),
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
