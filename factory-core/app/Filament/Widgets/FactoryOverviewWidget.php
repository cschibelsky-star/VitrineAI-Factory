<?php

namespace App\Filament\Widgets;

use App\Models\Engine;
use App\Models\FactoryAgent;
use App\Models\FactoryBlueprint;
use App\Models\FactoryBuild;
use App\Models\FactoryCapability;
use App\Models\FactoryHomologation;
use App\Models\FactoryIntake;
use App\Models\FactoryMission;
use App\Models\FactoryOpportunity;
use App\Models\FactoryOpportunitySource;
use App\Models\FactoryProduct;
use App\Models\FactoryRelease;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FactoryOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $highMatch = FactoryOpportunity::query()->where('match_score', '>=', 80)->count();
        $activeSources = FactoryOpportunitySource::query()->where('status', 'active')->count();
        $plannedSources = FactoryOpportunitySource::query()->whereIn('status', ['planned', 'manual'])->count();

        return [
            Stat::make('Projetos', (string) FactoryProduct::query()->count())
                ->description('Pipeline de produtos e software'),
            Stat::make('Oportunidades', (string) FactoryOpportunity::query()->count())
                ->description("{$highMatch} com aderência ≥ 80%"),
            Stat::make('Fontes ativas', (string) $activeSources)
                ->description("{$plannedSources} planejadas/manuais"),
            Stat::make('Intakes', (string) FactoryIntake::query()->count())
                ->description('Entradas de produto e oportunidade'),
            Stat::make('Missões', (string) FactoryMission::query()->count()),
            Stat::make('Builds', (string) FactoryBuild::query()->count()),
            Stat::make('HML', (string) FactoryHomologation::query()->count()),
            Stat::make('Releases', (string) FactoryRelease::query()->count()),
            Stat::make('Blueprints', (string) FactoryBlueprint::query()->count()),
            Stat::make('Capabilities', (string) FactoryCapability::query()->count()),
            Stat::make('Engines', (string) Engine::query()->count()),
            Stat::make('Agentes IA', (string) FactoryAgent::query()->count()),
        ];
    }
}
