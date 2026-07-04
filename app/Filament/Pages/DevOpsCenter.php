<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DevOpsCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'DevOps Center';
    protected static ?string $title = 'Vitrine AI DevOps Center';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static string $view = 'filament.pages.devops-center';

    public array $projects = [];

    public function mount(): void
    {
        $this->projects = config('devops-center.projects', []);
    }
}
