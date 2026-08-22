<?php

namespace App\Filament\Resources\FactoryHomologationResource\Pages;

use App\Filament\Resources\FactoryHomologationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryHomologation extends EditRecord
{
    protected static string $resource = FactoryHomologationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
