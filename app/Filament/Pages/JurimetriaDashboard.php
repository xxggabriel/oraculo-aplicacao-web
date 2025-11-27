<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AssuntoDistributionChart;
use App\Filament\Widgets\ClasseDistributionChart;
use App\Filament\Widgets\ClassificacaoConfidenceWidget;
use App\Filament\Widgets\GrauDistributionChart;
use App\Filament\Widgets\JurimetriaOverviewStats;
use App\Filament\Widgets\TribunalChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class JurimetriaDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $slug = 'jurimetria/dashboard';

    protected static ?string $navigationGroup = 'SearchEngine';

    protected static ?string $title = 'Painel de Jurimetria';

    protected function getHeaderWidgets(): array
    {
        return [
            JurimetriaOverviewStats::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            TribunalChartWidget::class,
            ClasseDistributionChart::class,
            AssuntoDistributionChart::class,
            GrauDistributionChart::class,
            ClassificacaoConfidenceWidget::class,
        ];
    }
}
