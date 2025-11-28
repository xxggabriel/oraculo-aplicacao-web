<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Oráculo | Busca de Jurisprudência</title>
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-3fpCfpo3Dk8ZFihgrnhcwOZefkRq3glw/2B+7uwZ0mo=" crossorigin="anonymous"></script>
        <script defer src="{{ route('assets.script') }}"></script>
    </head>
    <body class="bg-slate-950 text-slate-50 antialiased">
        <div class="min-h-screen flex flex-col">
            <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/80 backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-300 to-amber-600 text-lg font-black text-slate-950 shadow-lg">
                            Ω
                        </div>
                        <div>
                            <p class="text-xl font-semibold leading-tight">Oráculo Jurisprudência</p>
                            <p class="text-xs text-slate-400">Pesquisa avançada e estatísticas de classificação</p>
                        </div>
                    </div>
                    <nav class="flex items-center gap-2">
                        <a href="{{ route('jurisprudencia.index') }}" class="rounded-lg border border-white/10 bg-white/10 px-3 py-2 text-sm font-medium text-slate-50 shadow-sm transition hover:border-amber-300 hover:bg-amber-300/20">
                            Busca
                        </a>
                        <a href="/admin" class="rounded-lg border border-white/10 px-3 py-2 text-sm font-medium text-slate-200 transition hover:border-amber-300 hover:bg-amber-300/10">
                            Admin
                        </a>
                        <a href="#" class="rounded-lg border border-white/10 px-3 py-2 text-sm font-medium text-slate-200 transition hover:border-amber-300 hover:bg-amber-300/10">
                            Relatórios
                        </a>
                    </nav>
                </div>
            </header>

            <main
                id="jurisprudencia-app"
                data-search-url="{{ route('jurisprudencia.search') }}"
                data-show-base="{{ url('/jurisprudencia/processos') }}"
                class="flex-1"
            >
                <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6">
                    <div class="grid gap-6 lg:grid-cols-[330px,1fr]">
                        <aside class="space-y-4 rounded-2xl border border-white/5 bg-slate-900/70 p-4 shadow-lg shadow-amber-500/5">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-amber-300">Filtros</p>
                                    <p class="text-xs text-slate-400">Personalize a consulta e refine o resultado.</p>
                                </div>
                                <button
                                    type="button"
                                    id="reset-filters"
                                    class="rounded-lg border border-white/10 px-2 py-1 text-xs font-medium text-slate-200 transition hover:border-amber-300 hover:text-amber-200"
                                >
                                    Limpar
                                </button>
                            </div>

                            <form id="filters-form" class="space-y-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="q">Busca textual</label>
                                    <input
                                        id="q"
                                        name="q"
                                        type="text"
                                        placeholder="Teor, resumo, partes, relator..."
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="tribunais">Tribunais</label>
                                    <select
                                        id="tribunais"
                                        name="tribunais[]"
                                        multiple
                                        size="4"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option disabled>Carregando...</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="classes">Classes processuais</label>
                                    <select
                                        id="classes"
                                        name="classes[]"
                                        multiple
                                        size="4"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option disabled>Carregando...</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="assuntos">Assuntos</label>
                                    <select
                                        id="assuntos"
                                        name="assuntos[]"
                                        multiple
                                        size="4"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option disabled>Carregando...</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="graus">Grau</label>
                                    <select
                                        id="graus"
                                        name="graus[]"
                                        multiple
                                        size="2"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option value="1">1º grau</option>
                                        <option value="2">2º grau</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-slate-200" for="confianca_min">Confiança mín.</label>
                                        <input
                                            id="confianca_min"
                                            name="confianca_min"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="1"
                                            class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-slate-200" for="confianca_max">Confiança máx.</label>
                                        <input
                                            id="confianca_max"
                                            name="confianca_max"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="1"
                                            class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-slate-200" for="data_distribuicao_inicio">Dist. inicial</label>
                                        <input
                                            id="data_distribuicao_inicio"
                                            name="data_distribuicao_inicio"
                                            type="date"
                                            class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-slate-200" for="data_distribuicao_fim">Dist. final</label>
                                        <input
                                            id="data_distribuicao_fim"
                                            name="data_distribuicao_fim"
                                            type="date"
                                            class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-slate-200" for="data_julgamento_inicio">Julg. inicial</label>
                                        <input
                                            id="data_julgamento_inicio"
                                            name="data_julgamento_inicio"
                                            type="date"
                                            class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-slate-200" for="data_julgamento_fim">Julg. final</label>
                                        <input
                                            id="data_julgamento_fim"
                                            name="data_julgamento_fim"
                                            type="date"
                                            class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="classificacoes">Classificação</label>
                                    <select
                                        id="classificacoes"
                                        name="classificacoes[]"
                                        multiple
                                        size="4"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option disabled>Carregando...</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="modelos">Modelo</label>
                                    <select
                                        id="modelos"
                                        name="modelos[]"
                                        multiple
                                        size="3"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option disabled>Carregando...</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-slate-200" for="versoes">Versão do modelo</label>
                                    <select
                                        id="versoes"
                                        name="versoes[]"
                                        multiple
                                        size="3"
                                        class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option disabled>Carregando...</option>
                                    </select>
                                </div>

                                <div class="flex flex-wrap gap-2 pt-2">
                                    <button
                                        type="button"
                                        id="apply-filters"
                                        class="flex-1 rounded-xl bg-amber-400 px-4 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-amber-500/40 transition hover:-translate-y-0.5 hover:bg-amber-300"
                                    >
                                        Buscar
                                    </button>
                                    <button
                                        type="button"
                                        id="refresh-results"
                                        class="flex-1 rounded-xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-100 transition hover:border-amber-300 hover:text-amber-200"
                                    >
                                        Atualizar tabela
                                    </button>
                                </div>
                            </form>
                        </aside>

                        <section class="space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/5 bg-slate-900/70 p-4 shadow-lg shadow-amber-500/5">
                                <div>
                                    <p class="text-lg font-semibold text-slate-100">Resultados de jurisprudência</p>
                                    <p class="text-sm text-slate-400" id="results-meta">Carregando resultados...</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="text-xs text-slate-400" for="sort_field">Ordenar por</label>
                                    <select
                                        id="sort_field"
                                        class="rounded-lg border border-white/10 bg-slate-900/70 px-2 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option value="data_julgamento">Data de julgamento</option>
                                        <option value="data_distribuicao">Data de distribuição</option>
                                        <option value="classificacao_confianca">Confiança de classificação</option>
                                    </select>
                                    <select
                                        id="sort_direction"
                                        class="rounded-lg border border-white/10 bg-slate-900/70 px-2 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option value="desc">Desc</option>
                                        <option value="asc">Asc</option>
                                    </select>
                                    <label class="text-xs text-slate-400" for="per_page">Itens</label>
                                    <select
                                        id="per_page"
                                        class="rounded-lg border border-white/10 bg-slate-900/70 px-2 py-2 text-sm text-slate-100 outline-none ring-amber-400/40 focus:border-amber-300 focus:ring"
                                    >
                                        <option value="10">10</option>
                                        <option value="15" selected>15</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>

                            <div id="stats-cards" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-xl border border-dashed border-white/10 bg-slate-900/40 p-4 text-sm text-slate-400">
                                    Carregando estatísticas...
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-white/5 bg-slate-900/70 shadow-lg shadow-amber-500/5">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-white/5 text-sm">
                                        <thead class="bg-white/5 text-xs uppercase tracking-wide text-slate-400">
                                            <tr>
                                                <th class="px-4 py-3 text-left">Processo / Tribunal</th>
                                                <th class="px-4 py-3 text-left">Classe / Assunto</th>
                                                <th class="px-4 py-3 text-left">Relator e Partes</th>
                                                <th class="px-4 py-3 text-left">Datas</th>
                                                <th class="px-4 py-3 text-left">Classificação</th>
                                                <th class="px-4 py-3 text-left">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="results-body" class="divide-y divide-white/5 bg-slate-950/30">
                                            <tr>
                                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-400">Buscando dados...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="pagination" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/5 bg-slate-900/70 px-4 py-3 text-sm text-slate-300">
                                <span>Carregando paginação...</span>
                            </div>
                        </section>
                    </div>
                </div>
            </main>
        </div>

        <div
            id="loading-overlay"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm"
        >
            <div class="flex flex-col items-center gap-3 rounded-2xl border border-white/10 bg-slate-900/80 px-6 py-4 text-sm text-slate-100 shadow-xl shadow-amber-500/10">
                <div class="h-10 w-10 animate-spin rounded-full border-2 border-amber-300 border-t-transparent"></div>
                <p>Carregando resultados...</p>
            </div>
        </div>

        <div
            id="toast"
            class="fixed bottom-4 right-4 z-50 hidden max-w-xs rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-100 shadow-lg"
        ></div>
    </body>
</html>
