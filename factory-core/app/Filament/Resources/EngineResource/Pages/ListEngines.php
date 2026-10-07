<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Filament\Resources\EngineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEngines extends ListRecords
{
    protected static string $resource = EngineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
