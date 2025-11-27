<?php

namespace App\Filament\Widgets;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Widgets\ChartWidget;

class AssuntoDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Assuntos mais frequentes';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $aggregations = app(JurimetriaSearchService::class)
            ->aggregations(new SearchFilters());

        $labels = array_column($aggregations['por_assunto'], 'label');
        $values = array_column($aggregations['por_assunto'], 'value');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Assuntos',
                    'data' => $values,
                    'backgroundColor' => [
                        '#0ea5e9',
                        '#f472b6',
                        '#10b981',
                        '#facc15',
                        '#f97316',
                        '#8b5cf6',
                    ],
                ],
            ],
        ];
    }
}
