<?php

namespace App\Filament\Pages;

use App\Factory\Services\FactoryBrainService;
use Filament\Pages\Page;

class FactoryBrain extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Factory Brain';
    protected static ?string $title = 'Factory Brain Command Center';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 21;
    protected static string $view = 'filament.pages.factory-brain';

    public function getInsightsProperty(): array
    {
        return app(FactoryBrainService::class)->insights();
    }
}
