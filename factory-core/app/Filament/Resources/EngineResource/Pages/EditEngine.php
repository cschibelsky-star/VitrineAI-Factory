<?php

namespace App\Filament\Resources\EngineResource\Pages;

use App\Filament\Resources\EngineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngine extends EditRecord
{
    protected static string $resource = EngineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
