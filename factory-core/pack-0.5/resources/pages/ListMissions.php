<?php

namespace App\Filament\Resources\FactoryMissionResource\Pages;

use App\Filament\Resources\FactoryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMissions extends ListRecords
{
    protected static string $resource = FactoryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
