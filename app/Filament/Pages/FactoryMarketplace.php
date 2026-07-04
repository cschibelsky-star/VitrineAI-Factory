<?php

namespace App\Filament\Pages;

use App\Models\FactoryTemplate;
use Filament\Pages\Page;

class FactoryMarketplace extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Marketplace';
    protected static ?string $title = 'Marketplace de Templates';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.factory-marketplace';

    public function getTemplatesProperty()
    {
        return FactoryTemplate::orderBy('sort_order')->orderBy('name')->get();
    }
}
