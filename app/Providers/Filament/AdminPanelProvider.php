<?php

namespace App\Providers\Filament;

use App\Filament\Pages\ItemNormalizadoShow;
use App\Filament\Pages\JurimetriaDashboard;
use App\Filament\Pages\JurimetriaSearch;
use App\Filament\Widgets\AssuntoDistributionChart;
use App\Filament\Widgets\ClasseDistributionChart;
use App\Filament\Widgets\ClassificacaoConfidenceWidget;
use App\Filament\Widgets\GrauDistributionChart;
use App\Filament\Widgets\JurimetriaOverviewStats;
use App\Filament\Widgets\TribunalChartWidget;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Oráculo SearchEngine')
            ->homeUrl(fn () => route('filament.admin.pages.jurimetria.dashboard'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                JurimetriaDashboard::class,
                JurimetriaSearch::class,
                ItemNormalizadoShow::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                JurimetriaOverviewStats::class,
                TribunalChartWidget::class,
                ClasseDistributionChart::class,
                AssuntoDistributionChart::class,
                GrauDistributionChart::class,
                ClassificacaoConfidenceWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([]);
    }
}
