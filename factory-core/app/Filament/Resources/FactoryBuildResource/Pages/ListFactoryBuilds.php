<?php

namespace App\Filament\Resources\FactoryBuildResource\Pages;

use App\Filament\Resources\FactoryBuildResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryBuilds extends ListRecords
{
    protected static string $resource = FactoryBuildResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
