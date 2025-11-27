<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Busca de itens normalizados</h2>
                    <p class="text-sm text-gray-500">
                        Pesquise por teor, resumo, partes, relator ou órgão julgador usando o analyzer folded do índice itens-normalizados.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::button color="primary" wire:click="applyFilters">
                        Buscar
                    </x-filament::button>
                    <x-filament::button tag="a" href="{{ \App\Filament\Pages\JurimetriaDashboard::getUrl() }}" color="secondary">
                        Ver painel de estatísticas
                    </x-filament::button>
                    <x-filament::button color="gray" wire:click="resetFilters">
                        Limpar filtros
                    </x-filament::button>
                </div>
            </div>
            <div class="mt-4">
                {{ $this->form }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Exibindo {{ $this->searchResults->count() }} de {{ $this->searchResults->total() }} itens normalizados.
                </div>
                <div class="text-xs text-gray-500">
                    Ordenação: {{ $filters['sort_field'] ?? 'data_julgamento' }} ({{ $filters['sort_direction'] ?? 'desc' }})
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-2">Processo / Tribunal</th>
                            <th class="px-3 py-2">Classe / Assunto</th>
                            <th class="px-3 py-2">Relator / Partes</th>
                            <th class="px-3 py-2">Datas</th>
                            <th class="px-3 py-2">Classificação</th>
                            <th class="px-3 py-2">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($this->searchResults as $item)
                            <tr>
                                <td class="px-3 py-3 align-top">
                                    <div class="font-semibold text-gray-900">{{ $item->processo ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $item->metadata['tribunal'] ?? $item->orgaoJulgador ?? 'Tribunal não informado' }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Órgão julgador: {{ $item->orgaoJulgador ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <div class="text-gray-900">{{ $item->classe ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->assunto ?? '—' }}</div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <div class="text-gray-900">{{ $item->relator ?? 'Relator não informado' }}</div>
                                    <div class="text-xs text-gray-500">
                                        Partes: {{ \Illuminate\Support\Str::limit($item->partes ?? '—', 90) }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <div class="text-gray-900">Dist.: {{ $item->dataDistribuicao ?? '—' }}</div>
                                    <div class="text-gray-900">Julg.: {{ $item->dataJulgamento ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">Grau: {{ $item->grau ?? '—' }}</div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <div class="text-gray-900">{{ $item->classificacao ?? 'Sem rótulo' }}</div>
                                    <div class="text-xs text-gray-500">
                                        Confiança: {{ $item->classificacaoConfianca !== null ? number_format($item->classificacaoConfianca, 2) : '—' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Modelo: {{ $item->classificacaoModelo ?? '—' }} {{ $item->classificacaoVersao ? "({$item->classificacaoVersao})" : '' }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <x-filament::link
                                        color="primary"
                                        href="{{ \App\Filament\Pages\ItemNormalizadoShow::getUrl(['record' => $item->id]) }}"
                                    >
                                        Ver detalhes
                                    </x-filament::link>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                    Nenhum item encontrado para os filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $this->searchResults->onEachSide(1)->links() }}
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
