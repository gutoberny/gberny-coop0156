<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes — Coop0156</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        panelBorder: '#1e293b',
                    },
                },
            },
        };
    </script>
    <style>
        body {
            background-color: #020617;
            background-image:
                radial-gradient(at 20% 0%, rgba(16, 185, 129, 0.06) 0px, transparent 50%),
                radial-gradient(at 80% 100%, rgba(59, 130, 246, 0.06) 0px, transparent 50%);
            color: #e2e8f0;
        }
        .glass-panel {
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid #1e293b;
        }
    </style>
</head>
<body class="min-h-screen font-sans antialiased">

    <header class="border-b border-panelBorder/40">
        <div class="max-w-6xl mx-auto px-4 py-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-white">Clientes</h1>
                <p class="text-sm text-slate-500 mt-1">Cadastro da cooperativa, consumindo a API REST de clientes.</p>
            </div>
            <a href="/" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-all">
                &larr; Nova análise de crédito
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Formulário de cadastro -->
        <section class="lg:col-span-5 glass-panel rounded-3xl p-8">
            <h2 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <span class="bg-emerald-500/10 text-emerald-400 p-2 rounded-lg text-xs">01</span>
                Novo Cliente
            </h2>

            <div id="alerta" class="hidden rounded-xl p-4 mb-6 text-sm" role="alert" aria-live="polite"></div>

            <form id="form-cliente" class="space-y-5" novalidate>
                <div>
                    <label for="nome" class="block text-sm font-medium text-slate-400 mb-2">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required
                        class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                    <p data-erro="nome" class="hidden text-xs text-red-400 mt-1.5"></p>
                </div>

                <div>
                    <label for="cpf" class="block text-sm font-medium text-slate-400 mb-2">CPF</label>
                    <input type="text" id="cpf" name="cpf" required inputmode="numeric" maxlength="14" placeholder="000.000.000-00"
                        class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                    <p data-erro="cpf" class="hidden text-xs text-red-400 mt-1.5"></p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-400 mb-2">E-mail</label>
                    <input type="email" id="email" name="email" required
                        class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                    <p data-erro="email" class="hidden text-xs text-red-400 mt-1.5"></p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-slate-400 mb-2">
                            Telefone <span class="text-slate-500 font-normal">(opcional)</span>
                        </label>
                        <input type="text" id="telefone" name="telefone"
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        <p data-erro="telefone" class="hidden text-xs text-red-400 mt-1.5"></p>
                    </div>
                    <div>
                        <label for="renda_mensal" class="block text-sm font-medium text-slate-400 mb-2">Renda (R$)</label>
                        <input type="number" step="0.01" min="0" id="renda_mensal" name="renda_mensal" required
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                        <p data-erro="renda_mensal" class="hidden text-xs text-red-400 mt-1.5"></p>
                    </div>
                </div>

                <button type="submit" id="btn-salvar"
                    class="w-full bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-semibold py-3.5 px-6 rounded-xl transition-all flex items-center justify-center gap-2">
                    <span id="txt-salvar">Cadastrar Cliente</span>
                </button>
            </form>
        </section>

        <!-- Listagem -->
        <section class="lg:col-span-7 glass-panel rounded-3xl p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <span class="bg-emerald-500/10 text-emerald-400 p-2 rounded-lg text-xs">02</span>
                    Cadastrados
                </h2>
                <span id="total-clientes" class="text-xs text-slate-500"></span>
            </div>

            <div id="lista-vazia" class="hidden text-center py-16 border-2 border-dashed border-panelBorder rounded-2xl">
                <p class="text-slate-400 font-medium">Nenhum cliente cadastrado</p>
                <p class="text-sm text-slate-500 mt-1">Use o formulário ao lado para começar.</p>
            </div>

            <div id="tabela-wrapper" class="hidden overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-panelBorder">
                            <th class="text-left font-semibold pb-3">Cliente</th>
                            <th class="text-left font-semibold pb-3">CPF</th>
                            <th class="text-right font-semibold pb-3">Renda</th>
                            <th class="text-right font-semibold pb-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="lista-clientes" class="divide-y divide-panelBorder"></tbody>
                </table>
            </div>

            <div id="paginacao" class="hidden items-center justify-between mt-6 pt-4 border-t border-panelBorder">
                <button id="btn-anterior" class="text-sm text-slate-400 hover:text-slate-200 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                    &larr; Anterior
                </button>
                <span id="info-pagina" class="text-xs text-slate-500"></span>
                <button id="btn-proxima" class="text-sm text-slate-400 hover:text-slate-200 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                    Próxima &rarr;
                </button>
            </div>
        </section>

    </main>

    <footer class="border-t border-panelBorder/40 py-6 text-center text-xs text-slate-600">
        <p>&copy; 2026 Coop0156. Desafio Técnico Laravel.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = (id) => document.getElementById(id);

            const form = el('form-cliente');
            const btnSalvar = el('btn-salvar');
            const txtSalvar = el('txt-salvar');
            const alerta = el('alerta');
            const corpoTabela = el('lista-clientes');

            const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

            let paginaAtual = 1;

            const mascararCpf = (valor) => {
                const digitos = String(valor).replace(/\D/g, '').slice(0, 11);

                return digitos
                    .replace(/^(\d{3})(\d)/, '$1.$2')
                    .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                    .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
            };

            el('cpf').addEventListener('input', (evento) => {
                evento.target.value = mascararCpf(evento.target.value);
            });

            const exibirAlerta = (mensagem, tipo = 'erro') => {
                const estilos = {
                    erro: 'bg-red-500/10 border border-red-500/20 text-red-400',
                    sucesso: 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400',
                };

                alerta.className = `rounded-xl p-4 mb-6 text-sm ${estilos[tipo]}`;
                alerta.textContent = mensagem;
                alerta.classList.remove('hidden');
            };

            const limparErros = () => {
                alerta.classList.add('hidden');

                document.querySelectorAll('[data-erro]').forEach((campo) => {
                    campo.classList.add('hidden');
                    campo.textContent = '';
                });

                form.querySelectorAll('input').forEach((campo) => {
                    campo.classList.remove('border-red-500/60');
                });
            };

            const exibirErrosDeValidacao = (erros) => {
                Object.entries(erros).forEach(([campo, mensagens]) => {
                    const destino = document.querySelector(`[data-erro="${campo}"]`);
                    const input = el(campo);

                    if (destino) {
                        destino.textContent = mensagens[0];
                        destino.classList.remove('hidden');
                    }

                    if (input) {
                        input.classList.add('border-red-500/60');
                    }
                });
            };

            // -------------------------------------------------------------
            // Listagem paginada
            // -------------------------------------------------------------
            const linha = (cliente) => {
                const tr = document.createElement('tr');

                tr.innerHTML = `
                    <td class="py-3 pr-4">
                        <p class="font-medium text-slate-100">${cliente.nome}</p>
                        <p class="text-xs text-slate-500">${cliente.email}</p>
                    </td>
                    <td class="py-3 pr-4 font-mono text-slate-300">${mascararCpf(cliente.cpf)}</td>
                    <td class="py-3 pr-4 text-right text-slate-300">${moeda.format(cliente.renda_mensal)}</td>
                    <td class="py-3 text-right">
                        <button data-remover="${cliente.id}" class="text-xs text-red-400 hover:text-red-300 font-medium transition-all">
                            Remover
                        </button>
                    </td>
                `;

                return tr;
            };

            const carregar = async (pagina = 1) => {
                try {
                    const resposta = await fetch(`/api/clientes?page=${pagina}&per_page=10`, {
                        headers: { Accept: 'application/json' },
                    });

                    if (!resposta.ok) {
                        exibirAlerta('Não foi possível carregar a lista de clientes.');
                        return;
                    }

                    const { data, meta } = await resposta.json();

                    paginaAtual = meta.current_page;
                    corpoTabela.replaceChildren(...data.map(linha));

                    const vazio = meta.total === 0;
                    el('lista-vazia').classList.toggle('hidden', !vazio);
                    el('tabela-wrapper').classList.toggle('hidden', vazio);

                    el('total-clientes').textContent = vazio
                        ? ''
                        : `${meta.total} ${meta.total === 1 ? 'cliente' : 'clientes'}`;

                    const paginacao = el('paginacao');
                    paginacao.classList.toggle('hidden', meta.last_page <= 1);
                    paginacao.classList.toggle('flex', meta.last_page > 1);

                    el('info-pagina').textContent = `Página ${meta.current_page} de ${meta.last_page}`;
                    el('btn-anterior').disabled = meta.current_page === 1;
                    el('btn-proxima').disabled = meta.current_page === meta.last_page;
                } catch (erro) {
                    exibirAlerta('Falha de comunicação com o servidor.');
                }
            };

            el('btn-anterior').addEventListener('click', () => carregar(paginaAtual - 1));
            el('btn-proxima').addEventListener('click', () => carregar(paginaAtual + 1));

            // -------------------------------------------------------------
            // Cadastro
            // -------------------------------------------------------------
            form.addEventListener('submit', async (evento) => {
                evento.preventDefault();
                limparErros();

                const dados = Object.fromEntries(new FormData(form).entries());
                dados.cpf = String(dados.cpf).replace(/\D/g, '');

                if (!dados.telefone) {
                    delete dados.telefone;
                }

                btnSalvar.disabled = true;
                btnSalvar.classList.add('opacity-60', 'cursor-not-allowed');
                txtSalvar.textContent = 'Cadastrando...';

                try {
                    const resposta = await fetch('/api/clientes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                        },
                        body: JSON.stringify(dados),
                    });

                    const corpo = await resposta.json().catch(() => ({}));

                    if (resposta.status === 422) {
                        exibirErrosDeValidacao(corpo.errors ?? {});
                        exibirAlerta('Revise os campos destacados.');
                        return;
                    }

                    if (!resposta.ok) {
                        exibirAlerta(corpo.message ?? 'Não foi possível cadastrar o cliente.');
                        return;
                    }

                    form.reset();
                    exibirAlerta(`Cliente ${corpo.data.nome} cadastrado com sucesso.`, 'sucesso');
                    await carregar(1);
                } catch (erro) {
                    exibirAlerta('Falha de comunicação com o servidor.');
                } finally {
                    btnSalvar.disabled = false;
                    btnSalvar.classList.remove('opacity-60', 'cursor-not-allowed');
                    txtSalvar.textContent = 'Cadastrar Cliente';
                }
            });

            // -------------------------------------------------------------
            // Remoção
            // -------------------------------------------------------------
            corpoTabela.addEventListener('click', async (evento) => {
                const botao = evento.target.closest('[data-remover]');

                if (!botao) {
                    return;
                }

                if (!window.confirm('Remover este cliente? As análises vinculadas serão desassociadas.')) {
                    return;
                }

                botao.disabled = true;

                try {
                    const resposta = await fetch(`/api/clientes/${botao.dataset.remover}`, {
                        method: 'DELETE',
                        headers: { Accept: 'application/json' },
                    });

                    if (!resposta.ok) {
                        exibirAlerta('Não foi possível remover o cliente.');
                        botao.disabled = false;
                        return;
                    }

                    exibirAlerta('Cliente removido.', 'sucesso');
                    await carregar(paginaAtual);
                } catch (erro) {
                    exibirAlerta('Falha de comunicação com o servidor.');
                    botao.disabled = false;
                }
            });

            carregar();
        });
    </script>

</body>
</html>
