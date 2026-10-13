<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;
use App\Filament\Widgets\FactoryLatestMissionsWidget;
use App\Filament\Widgets\FactoryOverviewWidget;
use Filament\Pages\Dashboard;

class FactoryDashboard extends Dashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string|UnitEnum|null $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Dashboard Factory';
    protected static ?string $title = 'Vitrine IA Factory';

    public function getWidgets(): array
    {
        return [
            FactoryOverviewWidget::class,
            FactoryLatestMissionsWidget::class,
        ];
    }
}
