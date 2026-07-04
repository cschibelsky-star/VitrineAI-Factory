<?php

namespace App\Filament\Resources\FactoryTemplateResource\Pages;

use App\Filament\Resources\FactoryTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactoryTemplates extends ListRecords
{
    protected static string $resource = FactoryTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
