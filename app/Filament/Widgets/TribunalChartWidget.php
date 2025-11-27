<?php

namespace App\Filament\Widgets;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Widgets\ChartWidget;

class TribunalChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribuição por tribunal';

    protected static ?string $pollingInterval = '120s';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $aggregations = app(JurimetriaSearchService::class)
            ->aggregations(new SearchFilters());

        $labels = array_column($aggregations['por_tribunal'], 'label');
        $values = array_column($aggregations['por_tribunal'], 'value');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Itens',
                    'data' => $values,
                    'backgroundColor' => '#f59e0b',
                ],
            ],
        ];
    }
}
