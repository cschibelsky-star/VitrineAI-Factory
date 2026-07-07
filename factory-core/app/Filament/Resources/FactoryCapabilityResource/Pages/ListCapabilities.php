<?php

namespace App\Filament\Resources\FactoryCapabilityResource\Pages;

use App\Filament\Resources\FactoryCapabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCapabilities extends ListRecords
{
    protected static string $resource = FactoryCapabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
