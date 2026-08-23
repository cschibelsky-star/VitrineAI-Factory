<?php

namespace App\Filament\Resources\FactoryOpportunityResource\Pages;

use App\Filament\Resources\FactoryOpportunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryOpportunities extends ListRecords
{
    protected static string $resource = FactoryOpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
