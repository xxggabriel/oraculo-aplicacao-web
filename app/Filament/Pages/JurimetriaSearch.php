<?php

namespace App\Filament\Pages;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class JurimetriaSearch extends Page
{
    use Forms\Concerns\InteractsWithForms;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'SearchEngine';

    protected static ?string $slug = 'jurimetria/busca';

    protected static ?string $title = 'Busca de Itens';

    protected static bool $shouldRegisterNavigation = true;

    protected static string $view = 'filament.pages.jurimetria-search';

    public array $filters = [
        'per_page' => 15,
        'sort_field' => 'data_julgamento',
        'sort_direction' => 'desc',
    ];

    public array $filterOptions = [
        'tribunais' => [],
        'classes' => [],
        'assuntos' => [],
        'classificacoes' => [],
        'modelos' => [],
        'versoes' => [],
        'graus' => [
            1 => '1º grau',
            2 => '2º grau',
        ],
    ];

    public function mount(JurimetriaSearchService $service): void
    {
        try {
            $aggregations = $service->aggregations(new SearchFilters());
            $this->filterOptions['tribunais'] = collect($aggregations['por_tribunal'] ?? [])
                ->pluck('label', 'label')
                ->all();
            $this->filterOptions['classes'] = collect($aggregations['por_classe'] ?? [])
                ->pluck('label', 'label')
                ->all();
            $this->filterOptions['assuntos'] = collect($aggregations['por_assunto'] ?? [])
                ->pluck('label', 'label')
                ->all();
            $this->filterOptions['classificacoes'] = collect($aggregations['por_classificacao'] ?? [])
                ->pluck('label', 'label')
                ->all();
            $this->filterOptions['modelos'] = collect($aggregations['por_modelo'] ?? [])
                ->pluck('label', 'label')
                ->all();
            $this->filterOptions['versoes'] = collect($aggregations['por_versao'] ?? [])
                ->pluck('label', 'label')
                ->all();
        } catch (\Throwable) {
            // Mantém filtros vazios caso o cluster esteja indisponível.
        }

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('q')
                    ->label('Busca textual')
                    ->placeholder('teor, resumo, partes, relator, órgão julgador')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Forms\Components\Select::make('tribunais')
                    ->label('Tribunais')
                    ->multiple()
                    ->reactive()
                    ->options(fn () => $this->filterOptions['tribunais'])
                    ->placeholder('Selecione tribunais')
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Forms\Components\Select::make('classes')
                    ->label('Classes processuais')
                    ->multiple()
                    ->options(fn () => $this->filterOptions['classes'])
                    ->reactive()
                    ->placeholder('Selecione classes')
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Forms\Components\Select::make('assuntos')
                    ->label('Assuntos')
                    ->multiple()
                    ->options(fn () => $this->filterOptions['assuntos'])
                    ->reactive()
                    ->placeholder('Selecione assuntos')
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Forms\Components\Select::make('graus')
                    ->label('Grau')
                    ->multiple()
                    ->options(fn () => $this->filterOptions['graus'])
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Forms\Components\Fieldset::make('Datas')
                    ->schema([
                        Forms\Components\DatePicker::make('data_distribuicao_inicio')
                            ->label('Distribuição inicial')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                        Forms\Components\DatePicker::make('data_distribuicao_fim')
                            ->label('Distribuição final')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                        Forms\Components\DatePicker::make('data_julgamento_inicio')
                            ->label('Julgamento inicial')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                        Forms\Components\DatePicker::make('data_julgamento_fim')
                            ->label('Julgamento final')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make('Classificação')
                    ->schema([
                        Forms\Components\Select::make('classificacoes')
                            ->label('Rótulo')
                            ->options(fn () => $this->filterOptions['classificacoes'])
                            ->multiple()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                        Forms\Components\Select::make('modelos')
                            ->label('Modelo')
                            ->options(fn () => $this->filterOptions['modelos'])
                            ->multiple()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                        Forms\Components\Select::make('versoes')
                            ->label('Versão do modelo')
                            ->options(fn () => $this->filterOptions['versoes'])
                            ->multiple()
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                        Forms\Components\TextInput::make('confianca_min')
                            ->numeric()
                            ->label('Confiança mínima')
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                        Forms\Components\TextInput::make('confianca_max')
                            ->numeric()
                            ->label('Confiança máxima')
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->resetPage()),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Select::make('sort_field')
                    ->label('Ordenação')
                    ->options([
                        'data_julgamento' => 'Data de julgamento',
                        'data_distribuicao' => 'Data de distribuição',
                        'classificacao_confianca' => 'Confiança de classificação',
                    ])
                    ->default('data_julgamento')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Forms\Components\Select::make('sort_direction')
                    ->label('Direção')
                    ->options([
                        'desc' => 'Descendente',
                        'asc' => 'Ascendente',
                    ])
                    ->default('desc')
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetPage()),
                Forms\Components\Select::make('per_page')
                    ->label('Itens por página')
                    ->options([
                        10 => '10',
                        15 => '15',
                        25 => '25',
                        50 => '50',
                    ])
                    ->default(15)
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->resetPage()),
            ])
            ->columns(3)
            ->statePath('filters');
    }

    public function getSearchResultsProperty(): LengthAwarePaginator
    {
        $service = app(JurimetriaSearchService::class);
        $filters = $this->buildFiltersFromState();
        $perPage = (int) ($this->filters['per_page'] ?? 15);

        try {
            return $service->search(
                $filters,
                $this->getPage(),
                $perPage,
                $this->filters['sort_field'] ?? 'data_julgamento',
                $this->filters['sort_direction'] ?? 'desc',
            );
        } catch (\Throwable) {
            return new LengthAwarePaginator(
                collect(),
                total: 0,
                perPage: $perPage,
                currentPage: $this->getPage(),
            );
        }
    }

    public function resetFilters(): void
    {
        $this->filters = [
            'per_page' => 15,
            'sort_field' => 'data_julgamento',
            'sort_direction' => 'desc',
        ];
        $this->form->fill();
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    protected function buildFiltersFromState(): SearchFilters
    {
        return SearchFilters::fromArray([
            'query' => $this->filters['q'] ?? null,
            'tribunais' => $this->filters['tribunais'] ?? [],
            'classes' => $this->filters['classes'] ?? [],
            'assuntos' => $this->filters['assuntos'] ?? [],
            'graus' => $this->filters['graus'] ?? [],
            'classificacoes' => $this->filters['classificacoes'] ?? [],
            'modelos' => $this->filters['modelos'] ?? [],
            'versoes' => $this->filters['versoes'] ?? [],
            'confiancaMinima' => $this->filters['confianca_min'] ?? null,
            'confiancaMaxima' => $this->filters['confianca_max'] ?? null,
            'dataDistribuicaoInicial' => $this->filters['data_distribuicao_inicio'] ?? null,
            'dataDistribuicaoFinal' => $this->filters['data_distribuicao_fim'] ?? null,
            'dataJulgamentoInicial' => $this->filters['data_julgamento_inicio'] ?? null,
            'dataJulgamentoFinal' => $this->filters['data_julgamento_fim'] ?? null,
        ]);
    }
}
