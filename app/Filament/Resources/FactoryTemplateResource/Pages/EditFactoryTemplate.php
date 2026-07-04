<?php

namespace App\Filament\Resources\FactoryTemplateResource\Pages;

use App\Filament\Resources\FactoryTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryTemplate extends EditRecord
{
    protected static string $resource = FactoryTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
