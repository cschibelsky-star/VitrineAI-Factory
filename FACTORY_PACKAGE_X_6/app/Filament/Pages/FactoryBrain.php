<?php

namespace App\Filament\Pages;

use App\Factory\Services\FactoryBrainService;
use App\Models\FactoryProject;
use Filament\Pages\Page;

class FactoryBrain extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Factory Brain';
    protected static ?string $title = 'Factory Brain';
    protected static ?string $navigationGroup = 'Factory Enterprise';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.factory-brain';

    public function getInsightsProperty(): array
    {
        return app(FactoryBrainService::class)->platformInsights();
    }

    public function getProjectsProperty()
    {
        return FactoryProject::orderByDesc('updated_at')->limit(20)->get();
    }

    public function recommendationsFor(FactoryProject $project): array
    {
        return app(FactoryBrainService::class)->recommendations($project);
    }
}
