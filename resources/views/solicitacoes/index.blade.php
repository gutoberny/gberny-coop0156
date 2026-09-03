@extends('layouts.app')

@section('titulo', 'Solicitações de crédito')
@section('descricao', 'Análises realizadas, com o score consultado no Bureau e as condições aprovadas.')

@section('acao')
    <a href="/solicitacoes/nova"
       class="rounded-lg bg-verde px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro">
        Nova solicitação
    </a>
@endsection

@section('conteudo')

    <div id="alerta" class="mb-6 hidden rounded-lg p-4 text-sm font-semibold" role="status" aria-live="polite"></div>

    {{-- Filtro ativo, quando a lista chega a partir de um cliente --}}
    <div id="filtro" class="mb-6 hidden items-center gap-3 rounded-lg bg-neutroClaro/40 px-4 py-3 text-sm">
        <span id="filtro-texto" class="text-neutroEscuro"></span>
        <a href="/solicitacoes" class="font-semibold text-verdeEscuro hover:text-verde">Ver todas</a>
    </div>

    <p id="carregando" class="py-16 text-center text-neutroEscuro">Carregando solicitações…</p>

    <div id="vazio" class="hidden rounded-xl border border-dashed border-neutroClaro py-16 text-center">
        <p class="font-marca text-lg font-semibold text-verdeEscuro">Nenhuma solicitação por aqui</p>
        <p class="mx-auto mt-2 max-w-sm text-sm text-neutroEscuro">
            Solicite uma análise para consultar o score no Bureau e simular as condições de crédito.
        </p>
        <a href="/solicitacoes/nova" class="mt-6 inline-block rounded-lg bg-verde px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro">
            Solicitar análise
        </a>
    </div>

    <div id="tabela" class="hidden">
        <div class="overflow-x-auto rounded-xl border border-neutroClaro">
            <table class="w-full text-sm">
                <thead class="bg-neutroClaro/40">
                    <tr class="text-left text-neutroEscuro">
                        <th scope="col" class="px-5 py-3 font-bold">Proponente</th>
                        <th scope="col" class="px-5 py-3 font-bold">Crédito</th>
                        <th scope="col" class="px-5 py-3 text-right font-bold">Valor</th>
                        <th scope="col" class="px-5 py-3 text-right font-bold">Score</th>
                        <th scope="col" class="px-5 py-3 text-right font-bold">Parcela</th>
                        <th scope="col" class="px-5 py-3 font-bold">Situação</th>
                        <th scope="col" class="px-5 py-3 text-right font-bold">
                            <span class="sr-only">Ações</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="linhas" class="divide-y divide-neutroClaro"></tbody>
            </table>
        </div>

        <div id="paginacao" class="mt-5 hidden items-center justify-between gap-4">
            <button type="button" id="anterior"
                    class="rounded-lg px-3 py-2 text-sm font-semibold text-verdeEscuro transition-colors hover:bg-neutroClaro/50 disabled:cursor-not-allowed disabled:text-neutroEscuro/40 disabled:hover:bg-transparent">
                Anterior
            </button>
            <p id="pagina" class="text-sm text-neutroEscuro"></p>
            <button type="button" id="proxima"
                    class="rounded-lg px-3 py-2 text-sm font-semibold text-verdeEscuro transition-colors hover:bg-neutroClaro/50 disabled:cursor-not-allowed disabled:text-neutroEscuro/40 disabled:hover:bg-transparent">
                Próxima
            </button>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const el = (id) => document.getElementById(id);
        const linhas = el('linhas');
        const alerta = el('alerta');

        const clienteId = new URLSearchParams(window.location.search).get('cliente_id');
        let pagina = 1;

        /* Espelha os estilos de resources/views/partials/badge-status.blade.php */
        const BADGES = {
            aprovado: ['Aprovado', 'bg-neutroClaro text-verdeEscuro'],
            contratado: ['Contratado', 'bg-verde text-white'],
            reprovado: ['Reprovado', 'bg-magenta/10 text-magenta'],
            pendente: ['Pendente', 'bg-amarelo/25 text-tinta'],
            processando_contratacao: ['Processando contratação', 'bg-amarelo/25 text-tinta'],
        };

        const avisar = (mensagem) => {
            alerta.className = 'mb-6 rounded-lg bg-magenta/10 p-4 text-sm font-semibold text-magenta';
            alerta.textContent = mensagem;
            alerta.classList.remove('hidden');
        };

        const badge = (status) => {
            const [rotulo, estilo] = BADGES[status] ?? [status, 'bg-neutroClaro text-tinta'];

            return `<span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold ${estilo}">${rotulo}</span>`;
        };

        const linha = (analise) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-neutroClaro/20';

            const tipos = { pessoal: 'Pessoal', imobiliario: 'Imobiliário', automotivo: 'Automotivo' };

            /* Reprovação: o motivo é a informação útil, então ocupa o lugar da parcela. */
            const parcela = analise.valor_parcela !== null
                ? `<span class="font-marca font-semibold">${window.Coop.moeda.format(analise.valor_parcela)}</span>
                   <span class="block text-xs text-neutroEscuro">${analise.parcelas}× a ${analise.taxa_juros.toLocaleString('pt-BR', { minimumFractionDigits: 1 })}%</span>`
                : '<span class="text-neutroEscuro">—</span>';

            const acao = analise.url_simulacao
                ? `<a href="${analise.url_simulacao}" class="font-semibold text-verdeEscuro hover:text-verde">Simular</a>`
                : '';

            tr.innerHTML = `
                <td class="px-5 py-4">
                    <p class="font-semibold">${analise.nome}</p>
                    <p class="numero mt-0.5 whitespace-nowrap text-xs text-neutroEscuro">${window.Coop.mascararCpf(analise.cpf)}</p>
                </td>
                <td class="px-5 py-4">${tipos[analise.tipo_credito] ?? analise.tipo_credito}</td>
                <td class="numero px-5 py-4 text-right font-marca font-semibold">${window.Coop.moeda.format(analise.valor_solicitado)}</td>
                <td class="numero px-5 py-4 text-right">${analise.score ?? '<span class="text-neutroEscuro">—</span>'}</td>
                <td class="numero px-5 py-4 text-right">${parcela}</td>
                <td class="px-5 py-4">
                    ${badge(analise.status)}
                    ${analise.motivo_rejeicao ? `<p class="mt-1 max-w-48 text-xs text-neutroEscuro">${analise.motivo_rejeicao}</p>` : ''}
                </td>
                <td class="px-5 py-4 text-right whitespace-nowrap">${acao}</td>
            `;

            return tr;
        };

        const carregar = async (destino = 1) => {
            const parametros = new URLSearchParams({ page: destino, per_page: 10 });

            if (clienteId) {
                parametros.set('cliente_id', clienteId);
            }

            try {
                const { resposta, corpo } = await window.Coop.enviar(`/api/analise-credito?${parametros}`);

                if (!resposta.ok) {
                    avisar('Não foi possível carregar as solicitações. Recarregue a página para tentar novamente.');
                    return;
                }

                const { data, meta } = corpo;
                pagina = meta.current_page;

                linhas.replaceChildren(...data.map(linha));

                if (clienteId) {
                    const nome = data[0]?.nome;
                    el('filtro-texto').textContent = nome
                        ? `Mostrando apenas as solicitações de ${nome}.`
                        : 'Mostrando apenas as solicitações do cliente selecionado.';
                    el('filtro').classList.remove('hidden');
                    el('filtro').classList.add('flex');
                }

                const vazio = meta.total === 0;
                el('vazio').classList.toggle('hidden', !vazio);
                el('tabela').classList.toggle('hidden', vazio);

                const paginacao = el('paginacao');
                const varias = meta.last_page > 1;
                paginacao.classList.toggle('hidden', !varias);
                paginacao.classList.toggle('flex', varias);

                el('pagina').textContent = `Página ${meta.current_page} de ${meta.last_page} · ${meta.total} ${meta.total === 1 ? 'solicitação' : 'solicitações'}`;
                el('anterior').disabled = meta.current_page === 1;
                el('proxima').disabled = meta.current_page === meta.last_page;
            } catch (erro) {
                avisar('Sem conexão com o servidor. Verifique sua rede e recarregue a página.');
            } finally {
                el('carregando').classList.add('hidden');
            }
        };

        el('anterior').addEventListener('click', () => carregar(pagina - 1));
        el('proxima').addEventListener('click', () => carregar(pagina + 1));

        carregar();
    });
</script>
@endpush
