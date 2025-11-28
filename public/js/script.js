(() => {
    const boot = () => {
        if (typeof window.jQuery === 'undefined') {
            console.error('jQuery não encontrado após tentativa de carregamento.');
            return;
        }

        const $ = window.jQuery;

        const formatNumber = (value) => new Intl.NumberFormat('pt-BR').format(value ?? 0);

        const debounce = (fn, delay = 350) => {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn(...args), delay);
            };
        };

        $(function () {
            const root = $('#jurisprudencia-app');
            if (!root.length) {
                return;
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '',
                },
            });

            const endpoints = {
                search: root.data('search-url'),
                showBase: root.data('show-base'),
            };

            const baseState = {
                q: '',
                tribunais: [],
                classes: [],
                assuntos: [],
                graus: [],
                classificacoes: [],
                modelos: [],
                versoes: [],
                confianca_min: '',
                confianca_max: '',
                data_distribuicao_inicio: '',
                data_distribuicao_fim: '',
                data_julgamento_inicio: '',
                data_julgamento_fim: '',
                per_page: 15,
                page: 1,
                sort_field: 'data_julgamento',
                sort_direction: 'desc',
            };

            let state = { ...baseState };

            const $form = $('#filters-form');
            const $cards = $('#stats-cards');
            const $tbody = $('#results-body');
            const $pagination = $('#pagination');
            const $loading = $('#loading-overlay');
            const $resultsMeta = $('#results-meta');
            const $sortField = $('#sort_field');
            const $sortDirection = $('#sort_direction');
            const $perPage = $('#per_page');
            const $toast = $('#toast');

            const showToast = (message) => {
                if (!$toast.length) return;
                $toast.text(message).removeClass('hidden');
                setTimeout(() => $toast.addClass('hidden'), 3500);
            };

            const toggleLoading = (show) => {
                if (!$loading.length) return;
                $loading.toggleClass('hidden', !show);
            };

            const applyFormState = () => {
                state.q = ($form.find('[name="q"]').val() || '').toString().trim();
                state.tribunais = $form.find('[name="tribunais[]"]').val() || [];
                state.classes = $form.find('[name="classes[]"]').val() || [];
                state.assuntos = $form.find('[name="assuntos[]"]').val() || [];
                state.graus = $form.find('[name="graus[]"]').val() || [];
                state.classificacoes = $form.find('[name="classificacoes[]"]').val() || [];
                state.modelos = $form.find('[name="modelos[]"]').val() || [];
                state.versoes = $form.find('[name="versoes[]"]').val() || [];
                state.confianca_min = $form.find('[name="confianca_min"]').val() || '';
                state.confianca_max = $form.find('[name="confianca_max"]').val() || '';
                state.data_distribuicao_inicio = $form.find('[name="data_distribuicao_inicio"]').val() || '';
                state.data_distribuicao_fim = $form.find('[name="data_distribuicao_fim"]').val() || '';
                state.data_julgamento_inicio = $form.find('[name="data_julgamento_inicio"]').val() || '';
                state.data_julgamento_fim = $form.find('[name="data_julgamento_fim"]').val() || '';
                state.per_page = parseInt($perPage.val(), 10) || 15;
                state.sort_field = $sortField.val();
                state.sort_direction = $sortDirection.val();
            };

            const renderSelectOptions = (bucketKey, selectName, aggs) => {
                const select = $form.find(`[name="${selectName}"]`);
                if (!select.length) return;

                const buckets = (aggs?.[bucketKey] || []).slice();
                const selected = select.val() || [];
                const selectedSet = new Set(selected);
                const preserved = [...selectedSet]
                    .filter((value) => !buckets.some((bucket) => bucket.label === value))
                    .map((value) => ({ label: value, value: 0 }));

                const options = [...buckets, ...preserved];
                select.empty();

                if (!options.length) {
                    select.append($('<option disabled>').text('Nenhum valor disponível'));
                    return;
                }

                options.sort((a, b) => (b.value || 0) - (a.value || 0));
                options.forEach((bucket) => {
                    const option = $('<option>')
                        .val(bucket.label)
                        .text(bucket.value ? `${bucket.label} (${bucket.value})` : bucket.label);

                    if (selectedSet.has(bucket.label)) {
                        option.prop('selected', true);
                    }
                    select.append(option);
                });
            };

            const renderCards = (aggs) => {
                $cards.empty();
                const buckets = aggs?.por_classificacao || [];
                const total = aggs?.total ?? 0;
                const topClassifications = buckets.slice(0, 4);

                const cards = [
                    {
                        title: 'Total classificados',
                        value: total,
                        accent: 'from-amber-400 to-amber-600',
                        subtitle: 'Documentos com rótulo presente',
                    },
                    ...topClassifications.map((bucket, idx) => ({
                        title: bucket.label,
                        value: bucket.value,
                        accent: idx % 2 === 0 ? 'from-emerald-400 to-teal-500' : 'from-blue-400 to-indigo-500',
                        subtitle: total > 0 ? `${Math.round((bucket.value / total) * 100)}% do conjunto` : 'Sem percentual',
                    })),
                ];

                if (!cards.length || total === 0) {
                    $cards.append(
                        $('<div>')
                            .addClass('rounded-xl border border-dashed border-white/10 bg-slate-900/40 p-4 text-sm text-slate-400')
                            .text('Nenhuma estatística disponível para o filtro atual.'),
                    );
                    return;
                }

                cards.forEach((card) => {
                    const percent =
                        total > 0 && card.title !== 'Total classificados'
                            ? Math.round(((card.value || 0) / total) * 100)
                            : null;

                    const $card = $(`
                        <div class="rounded-2xl border border-white/5 bg-slate-900/60 p-4 shadow-lg shadow-amber-500/5">
                            <p class="text-xs uppercase tracking-wide text-slate-400">${card.title}</p>
                            <div class="mt-2 flex items-end justify-between gap-2">
                                <p class="text-2xl font-bold text-slate-50">${formatNumber(card.value)}</p>
                                ${
                                    percent !== null
                                        ? `<span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-xs font-semibold text-slate-200">${percent}%</span>`
                                        : ''
                                }
                            </div>
                            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-white/5">
                                <div class="h-full w-full rounded-full bg-gradient-to-r ${card.accent}"></div>
                            </div>
                            <p class="mt-2 text-xs text-slate-400">${card.subtitle}</p>
                        </div>
                    `);

                    $cards.append($card);
                });
            };

            const renderResults = (items) => {
                $tbody.empty();

                if (!items.length) {
                    $tbody.append(
                        $('<tr>').append(
                            $('<td>')
                                .attr('colspan', 6)
                                .addClass('px-4 py-6 text-center text-sm text-slate-400')
                                .text('Nenhum item encontrado para os filtros selecionados.'),
                        ),
                    );
                    return;
                }

                items.forEach((item) => {
                    const processo = item.processo || item.id;
                    const tribunal = item.tribunal || '—';
                    const partes = item.partes ? item.partes.toString().slice(0, 120) : 'Não informado';
                    const confianca =
                        item.classificacao_confianca !== null && item.classificacao_confianca !== undefined
                            ? Number(item.classificacao_confianca).toFixed(2)
                            : '—';

                    const row = $(`
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-slate-50">${processo}</p>
                                <p class="text-xs text-slate-400">${tribunal}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-slate-100">${item.classe ?? '—'}</p>
                                <p class="text-xs text-slate-400">${item.assunto ?? '—'}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-slate-100">${item.relator ?? 'Relator não informado'}</p>
                                <p class="text-xs text-slate-400">Partes: ${partes}</p>
                            </td>
                            <td class="px-4 py-3 align-top text-sm text-slate-200">
                                <p>Dist.: ${item.data_distribuicao ?? '—'}</p>
                                <p>Julg.: ${item.data_julgamento ?? '—'}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-slate-100">${item.classificacao ?? 'Sem rótulo'}</p>
                                <p class="text-xs text-slate-400">Confiança: ${confianca}</p>
                                <p class="text-xs text-slate-500">${item.classificacao_modelo ?? 'Modelo não informado'}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <a class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-3 py-2 text-xs font-semibold text-slate-950 shadow hover:-translate-y-0.5 hover:bg-amber-300 transition"
                                   href="${endpoints.showBase}/${encodeURIComponent(item.id)}">
                                    Ver processo
                                </a>
                            </td>
                        </tr>
                    `);

                    $tbody.append(row);
                });
            };

            const renderPagination = (pagination) => {
                const { page = 1, per_page = 15, total = 0, last_page = 1 } = pagination || {};
                const start = total === 0 ? 0 : (page - 1) * per_page + 1;
                const end = Math.min(page * per_page, total);

                $pagination.empty();
                const info = $('<span>').addClass('text-sm text-slate-400').text(`Exibindo ${start}–${end} de ${formatNumber(total)} itens`);

                const actions = $('<div>').addClass('flex items-center gap-2');
                const prev = $('<button>')
                    .text('Anterior')
                    .addClass('rounded-lg border border-white/10 px-3 py-2 text-sm transition hover:border-amber-300 hover:text-amber-200')
                    .prop('disabled', page <= 1)
                    .toggleClass('opacity-40 cursor-not-allowed', page <= 1)
                    .data('page', page - 1);
                const next = $('<button>')
                    .text('Próxima')
                    .addClass('rounded-lg border border-white/10 px-3 py-2 text-sm transition hover:border-amber-300 hover:text-amber-200')
                    .prop('disabled', page >= last_page)
                    .toggleClass('opacity-40 cursor-not-allowed', page >= last_page)
                    .data('page', page + 1);

                actions.append(prev, $('<span class="text-xs text-slate-500">').text(`Página ${page} de ${last_page}`), next);
                $pagination.append(info, actions);

                $pagination.find('button').on('click', function () {
                    const targetPage = Number($(this).data('page'));
                    if (!targetPage || targetPage < 1 || targetPage > last_page) return;
                    state.page = targetPage;
                    performSearch();
                });
            };

            const renderMeta = (pagination, aggs) => {
                const total = aggs?.total ?? pagination?.total ?? 0;
                const currentCount = pagination?.per_page ?? 0;
                $resultsMeta.text(
                    total > 0
                        ? `Exibindo ${Math.min(currentCount, total)} itens de ${formatNumber(total)} encontrados`
                        : 'Nenhum resultado encontrado para os filtros atuais.',
                );
            };

            const renderFilters = (aggs) => {
                renderSelectOptions('por_tribunal', 'tribunais[]', aggs);
                renderSelectOptions('por_classe', 'classes[]', aggs);
                renderSelectOptions('por_assunto', 'assuntos[]', aggs);
                renderSelectOptions('por_classificacao', 'classificacoes[]', aggs);
                renderSelectOptions('por_modelo', 'modelos[]', aggs);
                renderSelectOptions('por_versao', 'versoes[]', aggs);
            };

            const performSearch = ({ resetPage = false } = {}) => {
                if (!endpoints.search) return;

                if (resetPage) {
                    state.page = 1;
                }

                toggleLoading(true);

                $.getJSON(endpoints.search, state)
                    .done((response) => {
                        renderFilters(response.aggregations || {});
                        renderCards(response.aggregations || {});
                        renderResults(response.results || []);
                        renderPagination(response.pagination || {});
                        renderMeta(response.pagination || {}, response.aggregations || {});
                    })
                    .fail(() => {
                        showToast('Erro ao buscar dados. Verifique a conexão ou o cluster de busca.');
                    })
                    .always(() => toggleLoading(false));
            };

            const queueSearch = debounce(() => {
                applyFormState();
                performSearch({ resetPage: true });
            }, 400);

            $form.on('input', 'input', queueSearch);
            $form.on('change', 'select', queueSearch);

            $('#apply-filters').on('click', (event) => {
                event.preventDefault();
                applyFormState();
                performSearch({ resetPage: true });
            });

            $('#refresh-results').on('click', (event) => {
                event.preventDefault();
                applyFormState();
                performSearch({ resetPage: true });
            });

            $('#reset-filters').on('click', (event) => {
                event.preventDefault();
                $form[0].reset();
                $form.find('select[multiple]').val([]);
                state = { ...baseState };
                performSearch({ resetPage: true });
            });

            $sortField.on('change', () => {
                applyFormState();
                performSearch({ resetPage: true });
            });

            $sortDirection.on('change', () => {
                applyFormState();
                performSearch({ resetPage: true });
            });

            $perPage.on('change', () => {
                applyFormState();
                performSearch({ resetPage: true });
            });

            performSearch({ resetPage: true });
        });
    };

    if (typeof window.jQuery === 'undefined') {
        const fallback = document.createElement('script');
        fallback.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js';
        fallback.integrity = 'sha512-3gJwYp80bPp3o5cHI7lUohc7QsImzR1JKUUUVXwvYdGQ5QShPiYzd9jkF3lD93oa5sd4HPQwVv0L8R1GZfNNNg==';
        fallback.crossOrigin = 'anonymous';
        fallback.referrerPolicy = 'no-referrer';
        fallback.onload = boot;
        fallback.onerror = () => console.error('Falha ao carregar jQuery dos CDNs.');
        document.head.appendChild(fallback);
    } else {
        boot();
    }
})();
