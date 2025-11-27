<?php

namespace App\Filament\Widgets;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Widgets\ChartWidget;

class ClasseDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Classes processuais';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $aggregations = app(JurimetriaSearchService::class)
            ->aggregations(new SearchFilters());

        $labels = array_column($aggregations['por_classe'], 'label');
        $values = array_column($aggregations['por_classe'], 'value');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Classes',
                    'data' => $values,
                    'backgroundColor' => [
                        '#a78bfa',
                        '#22c55e',
                        '#f97316',
                        '#3b82f6',
                        '#ef4444',
                        '#14b8a6',
                    ],
                ],
            ],
        ];
    }
}
