<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Processo {{ $item->processo ?? $item->id }} | Oráculo</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'system-ui', 'sans-serif'],
                        },
                    },
                },
            };
        </script>
    </head>
    <body class="bg-slate-950 text-slate-50 antialiased">
        <div class="min-h-screen flex flex-col">
            <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/80 backdrop-blur">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-300 to-amber-600 text-lg font-black text-slate-950 shadow-lg">
                            Ω
                        </div>
                        <div>
                            <p class="text-xl font-semibold leading-tight">Detalhes do Processo</p>
                            <p class="text-xs text-slate-400">Visualização detalhada no estilo JusBrasil</p>
                        </div>
                    </div>
                    <nav class="flex items-center gap-2">
                        <a href="{{ route('jurisprudencia.index') }}" class="rounded-lg border border-white/10 bg-white/10 px-3 py-2 text-sm font-medium text-slate-50 transition hover:border-amber-300 hover:bg-amber-300/20">
                            Voltar para busca
                        </a>
                        <a href="/admin" class="rounded-lg border border-white/10 px-3 py-2 text-sm font-medium text-slate-200 transition hover:border-amber-300 hover:bg-amber-300/10">
                            Admin
                        </a>
                    </nav>
                </div>
            </header>

            <main class="flex-1">
                <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-8 sm:px-6">
                    <div class="rounded-2xl border border-white/5 bg-gradient-to-br from-slate-900/90 via-slate-900/70 to-slate-950/80 p-6 shadow-xl shadow-amber-500/10">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-sm uppercase tracking-wide text-amber-300">Processo</p>
                                <h1 class="text-2xl font-bold text-slate-50">{{ $item->processo ?? $item->id }}</h1>
                                <p class="text-sm text-slate-400">
                                    Tribunal / Órgão julgador:
                                    <span class="text-slate-100">{{ $item->metadata['tribunal'] ?? $item->orgaoJulgador ?? 'Não informado' }}</span>
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2 text-right">
                                <span class="rounded-full border border-amber-400/40 bg-amber-400/10 px-3 py-1 text-xs font-semibold uppercase text-amber-100">
                                    {{ $item->classificacao ?? 'Sem rótulo' }}
                                </span>
                                <span class="text-sm text-slate-300">
                                    Confiança:
                                    <strong class="text-amber-200">
                                        {{ $item->classificacaoConfianca !== null ? number_format($item->classificacaoConfianca, 2) : '—' }}
                                    </strong>
                                </span>
                                @if ($item->classificacaoModelo)
                                    <span class="text-xs text-slate-400">
                                        Modelo: {{ $item->classificacaoModelo }}
                                        @if ($item->classificacaoVersao)
                                            <span class="text-slate-500">({{ $item->classificacaoVersao }})</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                                <p class="text-xs uppercase text-slate-400">Classe</p>
                                <p class="text-sm font-semibold text-slate-100">{{ $item->classe ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $item->assunto ?? 'Assunto não informado' }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                                <p class="text-xs uppercase text-slate-400">Relator</p>
                                <p class="text-sm font-semibold text-slate-100">{{ $item->relator ?? 'Não informado' }}</p>
                                <p class="text-xs text-slate-400">Grau: {{ $item->grau ?? '—' }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                                <p class="text-xs uppercase text-slate-400">Distribuição</p>
                                <p class="text-sm font-semibold text-slate-100">{{ $item->dataDistribuicao ?? '—' }}</p>
                                <p class="text-xs text-slate-400">Julga.: {{ $item->dataJulgamento ?? '—' }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-slate-900/60 p-4">
                                <p class="text-xs uppercase text-slate-400">Código interno</p>
                                <p class="text-sm font-semibold text-slate-100">{{ $item->id }}</p>
                                <p class="text-xs text-slate-400">Item bruto: {{ $item->itemBrutoId ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[1.2fr,0.8fr]">
                        <section class="space-y-4 rounded-2xl border border-white/5 bg-slate-900/70 p-6 shadow-lg shadow-amber-500/5">
                            <h2 class="text-lg font-semibold text-slate-100">Partes e relatoria</h2>
                            <div class="space-y-2 text-sm text-slate-300">
                                <p><span class="font-semibold text-slate-100">Relator: </span>{{ $item->relator ?? 'Não informado' }}</p>
                                <p><span class="font-semibold text-slate-100">Partes: </span>{{ $item->partes ?? 'Não informado' }}</p>
                                <p><span class="font-semibold text-slate-100">Órgão julgador: </span>{{ $item->orgaoJulgador ?? '—' }}</p>
                            </div>
                        </section>

                        <section class="space-y-4 rounded-2xl border border-white/5 bg-slate-900/70 p-6 shadow-lg shadow-amber-500/5">
                            <h2 class="text-lg font-semibold text-slate-100">Classificação</h2>
                            <div class="space-y-2 text-sm text-slate-300">
                                <p><span class="font-semibold text-slate-100">Rótulo: </span>{{ $item->classificacao ?? 'Sem rótulo' }}</p>
                                <p><span class="font-semibold text-slate-100">Confiança: </span>{{ $item->classificacaoConfianca !== null ? number_format($item->classificacaoConfianca, 2) : '—' }}</p>
                                <p>
                                    <span class="font-semibold text-slate-100">Modelo: </span>
                                    {{ $item->classificacaoModelo ?? '—' }}
                                    @if ($item->classificacaoVersao)
                                        <span class="text-slate-500">({{ $item->classificacaoVersao }})</span>
                                    @endif
                                </p>
                                <p><span class="font-semibold text-slate-100">Classificado em: </span>{{ $item->classificadoEm ?? '—' }}</p>
                            </div>
                        </section>
                    </div>

                    <section class="rounded-2xl border border-white/5 bg-slate-900/70 p-6 shadow-lg shadow-amber-500/5">
                        <h2 class="text-lg font-semibold text-slate-100">Resumo</h2>
                        <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-slate-200">{{ $item->resumo ?? 'Sem resumo disponível.' }}</p>
                    </section>

                    <section class="rounded-2xl border border-white/5 bg-slate-900/70 p-6 shadow-lg shadow-amber-500/5">
                        <h2 class="text-lg font-semibold text-slate-100">Teor completo</h2>
                        <p class="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-slate-200">{{ $item->teor ?? 'Sem teor disponível.' }}</p>
                    </section>

                    <section class="rounded-2xl border border-white/5 bg-slate-900/70 p-6 shadow-lg shadow-amber-500/5">
                        <h2 class="text-lg font-semibold text-slate-100">Metadata do índice</h2>
                        @if (empty($item->metadata))
                            <p class="mt-2 text-sm text-slate-400">Nenhuma metadata adicional disponível.</p>
                        @else
                            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($item->metadata as $key => $value)
                                    <div class="rounded-xl border border-white/10 bg-slate-900/60 p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-400">{{ $key }}</p>
                                        <p class="mt-1 text-sm text-slate-100">
                                            {{ is_array($value) ? json_encode($value) : (string) $value }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>
            </main>
        </div>
    </body>
</html>
