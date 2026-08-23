<?php

namespace App\Filament\Resources\FactoryOpportunitySourceResource\Pages;

use App\Filament\Resources\FactoryOpportunitySourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryOpportunitySources extends ListRecords
{
    protected static string $resource = FactoryOpportunitySourceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
