<?php

namespace App\Filament\Widgets;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JurimetriaOverviewStats extends StatsOverviewWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $service = app(JurimetriaSearchService::class);
        $aggregations = $service->aggregations(new SearchFilters());

        $total = $aggregations['total'] ?? 0;
        $topTribunal = $aggregations['por_tribunal'][0]['label'] ?? '—';
        $topClasse = $aggregations['por_classe'][0]['label'] ?? '—';

        return [
            Stat::make('Itens normalizados', number_format($total))
                ->description('Índice itens-normalizados')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color(Color::Amber),
            Stat::make('Tribunal mais frequente', $topTribunal)
                ->description('Extraído de metadata.tribunal ou orgao_julgador')
                ->descriptionIcon('heroicon-m-building-library')
                ->color(Color::Indigo),
            Stat::make('Classe processual líder', $topClasse)
                ->description('Campo classe (keyword)')
                ->descriptionIcon('heroicon-m-scale')
                ->color(Color::Purple),
        ];
    }
}
