<?php

namespace App\Filament\Resources\EngineTypeResource\Pages;

use App\Filament\Resources\EngineTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngineType extends EditRecord
{
    protected static string $resource = EngineTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
