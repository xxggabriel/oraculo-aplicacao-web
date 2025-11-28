<?php

namespace App\Http\Controllers;

use App\Domain\Jurimetria\Dto\NormalizedItem;
use App\Domain\Jurimetria\Dto\SearchFilters;
use App\Domain\Jurimetria\Services\JurimetriaSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurisprudenciaController extends Controller
{
    public function index(): View
    {
        return view('jurisprudencia.search');
    }

    public function search(Request $request, JurimetriaSearchService $service): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:500'],
            'tribunais' => ['array'],
            'tribunais.*' => ['string', 'max:255'],
            'classes' => ['array'],
            'classes.*' => ['string', 'max:255'],
            'assuntos' => ['array'],
            'assuntos.*' => ['string', 'max:255'],
            'graus' => ['array'],
            'graus.*' => ['integer', 'between:0,5'],
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort_field' => ['nullable', 'in:data_julgamento,data_distribuicao,classificacao_confianca'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'incluir_sem_classificacao' => ['sometimes', 'boolean'],
        ]);

        $filters = SearchFilters::fromArray([
            'query' => $validated['q'] ?? null,
            'includeSemClassificacao' => (bool) ($validated['incluir_sem_classificacao'] ?? false),
            'tribunais' => $validated['tribunais'] ?? [],
            'classes' => $validated['classes'] ?? [],
            'assuntos' => $validated['assuntos'] ?? [],
            'graus' => $validated['graus'] ?? [],
            'classificacoes' => $validated['classificacoes'] ?? [],
            'modelos' => $validated['modelos'] ?? [],
            'versoes' => $validated['versoes'] ?? [],
            'confiancaMinima' => $validated['confianca_min'] ?? null,
            'confiancaMaxima' => $validated['confianca_max'] ?? null,
            'dataDistribuicaoInicial' => $validated['data_distribuicao_inicio'] ?? null,
            'dataDistribuicaoFinal' => $validated['data_distribuicao_fim'] ?? null,
            'dataJulgamentoInicial' => $validated['data_julgamento_inicio'] ?? null,
            'dataJulgamentoFinal' => $validated['data_julgamento_fim'] ?? null,
        ]);

        $perPage = max(1, (int) ($validated['per_page'] ?? 15));
        $perPage = min($perPage, 100);
        $page = max(1, (int) ($validated['page'] ?? 1));
        $sortField = $validated['sort_field'] ?? 'data_julgamento';
        $sortDirection = $validated['sort_direction'] ?? 'desc';

        $results = $service->search($filters, $page, $perPage, $sortField, $sortDirection);
        $aggregations = $service->aggregations($filters);

        return response()->json([
            'pagination' => [
                'page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ],
            'results' => $results->getCollection()->map(fn (NormalizedItem $item): array => [
                'id' => $item->id,
                'processo' => $item->processo,
                'classe' => $item->classe,
                'assunto' => $item->assunto,
                'partes' => $item->partes,
                'relator' => $item->relator,
                'orgao_julgador' => $item->orgaoJulgador,
                'tribunal' => $item->metadata['tribunal'] ?? $item->orgaoJulgador ?? '—',
                'data_distribuicao' => $item->dataDistribuicao,
                'data_julgamento' => $item->dataJulgamento,
                'classificacao' => $item->classificacao,
                'classificacao_confianca' => $item->classificacaoConfianca,
                'classificacao_modelo' => $item->classificacaoModelo,
                'classificacao_versao' => $item->classificacaoVersao,
            ]),
            'aggregations' => $aggregations,
        ]);
    }

    public function show(string $id, JurimetriaSearchService $service): View
    {
        $item = $service->find($id);

        abort_if($item === null, 404);

        return view('jurisprudencia.show', [
            'item' => $item,
        ]);
    }
}
