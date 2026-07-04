<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ExecutarFila extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'Executar Fila';
    protected static ?string $title = 'Executar Fila de Provisionamento';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static string $view = 'filament.pages.executar-fila';
}
