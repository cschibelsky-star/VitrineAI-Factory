<?php

namespace App\Filament\Pages;

use App\Models\FactoryProject;
use Filament\Pages\Page;

class PipelineVisual extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'Pipeline Visual';
    protected static ?string $title = 'Pipeline Visual';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.pipeline-visual';

    public function getProjectsProperty()
    {
        return FactoryProject::with('provisioningLogs')->latest()->limit(10)->get();
    }
}
