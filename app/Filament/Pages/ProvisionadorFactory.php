<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ProvisionadorFactory extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Provisionador';
    protected static ?string $title = 'Provisionador Automático';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static string $view = 'filament.pages.provisionador-factory';
}
