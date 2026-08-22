<?php

namespace App\Filament\Resources\FactoryIntakeResource\Pages;

use App\Filament\Resources\FactoryIntakeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryIntake extends EditRecord
{
    protected static string $resource = FactoryIntakeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
