@extends('layouts.app')

@section('titulo', 'Clientes')
@section('descricao', 'Pessoas associadas cadastradas na cooperativa.')

@section('acao')
    <a href="/clientes/novo"
       class="rounded-lg bg-verde px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro">
        Novo cliente
    </a>
@endsection

@section('conteudo')

    <div id="alerta" class="mb-6 hidden rounded-lg p-4 text-sm" role="status" aria-live="polite"></div>

    {{-- Carregando --}}
    <p id="carregando" class="py-16 text-center text-neutroEscuro">Carregando clientes…</p>

    {{-- Vazio --}}
    <div id="vazio" class="hidden rounded-xl border border-dashed border-neutroClaro py-16 text-center">
        <p class="font-marca text-lg font-semibold text-verdeEscuro">Nenhum cliente cadastrado</p>
        <p class="mx-auto mt-2 max-w-sm text-sm text-neutroEscuro">
            Cadastre a primeira pessoa associada para começar a simular crédito.
        </p>
        <a href="/clientes/novo" class="mt-6 inline-block rounded-lg bg-verde px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro">
            Cadastrar cliente
        </a>
    </div>

    {{-- Tabela --}}
    <div id="tabela" class="hidden">
        <div class="overflow-x-auto rounded-xl border border-neutroClaro">
            <table class="w-full text-sm">
                <thead class="bg-neutroClaro/40">
                    <tr class="text-left text-neutroEscuro">
                        <th scope="col" class="px-5 py-3 font-bold">Cliente</th>
                        <th scope="col" class="px-5 py-3 font-bold">CPF</th>
                        <th scope="col" class="px-5 py-3 text-right font-bold">Renda mensal</th>
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

        let pagina = 1;

        const avisar = (mensagem, tipo = 'erro') => {
            const estilos = {
                erro: 'bg-magenta/10 text-magenta',
                sucesso: 'bg-neutroClaro text-verdeEscuro',
            };

            alerta.className = `mb-6 rounded-lg p-4 text-sm font-semibold ${estilos[tipo]}`;
            alerta.textContent = mensagem;
            alerta.classList.remove('hidden');
        };

        const linha = (cliente) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-neutroClaro/20';

            const email = window.Coop.emailProvisorio(cliente.email)
                ? '<span class="italic">E-mail não informado</span>'
                : cliente.email;

            tr.innerHTML = `
                <td class="px-5 py-4">
                    <p class="font-semibold">${cliente.nome}</p>
                    <p class="mt-0.5 text-xs text-neutroEscuro">${email}</p>
                </td>
                <td class="numero whitespace-nowrap px-5 py-4 text-neutroEscuro">${window.Coop.mascararCpf(cliente.cpf)}</td>
                <td class="numero px-5 py-4 text-right font-marca font-semibold">${window.Coop.moeda.format(cliente.renda_mensal)}</td>
                <td class="px-5 py-4 text-right whitespace-nowrap">
                    <a href="/solicitacoes?cliente_id=${cliente.id}" class="font-semibold text-verdeEscuro hover:text-verde">Solicitações</a>
                    <button type="button" data-remover="${cliente.id}" data-nome="${cliente.nome}"
                            class="ml-4 font-semibold text-neutroEscuro hover:text-magenta">Remover</button>
                </td>
            `;

            return tr;
        };

        const carregar = async (destino = 1) => {
            try {
                const { resposta, corpo } = await window.Coop.enviar(`/api/clientes?page=${destino}&per_page=10`);

                if (!resposta.ok) {
                    avisar('Não foi possível carregar os clientes. Recarregue a página para tentar novamente.');
                    return;
                }

                const { data, meta } = corpo;
                pagina = meta.current_page;

                linhas.replaceChildren(...data.map(linha));

                const vazio = meta.total === 0;
                el('vazio').classList.toggle('hidden', !vazio);
                el('tabela').classList.toggle('hidden', vazio);

                const paginacao = el('paginacao');
                const varias = meta.last_page > 1;
                paginacao.classList.toggle('hidden', !varias);
                paginacao.classList.toggle('flex', varias);

                el('pagina').textContent = `Página ${meta.current_page} de ${meta.last_page} · ${meta.total} ${meta.total === 1 ? 'cliente' : 'clientes'}`;
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

        linhas.addEventListener('click', async (evento) => {
            const botao = evento.target.closest('[data-remover]');
            if (!botao) return;

            const nome = botao.dataset.nome;

            if (!window.confirm(`Remover ${nome}? As solicitações de crédito dessa pessoa continuam registradas, mas deixam de estar vinculadas a ela.`)) {
                return;
            }

            botao.disabled = true;

            try {
                const { resposta } = await window.Coop.enviar(`/api/clientes/${botao.dataset.remover}`, { method: 'DELETE' });

                if (!resposta.ok) {
                    avisar(`Não foi possível remover ${nome}. Tente novamente.`);
                    botao.disabled = false;
                    return;
                }

                avisar(`${nome} foi removido.`, 'sucesso');
                await carregar(pagina);
            } catch (erro) {
                avisar('Sem conexão com o servidor. Tente novamente.');
                botao.disabled = false;
            }
        });

        carregar();
    });
</script>
@endpush
