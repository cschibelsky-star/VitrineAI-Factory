<?php

namespace App\Filament\Resources\FactoryBuildResource\Pages;

use App\Filament\Resources\FactoryBuildResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryBuild extends EditRecord
{
    protected static string $resource = FactoryBuildResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
