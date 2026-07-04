<?php

namespace App\Filament\Resources\FactoryProjectResource\Pages;

use App\Filament\Resources\FactoryProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryProject extends EditRecord
{
    protected static string $resource = FactoryProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
