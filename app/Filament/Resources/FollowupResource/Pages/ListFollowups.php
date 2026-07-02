<?php

namespace App\Filament\Resources\FollowupResource\Pages;

use App\Filament\Resources\FollowupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFollowups extends ListRecords
{
    protected static string $resource = FollowupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
