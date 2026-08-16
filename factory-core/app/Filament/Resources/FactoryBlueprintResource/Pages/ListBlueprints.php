<?php

namespace App\Filament\Resources\FactoryBlueprintResource\Pages;

use App\Filament\Resources\FactoryBlueprintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlueprints extends ListRecords
{
    protected static string $resource = FactoryBlueprintResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
