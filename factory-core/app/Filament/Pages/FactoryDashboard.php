<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FactoryLatestMissionsWidget;
use App\Filament\Widgets\FactoryOverviewWidget;
use Filament\Pages\Dashboard;

class FactoryDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Vitrine IA Factory';
    protected static ?string $navigationLabel = 'Dashboard Factory';
    protected static ?string $title = 'Vitrine IA Factory';

    protected function getHeaderWidgets(): array
    {
        return [
            FactoryOverviewWidget::class,
            FactoryLatestMissionsWidget::class,
        ];
    }
}
