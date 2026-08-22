<?php

namespace App\Filament\Resources\FactoryArtifactResource\Pages;

use App\Filament\Resources\FactoryArtifactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFactoryArtifact extends EditRecord
{
    protected static string $resource = FactoryArtifactResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
