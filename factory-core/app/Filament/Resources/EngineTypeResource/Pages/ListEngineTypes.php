<?php

namespace App\Filament\Resources\EngineTypeResource\Pages;

use App\Filament\Resources\EngineTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEngineTypes extends ListRecords
{
    protected static string $resource = EngineTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
