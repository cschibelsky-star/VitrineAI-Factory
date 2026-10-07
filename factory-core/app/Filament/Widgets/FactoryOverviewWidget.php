<?php

namespace App\Filament\Widgets;

use App\Models\Engine;
use App\Models\FactoryAgent;
use App\Models\FactoryBlueprint;
use App\Models\FactoryCapability;
use App\Models\FactoryMission;
use App\Models\FactoryProduct;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FactoryOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Produtos', (string) FactoryProduct::query()->count()),
            Stat::make('Blueprints', (string) FactoryBlueprint::query()->count()),
            Stat::make('Capabilities', (string) FactoryCapability::query()->count()),
            Stat::make('Engines', (string) Engine::query()->count()),
            Stat::make('Agentes IA', (string) FactoryAgent::query()->count()),
            Stat::make('Missões', (string) FactoryMission::query()->count()),
        ];
    }
}
