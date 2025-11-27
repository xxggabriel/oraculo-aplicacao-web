<?php

namespace App\Filament\Widgets;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Widgets\ChartWidget;

class GrauDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Distribuição por grau';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $aggregations = app(JurimetriaSearchService::class)
            ->aggregations(new SearchFilters());

        $labels = array_column($aggregations['por_grau'], 'label');
        $values = array_column($aggregations['por_grau'], 'value');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Itens por grau',
                    'data' => $values,
                    'backgroundColor' => '#22c55e',
                ],
            ],
        ];
    }
}
