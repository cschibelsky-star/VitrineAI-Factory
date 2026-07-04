<?php

namespace App\Filament\Resources\FactoryProjectResource\Pages;

use App\Filament\Resources\FactoryProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryProjects extends ListRecords
{
    protected static string $resource = FactoryProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
