<?php

namespace App\Filament\Resources\FactoryOpportunityActionResource\Pages;

use App\Filament\Resources\FactoryOpportunityActionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryOpportunityActions extends ListRecords
{
    protected static string $resource = FactoryOpportunityActionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
