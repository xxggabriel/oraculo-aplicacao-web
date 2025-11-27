<x-filament-panels::page>
    @if ($item)
        <div class="space-y-6">
            <x-filament::section>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold">Processo {{ $item->processo ?? $item->id }}</h2>
                        <p class="text-sm text-gray-500">Detalhes do documento no índice itens-normalizados.</p>
                    </div>
                    <x-filament::badge color="gray">
                        Grau: {{ $item->grau ?? '—' }}
                    </x-filament::badge>
                </div>

                <dl class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Tribunal / Órgão julgador</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $item->metadata['tribunal'] ?? $item->orgaoJulgador ?? 'Não informado' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Classe</dt>
                        <dd class="text-sm text-gray-900">{{ $item->classe ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Assunto</dt>
                        <dd class="text-sm text-gray-900">{{ $item->assunto ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Relator</dt>
                        <dd class="text-sm text-gray-900">{{ $item->relator ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Partes</dt>
                        <dd class="text-sm text-gray-900">{{ $item->partes ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Datas</dt>
                        <dd class="text-sm text-gray-900">
                            Distribuição: {{ $item->dataDistribuicao ?? '—' }}<br>
                            Julgamento: {{ $item->dataJulgamento ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Classificação</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $item->classificacao ?? 'Sem rótulo' }}
                            <span class="text-xs text-gray-500">
                                ({{ $item->classificacaoConfianca !== null ? number_format($item->classificacaoConfianca, 2) : '—' }})
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Modelo</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $item->classificacaoModelo ?? '—' }}
                            @if ($item->classificacaoVersao)
                                <span class="text-xs text-gray-500">{{ $item->classificacaoVersao }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-500">Classificado em</dt>
                        <dd class="text-sm text-gray-900">{{ $item->classificadoEm ?? '—' }}</dd>
                    </div>
                </dl>
            </x-filament::section>

            <x-filament::section>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Resumo</h3>
                        <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900">{{ $item->resumo ?? '—' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Teor</h3>
                        <p class="mt-2 whitespace-pre-wrap text-sm text-gray-900">{{ $item->teor ?? '—' }}</p>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <h3 class="text-sm font-semibold text-gray-700">Metadata do índice</h3>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($item->metadata as $key => $value)
                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="text-xs uppercase text-gray-500">{{ $key }}</div>
                            <div class="text-sm text-gray-900">
                                {{ is_array($value) ? json_encode($value) : (string) $value }}
                            </div>
                        </div>
                    @endforeach
                    @if (empty($item->metadata))
                        <p class="text-sm text-gray-500">Sem metadata adicional.</p>
                    @endif
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>
