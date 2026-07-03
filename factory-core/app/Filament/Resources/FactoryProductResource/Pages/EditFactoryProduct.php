<?php

namespace App\Filament\Resources\FactoryProductResource\Pages;

use App\Filament\Resources\FactoryProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryProduct extends EditRecord
{
    protected static string $resource = FactoryProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
