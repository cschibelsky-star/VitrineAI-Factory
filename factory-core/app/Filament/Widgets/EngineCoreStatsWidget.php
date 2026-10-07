<?php

namespace App\Filament\Widgets;

use App\Models\Engine;
use App\Models\EngineType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EngineCoreStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Tipos de Engine', (string) EngineType::query()->count()),
            Stat::make('Engines', (string) Engine::query()->count()),
            Stat::make('Engines Ativos', (string) Engine::query()->where('status', Engine::STATUS_ACTIVE)->count()),
            Stat::make('Engines Core', (string) Engine::query()->where('is_core', true)->count()),
        ];
    }
}
