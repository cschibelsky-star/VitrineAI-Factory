<?php

namespace App\Filament\Resources\FactoryHomologationResource\Pages;

use App\Filament\Resources\FactoryHomologationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryHomologations extends ListRecords
{
    protected static string $resource = FactoryHomologationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
