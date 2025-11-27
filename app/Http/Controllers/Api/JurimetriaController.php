<?php

namespace App\Http\Controllers\Api;

use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JurimetriaController extends Controller
{
    public function __construct(private readonly JurimetriaSearchService $service)
    {
    }

    public function ping(): JsonResponse
    {
        return response()->json([
            'ok' => $this->service->ping(),
            'index' => config('search.elasticsearch.index'),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        $filters = $this->makeFilters($validated);
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) min(($validated['per_page'] ?? 15), 100);
        $sortField = $validated['sort_field'] ?? null;
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        $results = $this->service->search($filters, $page, $perPage, $sortField, $sortDirection);
        $aggregations = $this->service->aggregations($filters);

        return response()->json([
            'meta' => [
                'page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
            'data' => $results->items(),
            'aggregations' => $aggregations,
        ]);
    }

    public function aggregations(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);
        $filters = $this->makeFilters($validated);

        return response()->json([
            'filters' => $validated,
            'aggregations' => $this->service->aggregations($filters),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function makeFilters(array $data): SearchFilters
    {
        return SearchFilters::fromArray([
            'query' => $data['q'] ?? $data['query'] ?? null,
            'tribunais' => $data['tribunais'] ?? [],
            'classes' => $data['classes'] ?? [],
            'assuntos' => $data['assuntos'] ?? [],
            'graus' => $data['graus'] ?? [],
            'classificacoes' => $data['classificacoes'] ?? [],
            'modelos' => $data['modelos'] ?? [],
            'versoes' => $data['versoes'] ?? [],
            'confiancaMinima' => $data['confianca_min'] ?? $data['confiancaMinima'] ?? null,
            'confiancaMaxima' => $data['confianca_max'] ?? $data['confiancaMaxima'] ?? null,
            'dataDistribuicaoInicial' => $data['data_distribuicao_inicio'] ?? $data['dataDistribuicaoInicial'] ?? null,
            'dataDistribuicaoFinal' => $data['data_distribuicao_fim'] ?? $data['dataDistribuicaoFinal'] ?? null,
            'dataJulgamentoInicial' => $data['data_julgamento_inicio'] ?? $data['dataJulgamentoInicial'] ?? null,
            'dataJulgamentoFinal' => $data['data_julgamento_fim'] ?? $data['dataJulgamentoFinal'] ?? null,
        ]);
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:500'],
            'query' => ['nullable', 'string', 'max:500'],
            'tribunais' => ['array'],
            'tribunais.*' => ['string', 'max:255'],
            'classes' => ['array'],
            'classes.*' => ['string', 'max:255'],
            'assuntos' => ['array'],
            'assuntos.*' => ['string', 'max:255'],
            'graus' => ['array'],
            'graus.*' => ['integer'],
            'classificacoes' => ['array'],
            'classificacoes.*' => ['string', 'max:255'],
            'modelos' => ['array'],
            'modelos.*' => ['string', 'max:255'],
            'versoes' => ['array'],
            'versoes.*' => ['string', 'max:255'],
            'confianca_min' => ['nullable', 'numeric', 'between:0,1'],
            'confianca_max' => ['nullable', 'numeric', 'between:0,1'],
            'data_distribuicao_inicio' => ['nullable', 'date'],
            'data_distribuicao_fim' => ['nullable', 'date'],
            'data_julgamento_inicio' => ['nullable', 'date'],
            'data_julgamento_fim' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_field' => ['nullable', 'string'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
        ]);
    }
}
