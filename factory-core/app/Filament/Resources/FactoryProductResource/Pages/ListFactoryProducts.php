<?php

namespace App\Filament\Resources\FactoryProductResource\Pages;

use App\Filament\Resources\FactoryProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryProducts extends ListRecords
{
    protected static string $resource = FactoryProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
