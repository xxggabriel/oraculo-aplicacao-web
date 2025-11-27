<?php

namespace App\Filament\Widgets;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Widgets\ChartWidget;

class ClassificacaoConfidenceWidget extends ChartWidget
{
    protected static ?string $heading = 'Confiança da classificação';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $aggregations = app(JurimetriaSearchService::class)
            ->aggregations(new SearchFilters());

        $labels = array_column($aggregations['histograma_confianca'], 'label');
        $values = array_column($aggregations['histograma_confianca'], 'value');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Itens por faixa de confiança',
                    'data' => $values,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14,165,233,0.2)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }
}
