<?php

namespace App\Domain\Jurimetria\Services;

use App\Domain\Jurimetria\Dto\NormalizedItem;
use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Infrastructure\Search\ElasticsearchClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class JurimetriaSearchService
{
    public function __construct(private readonly ElasticsearchClient $client)
    {
    }

    public function ping(): bool
    {
        return $this->client->ping();
    }

    public function find(string $id): ?NormalizedItem
    {
        try {
            $response = $this->client->search([
                'size' => 1,
                'query' => [
                    'term' => [
                        'id' => $id,
                    ],
                ],
            ]);
        } catch (\Throwable) {
            return null;
        }

        $hit = $response['hits']['hits'][0] ?? null;

        return $hit ? NormalizedItem::fromHit($hit) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function aggregations(SearchFilters $filters): array
    {
        try {
            $response = $this->client->search([
                'size' => 0,
                'track_total_hits' => true,
                'query' => $this->buildQuery($filters),
                'runtime_mappings' => $this->runtimeMappings(),
                'aggs' => $this->aggregationDefinitions(),
            ]);
        } catch (\Throwable) {
            return [
                'total' => 0,
                'por_tribunal' => [],
                'por_classe' => [],
                'por_assunto' => [],
                'por_grau' => [],
                'por_classificacao' => [],
                'por_modelo' => [],
                'por_versao' => [],
                'histograma_confianca' => [],
                'distribuicao_datas_distribuicao' => [],
                'distribuicao_datas_julgamento' => [],
            ];
        }

        $aggs = $response['aggregations'] ?? [];

        return [
            'total' => (int) Arr::get($response, 'hits.total.value', Arr::get($response, 'hits.total', 0)),
            'por_tribunal' => $this->mapBuckets($aggs['por_tribunal']['buckets'] ?? []),
            'por_classe' => $this->mapBuckets($aggs['por_classe']['buckets'] ?? []),
            'por_assunto' => $this->mapBuckets($aggs['por_assunto']['buckets'] ?? []),
            'por_grau' => $this->mapBuckets($aggs['por_grau']['buckets'] ?? []),
            'por_classificacao' => $this->mapBuckets($aggs['por_classificacao']['buckets'] ?? []),
            'por_modelo' => $this->mapBuckets($aggs['por_modelo']['buckets'] ?? []),
            'por_versao' => $this->mapBuckets($aggs['por_versao']['buckets'] ?? []),
            'histograma_confianca' => $this->mapHistogram($aggs['por_confianca']['buckets'] ?? []),
            'distribuicao_datas_distribuicao' => $this->mapDateHistogram($aggs['por_data_distribuicao']['buckets'] ?? []),
            'distribuicao_datas_julgamento' => $this->mapDateHistogram($aggs['por_data_julgamento']['buckets'] ?? []),
        ];
    }

    /**
     * @return LengthAwarePaginator<NormalizedItem>
     */
    public function search(SearchFilters $filters, int $page = 1, int $perPage = 15, ?string $sortField = null, string $direction = 'desc'): LengthAwarePaginator
    {
        $body = [
            'from' => ($page - 1) * $perPage,
            'size' => $perPage,
            'track_total_hits' => true,
            'query' => $this->buildQuery($filters),
            'runtime_mappings' => $this->runtimeMappings(),
        ];

        if ($sort = $this->buildSort($sortField, $direction)) {
            $body['sort'] = $sort;
        }

        try {
            $response = $this->client->search($body);
        } catch (\Throwable) {
            return new LengthAwarePaginator(
                collect(),
                total: 0,
                perPage: $perPage,
                currentPage: $page,
                options: [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ],
            );
        }

        $hits = collect($response['hits']['hits'] ?? [])
            ->map(fn (array $hit): NormalizedItem => NormalizedItem::fromHit($hit));

        $total = (int) Arr::get($response, 'hits.total.value', Arr::get($response, 'hits.total', 0));

        return new LengthAwarePaginator(
            $hits,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQuery(SearchFilters $filters): array
    {
        $must = [];
        $filter = [];

        if (filled($filters->query)) {
            // Busca textual nos campos analisados com analyzer folded.
            $must[] = [
                'multi_match' => [
                    'query' => $filters->query,
                    'fields' => [
                        'teor^4',
                        'resumo^3',
                        'partes^2',
                        'relator',
                        'orgao_julgador',
                    ],
                    'type' => 'most_fields',
                ],
            ];
        }

        if ($filters->tribunais) {
            $filter[] = [
                'terms' => [
                    'tribunal_fallback' => $filters->tribunais,
                ],
            ];
        }

        if ($filters->classes) {
            $filter[] = [
                'terms' => [
                    'classe.keyword' => $filters->classes,
                ],
            ];
        }

        if ($filters->assuntos) {
            $filter[] = [
                'terms' => [
                    'assunto.keyword' => $filters->assuntos,
                ],
            ];
        }

        if ($filters->graus) {
            $filter[] = [
                'terms' => [
                    'grau' => array_map('intval', $filters->graus),
                ],
            ];
        }

        if ($filters->classificacoes) {
            $filter[] = [
                'terms' => [
                    'classificacao' => $filters->classificacoes,
                ],
            ];
        }

        if ($filters->modelos) {
            $filter[] = [
                'terms' => [
                    'classificacao_modelo' => $filters->modelos,
                ],
            ];
        }

        if ($filters->versoes) {
            $filter[] = [
                'terms' => [
                    'classificacao_versao' => $filters->versoes,
                ],
            ];
        }

        if ($filters->confiancaMinima !== null || $filters->confiancaMaxima !== null) {
            $range = [];
            if ($filters->confiancaMinima !== null) {
                $range['gte'] = $filters->confiancaMinima;
            }
            if ($filters->confiancaMaxima !== null) {
                $range['lte'] = $filters->confiancaMaxima;
            }

            $filter[] = [
                'range' => [
                    'classificacao_confianca' => $range,
                ],
            ];
        }

        if ($filters->dataDistribuicaoInicial || $filters->dataDistribuicaoFinal) {
            $range = [];
            if ($filters->dataDistribuicaoInicial) {
                $range['gte'] = $filters->dataDistribuicaoInicial->toIso8601String();
            }
            if ($filters->dataDistribuicaoFinal) {
                $range['lte'] = $filters->dataDistribuicaoFinal->toIso8601String();
            }

            $filter[] = [
                'range' => [
                    'data_distribuicao' => $range,
                ],
            ];
        }

        if ($filters->dataJulgamentoInicial || $filters->dataJulgamentoFinal) {
            $range = [];
            if ($filters->dataJulgamentoInicial) {
                $range['gte'] = $filters->dataJulgamentoInicial->toIso8601String();
            }
            if ($filters->dataJulgamentoFinal) {
                $range['lte'] = $filters->dataJulgamentoFinal->toIso8601String();
            }

            $filter[] = [
                'range' => [
                    'data_julgamento' => $range,
                ],
            ];
        }

        return [
            'bool' => [
                'must' => $must ?: [['match_all' => (object) []]],
                'filter' => $filter,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function aggregationDefinitions(): array
    {
        return [
            // Agrupa por tribunal usando runtime field que olha metadata.tribunal e orgao_julgador.
            'por_tribunal' => [
                'terms' => [
                    'field' => 'tribunal_fallback',
                    'size' => 30,
                    'missing' => 'Não informado',
                ],
            ],
            'por_classe' => [
                'terms' => [
                    'field' => 'classe.keyword',
                    'size' => 20,
                ],
            ],
            'por_assunto' => [
                'terms' => [
                    'field' => 'assunto.keyword',
                    'size' => 20,
                ],
            ],
            'por_grau' => [
                'terms' => [
                    'field' => 'grau',
                    'size' => 5,
                    'missing' => '0',
                ],
            ],
            'por_classificacao' => [
                'terms' => [
                    'field' => 'classificacao',
                    'size' => 15,
                ],
            ],
            'por_modelo' => [
                'terms' => [
                    'field' => 'classificacao_modelo',
                    'size' => 10,
                ],
            ],
            'por_versao' => [
                'terms' => [
                    'field' => 'classificacao_versao',
                    'size' => 10,
                ],
            ],
            'por_confianca' => [
                'histogram' => [
                    'field' => 'classificacao_confianca',
                    'interval' => 0.1,
                    'min_doc_count' => 0,
                ],
            ],
            'por_data_distribuicao' => [
                'date_histogram' => [
                    'field' => 'data_distribuicao',
                    'calendar_interval' => 'month',
                    'format' => 'yyyy-MM',
                ],
            ],
            'por_data_julgamento' => [
                'date_histogram' => [
                    'field' => 'data_julgamento',
                    'calendar_interval' => 'month',
                    'format' => 'yyyy-MM',
                ],
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $buckets
     * @return array<int, array{label: string, value: int}>
     */
    private function mapBuckets(array $buckets): array
    {
        return collect($buckets)
            ->map(fn (array $bucket): array => [
                'label' => (string) ($bucket['key_as_string'] ?? $bucket['key'] ?? 'n/a'),
                'value' => (int) ($bucket['doc_count'] ?? 0),
            ])
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $buckets
     * @return array<int, array{label: string, value: float}>
     */
    private function mapHistogram(array $buckets): array
    {
        return collect($buckets)
            ->map(fn (array $bucket): array => [
                'label' => number_format((float) ($bucket['key'] ?? 0), 1),
                'value' => (float) ($bucket['doc_count'] ?? 0),
            ])
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $buckets
     * @return array<int, array{label: string, value: int}>
     */
    private function mapDateHistogram(array $buckets): array
    {
        return collect($buckets)
            ->map(fn (array $bucket): array => [
                'label' => (string) ($bucket['key_as_string'] ?? $bucket['key'] ?? ''),
                'value' => (int) ($bucket['doc_count'] ?? 0),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSort(?string $sortField, string $direction): array
    {
        $allowedSorts = [
            'data_distribuicao' => 'data_distribuicao',
            'data_julgamento' => 'data_julgamento',
            'classificacao_confianca' => 'classificacao_confianca',
        ];

        $field = $allowedSorts[$sortField] ?? 'data_julgamento';
        $dir = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return [
            [$field => ['order' => $dir, 'missing' => '_last']],
        ];
    }

    /**
     * Runtime field ajuda a obter tribunal mesmo quando só está em metadata ou orgao_julgador.
     *
     * @return array<string, array<string, string>>
     */
    private function runtimeMappings(): array
    {
        return [
            'tribunal_fallback' => [
                'type' => 'keyword',
                'script' => <<<PAINLESS
                    if (params._source.containsKey('metadata') && params._source.metadata.containsKey('tribunal') && params._source.metadata.tribunal != null) {
                        emit(params._source.metadata.tribunal);
                    } else if (params._source.containsKey('orgao_julgador') && params._source.orgao_julgador != null) {
                        emit(params._source.orgao_julgador);
                    } else {
                        emit('Não informado');
                    }
                PAINLESS,
            ],
        ];
    }
}
