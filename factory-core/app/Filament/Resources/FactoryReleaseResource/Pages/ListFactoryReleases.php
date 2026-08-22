<?php

namespace App\Filament\Resources\FactoryReleaseResource\Pages;

use App\Filament\Resources\FactoryReleaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryReleases extends ListRecords
{
    protected static string $resource = FactoryReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
