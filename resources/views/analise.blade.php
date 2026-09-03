<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Crédito Cooperativo</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        coop: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            900: '#14532d',
                        },
                        darkBg: '#0b0f19',
                        panelBg: '#131c2e',
                        panelBorder: '#1e2d4a',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0b0f19;
            background-image: 
                radial-gradient(at 0% 0%, hsla(142, 70%, 15%, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, hsla(220, 70%, 15%, 0.15) 0px, transparent 50%);
        }
        /* Glassmorphism utility */
        .glass-panel {
            background: rgba(19, 28, 46, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(30, 45, 74, 0.6);
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex flex-col font-sans">

    <!-- Header / Navbar -->
    <header class="border-b border-panelBorder/50 py-5 glass-panel sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-emerald-400 to-green-300 bg-clip-text text-transparent">Coop0156</h1>
                    <p class="text-xs text-slate-400">Desafio Análise de Crédito</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="/clientes" class="text-sm text-slate-400 hover:text-emerald-400 font-medium transition-all">
                    Clientes
                </a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Ambiente de Testes
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-6xl mx-auto px-4 py-12 w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Formulário de Solicitação -->
        <section class="lg:col-span-7 glass-panel rounded-3xl p-8 shadow-2xl relative overflow-hidden transition-all duration-300 hover:border-panelBorder">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl"></div>
            
            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <span class="bg-emerald-500/10 text-emerald-400 p-2 rounded-lg text-sm">01</span>
                Nova Solicitação de Crédito
            </h2>
            
            <!-- Alerta geral (falhas de comunicação, Bureau indisponível, etc.) -->
            <div id="alerta-erro" class="hidden bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6" role="alert" aria-live="assertive">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <span class="text-red-400 text-xs block font-semibold uppercase tracking-wider mb-1">Não foi possível concluir</span>
                        <p id="alerta-erro-mensagem" class="text-slate-200 text-sm"></p>
                    </div>
                </div>
            </div>

            <form id="form-analise" class="space-y-6" novalidate>
                <!-- Nome Completo -->
                <div>
                    <label for="nome" class="block text-sm font-medium text-slate-400 mb-2">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required placeholder="Digite o nome completo do proponente"
                        class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    <p data-erro="nome" class="hidden text-xs text-red-400 mt-1.5"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- CPF -->
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-slate-400 mb-2">CPF</label>
                        <input type="text" id="cpf" name="cpf" required inputmode="numeric" maxlength="14" placeholder="000.000.000-00"
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        <p data-erro="cpf" class="hidden text-xs text-red-400 mt-1.5"></p>
                    </div>

                    <!-- Renda Mensal -->
                    <div>
                        <label for="renda_mensal" class="block text-sm font-medium text-slate-400 mb-2">Renda Mensal (R$)</label>
                        <input type="number" step="0.01" min="0" id="renda_mensal" name="renda_mensal" required placeholder="Ex: 3500.00"
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        <p data-erro="renda_mensal" class="hidden text-xs text-red-400 mt-1.5"></p>
                    </div>
                </div>

                <!-- E-mail (opcional) -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-400 mb-2">
                        E-mail <span class="text-slate-500 font-normal">(opcional)</span>
                    </label>
                    <input type="email" id="email" name="email" placeholder="proponente@email.com"
                        class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    <p data-erro="email" class="hidden text-xs text-red-400 mt-1.5"></p>
                    <p class="text-xs text-slate-500 mt-1.5">Usado no cadastro do cliente. Se não informado, um endereço provisório é gerado a partir do CPF.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipo de Crédito -->
                    <div>
                        <label for="tipo_credito" class="block text-sm font-medium text-slate-400 mb-2">Tipo de Crédito</label>
                        <select id="tipo_credito" name="tipo_credito" required
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            <option value="" disabled selected>Selecione uma opção</option>
                            <option value="pessoal">Crédito Pessoal</option>
                            <option value="imobiliario">Crédito Imobiliário</option>
                            <option value="automotivo">Crédito Automotivo</option>
                        </select>
                        <p data-erro="tipo_credito" class="hidden text-xs text-red-400 mt-1.5"></p>
                    </div>

                    <!-- Valor Solicitado -->
                    <div>
                        <label for="valor_solicitado" class="block text-sm font-medium text-slate-400 mb-2">Valor Requerido (R$)</label>
                        <input type="number" step="0.01" min="0.01" id="valor_solicitado" name="valor_solicitado" required placeholder="Ex: 15000.00"
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                        <p data-erro="valor_solicitado" class="hidden text-xs text-red-400 mt-1.5"></p>
                    </div>
                </div>

                <!-- Prévia da simulação, calculada no cliente antes de enviar -->
                <div id="previa-simulacao" class="hidden bg-slate-950/40 border border-panelBorder rounded-xl p-4">
                    <span class="text-slate-500 text-xs block font-semibold uppercase tracking-wider mb-2">Prévia (sujeita à análise)</span>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Parcela estimada (12x)</span>
                        <span id="previa-parcela" class="font-medium text-slate-200">-</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1.5">
                        <span class="text-slate-400">Renda comprometida</span>
                        <span id="previa-comprometimento" class="font-medium text-slate-200">-</span>
                    </div>
                </div>

                <!-- Botão Enviar -->
                <button type="submit" id="btn-solicitar"
                    class="w-full bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform active:scale-98 shadow-lg shadow-emerald-500/10 flex items-center justify-center gap-2">
                    <span id="txt-solicitar">Solicitar Análise de Crédito</span>
                    <svg id="loading-spinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </section>

        <!-- Resultados e Contratação -->
        <section class="lg:col-span-5 space-y-6">
            
            <!-- Card de Resultado Inicial (Placeholder) -->
            <div id="resultado-vazio" class="glass-panel rounded-3xl p-8 text-center border-dashed border-2 border-panelBorder flex flex-col items-center justify-center py-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-slate-400">Aguardando Solicitação</h3>
                <p class="text-sm text-slate-500 mt-2 max-w-xs">Preencha os dados do formulário ao lado e solicite a análise para simular as condições.</p>
            </div>

            <!-- Card de Resultado da Análise -->
            <div id="resultado-analise" class="glass-panel rounded-3xl p-8 shadow-2xl relative overflow-hidden hidden">
                <div id="status-indicator-badge" class="absolute top-6 right-6">
                    <!-- Badge Aprovado ou Reprovado (Dinâmico) -->
                </div>

                <h3 class="text-xl font-semibold mb-6 flex items-center gap-2">
                    <span class="bg-emerald-500/10 text-emerald-400 p-2 rounded-lg text-sm">02</span>
                    Resultado da Análise
                </h3>

                <!-- Dados da Análise -->
                <div class="space-y-4 divide-y divide-panelBorder">
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-400 text-sm">Proponente</span>
                        <span id="res-nome" class="font-medium text-slate-100">-</span>
                    </div>
                    <div class="flex justify-between pt-4">
                        <span class="text-slate-400 text-sm">CPF</span>
                        <span id="res-cpf" class="font-medium text-slate-100">-</span>
                    </div>
                    <div class="flex justify-between pt-4">
                        <span class="text-slate-400 text-sm">Score de Crédito</span>
                        <span id="res-score" class="font-medium text-slate-100">-</span>
                    </div>
                    <div class="flex justify-between pt-4">
                        <span class="text-slate-400 text-sm">Status da Análise</span>
                        <span id="res-status" class="font-bold">-</span>
                    </div>
                    
                    <!-- Bloco Aprovado -->
                    <div id="dados-aprovado" class="space-y-4 pt-4 hidden">
                        <div class="flex justify-between">
                            <span class="text-slate-400 text-sm">Taxa de Juros Aplicada</span>
                            <span id="res-taxa" class="font-medium text-emerald-400">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 text-sm">Parcela Mensal (12x)</span>
                            <span id="res-parcela" class="font-bold text-lg text-emerald-400">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 text-sm">Renda Comprometida</span>
                            <span id="res-comprometimento" class="font-medium text-slate-100">-</span>
                        </div>
                    </div>

                    <!-- Bloco Reprovado -->
                    <div id="dados-reprovado" class="pt-4 hidden">
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mt-2">
                            <span class="text-red-400 text-xs block font-semibold uppercase tracking-wider mb-1">Motivo da Recusa</span>
                            <p id="res-motivo" class="text-slate-200 text-sm">-</p>
                        </div>
                    </div>
                </div>

                <!-- Ações para Contratação -->
                <div id="container-contratacao" class="mt-8 pt-6 border-t border-panelBorder hidden">
                    <a id="btn-contratar" href="#"
                        class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform active:scale-98 shadow-lg shadow-indigo-500/10 flex items-center justify-center gap-2">
                        <span id="txt-contratar">Ver Simulação e Contratar</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <p class="text-center text-xs text-slate-500 mt-3">Revise as condições completas na tela de simulação antes de confirmar a contratação.</p>
                </div>
            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-panelBorder/40 py-6 text-center text-xs text-slate-600">
        <div class="max-w-6xl mx-auto px-4">
            <p>&copy; 2026 CoopCred. Todos os direitos reservados. Desafio Técnico Laravel.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const PARCELAS = 12;
            const COMPROMETIMENTO_MAXIMO = 0.30;

            const el = (id) => document.getElementById(id);

            const form = el('form-analise');
            const btnSolicitar = el('btn-solicitar');
            const txtSolicitar = el('txt-solicitar');
            const spinner = el('loading-spinner');
            const inputCpf = el('cpf');

            const alerta = el('alerta-erro');
            const alertaMensagem = el('alerta-erro-mensagem');

            const cardVazio = el('resultado-vazio');
            const cardResultado = el('resultado-analise');
            const badge = el('status-indicator-badge');
            const blocoAprovado = el('dados-aprovado');
            const blocoReprovado = el('dados-reprovado');
            const containerContratacao = el('container-contratacao');
            const btnContratar = el('btn-contratar');

            const previa = el('previa-simulacao');

            const moeda = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });

            const percentual = (fracao) =>
                `${(fracao * 100).toLocaleString('pt-BR', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1,
                })}%`;

            // -------------------------------------------------------------
            // Máscara de CPF — a API normaliza de todo modo, mas a máscara
            // deixa claro o formato esperado.
            // -------------------------------------------------------------
            const mascararCpf = (valor) => {
                const digitos = valor.replace(/\D/g, '').slice(0, 11);

                return digitos
                    .replace(/^(\d{3})(\d)/, '$1.$2')
                    .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                    .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
            };

            inputCpf.addEventListener('input', (evento) => {
                evento.target.value = mascararCpf(evento.target.value);
            });

            // -------------------------------------------------------------
            // Feedback de erros
            // -------------------------------------------------------------
            const limparErros = () => {
                alerta.classList.add('hidden');
                alertaMensagem.textContent = '';

                document.querySelectorAll('[data-erro]').forEach((campo) => {
                    campo.classList.add('hidden');
                    campo.textContent = '';
                });

                form.querySelectorAll('input, select').forEach((campo) => {
                    campo.classList.remove('border-red-500/60');
                });
            };

            const exibirAlerta = (mensagem) => {
                alertaMensagem.textContent = mensagem;
                alerta.classList.remove('hidden');
                alerta.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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

                exibirAlerta('Revise os campos destacados e tente novamente.');
            };

            // -------------------------------------------------------------
            // Prévia da parcela, recalculada conforme o usuário digita
            // -------------------------------------------------------------
            const atualizarPrevia = () => {
                const renda = parseFloat(el('renda_mensal').value);
                const valor = parseFloat(el('valor_solicitado').value);

                if (!Number.isFinite(renda) || !Number.isFinite(valor) || renda <= 0 || valor <= 0) {
                    previa.classList.add('hidden');
                    return;
                }

                // Taxa ainda desconhecida (depende do score); a prévia usa a
                // taxa padrão de 4,5% como cenário conservador.
                const parcela = (valor + valor * 0.045 * PARCELAS) / PARCELAS;
                const comprometimento = parcela / renda;

                el('previa-parcela').textContent = moeda.format(parcela);

                const alvo = el('previa-comprometimento');
                alvo.textContent = percentual(comprometimento);
                alvo.classList.toggle('text-red-400', comprometimento > COMPROMETIMENTO_MAXIMO);
                alvo.classList.toggle('text-slate-200', comprometimento <= COMPROMETIMENTO_MAXIMO);

                previa.classList.remove('hidden');
            };

            ['renda_mensal', 'valor_solicitado'].forEach((campo) => {
                el(campo).addEventListener('input', atualizarPrevia);
            });

            // -------------------------------------------------------------
            // Exibição do resultado
            // -------------------------------------------------------------
            const badgeHtml = (aprovado) => {
                const cor = aprovado
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                    : 'bg-red-500/10 text-red-400 border-red-500/20';

                return `<span class="${cor} border text-xs font-semibold uppercase tracking-wider px-3 py-1.5 rounded-full">
                    ${aprovado ? 'Aprovado' : 'Reprovado'}
                </span>`;
            };

            const exibirResultado = (analise) => {
                const aprovado = analise.status === 'aprovado';

                el('res-nome').textContent = analise.nome;
                el('res-cpf').textContent = mascararCpf(analise.cpf);
                el('res-score').textContent = analise.score ?? 'Não informado';

                const status = el('res-status');
                status.textContent = aprovado ? 'Aprovado' : 'Reprovado';
                status.className = `font-bold ${aprovado ? 'text-emerald-400' : 'text-red-400'}`;

                badge.innerHTML = badgeHtml(aprovado);

                blocoAprovado.classList.toggle('hidden', !aprovado);
                blocoReprovado.classList.toggle('hidden', aprovado);
                containerContratacao.classList.toggle('hidden', !aprovado);

                if (aprovado) {
                    el('res-taxa').textContent = `${analise.taxa_juros.toLocaleString('pt-BR', {
                        minimumFractionDigits: 1,
                    })}% a.m.`;
                    el('res-parcela').textContent = moeda.format(analise.valor_parcela);
                    el('res-comprometimento').textContent = percentual(
                        analise.valor_parcela / analise.renda_mensal,
                    );

                    btnContratar.href = analise.url_simulacao ?? `/simulacao/${analise.id}`;
                } else {
                    el('res-motivo').textContent = analise.motivo_rejeicao;
                }

                cardVazio.classList.add('hidden');
                cardResultado.classList.remove('hidden');
                cardResultado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            };

            // -------------------------------------------------------------
            // Submissão
            // -------------------------------------------------------------
            const alternarCarregando = (carregando) => {
                btnSolicitar.disabled = carregando;
                btnSolicitar.classList.toggle('opacity-60', carregando);
                btnSolicitar.classList.toggle('cursor-not-allowed', carregando);
                spinner.classList.toggle('hidden', !carregando);
                txtSolicitar.textContent = carregando
                    ? 'Consultando o Bureau de Crédito...'
                    : 'Solicitar Análise de Crédito';
            };

            form.addEventListener('submit', async (evento) => {
                evento.preventDefault();
                limparErros();

                const dados = Object.fromEntries(new FormData(form).entries());

                // Campos opcionais em branco não são enviados, para não
                // disparar a validação de formato de e-mail à toa.
                if (!dados.email) {
                    delete dados.email;
                }

                alternarCarregando(true);

                try {
                    const resposta = await fetch('/api/analise-credito', {
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
                        return;
                    }

                    if (!resposta.ok) {
                        exibirAlerta(
                            corpo.message ??
                                'Não foi possível concluir a análise. Tente novamente em instantes.',
                        );
                        return;
                    }

                    exibirResultado(corpo.data);
                } catch (erro) {
                    exibirAlerta(
                        'Falha de comunicação com o servidor. Verifique sua conexão e tente novamente.',
                    );
                } finally {
                    alternarCarregando(false);
                }
            });
        });
    </script>
</body>
</html>
