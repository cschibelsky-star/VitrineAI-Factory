<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use Filament\Pages\Page;

class CpanelAssistido extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    protected static ?string $navigationLabel = 'cPanel Assistido';
    protected static ?string $title = 'cPanel Assistido';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = true;
    protected static string $view = 'filament.pages.cpanel-assistido';

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('created_at')->get();
    }
}
