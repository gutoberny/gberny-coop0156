<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') — Coop0156</title>

    {{--
        Tipografia oficial da marca: Exo 2.0 (principal, personalidade e
        títulos) e Nunito, que o manual indica para textos corridos e
        interfaces. Ver https://marca.sicredi.com.br/tipografia/
    --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    /*
                     * Paleta oficial: https://marca.sicredi.com.br/cores/
                     * `tinta` é o único tom derivado — o manual não define
                     * preto de texto, então usamos um neutro escuro irmão
                     * do Neutro Escuro (#5A645A).
                     */
                    colors: {
                        verde: '#3FA110',
                        verdeEscuro: '#146E37',
                        neutroClaro: '#D7E6C8',
                        neutroEscuro: '#5A645A',
                        amarelo: '#FFCD00',
                        magenta: '#E60050',
                        tinta: '#2B332E',
                    },
                    fontFamily: {
                        marca: ['"Exo 2"', 'system-ui', 'sans-serif'],
                        texto: ['Nunito', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>

    <style>
        body { background-color: #FFFFFF; }

        /* Valores monetários alinham por dígito nas colunas das tabelas. */
        .numero { font-variant-numeric: tabular-nums; }

        /* Foco visível em todo elemento interativo. */
        :focus-visible {
            outline: 2px solid #3FA110;
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="font-texto text-tinta antialiased">

<div class="min-h-screen lg:flex">

    {{-- Navegação --}}
    <nav class="bg-verdeEscuro lg:w-64 lg:shrink-0 lg:min-h-screen" aria-label="Navegação principal">
        <div class="lg:sticky lg:top-0">

            <a href="/clientes" class="flex items-baseline gap-2 px-6 py-6 text-white">
                <span class="font-marca font-bold text-xl tracking-tight">Sicredi</span>
                <span class="font-texto text-sm text-neutroClaro">Coop0156</span>
            </a>

            @php
                $itens = [
                    ['rota' => '/clientes', 'texto' => 'Clientes'],
                    ['rota' => '/solicitacoes', 'texto' => 'Solicitações de crédito'],
                ];
            @endphp

            <ul class="flex lg:flex-col gap-1 px-3 pb-4 overflow-x-auto">
                @foreach ($itens as $item)
                    @php $ativo = request()->is(ltrim($item['rota'], '/').'*'); @endphp
                    <li class="shrink-0">
                        <a href="{{ $item['rota'] }}"
                           @if ($ativo) aria-current="page" @endif
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors
                                  {{ $ativo
                                     ? 'bg-white/15 text-white font-bold'
                                     : 'text-neutroClaro hover:bg-white/10 hover:text-white' }}">
                            <span class="h-5 w-0.5 rounded-full {{ $ativo ? 'bg-verde' : 'bg-transparent' }}"></span>
                            {{ $item['texto'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

        </div>
    </nav>

    {{-- Conteúdo --}}
    <main class="flex-1 min-w-0">
        <div class="max-w-5xl px-6 py-10 lg:px-12 lg:py-14">

            <header class="mb-10">
                <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-3">
                    <div class="min-w-0">
                        <h1 class="font-marca font-bold text-3xl tracking-tight text-verdeEscuro">
                            @yield('titulo')
                        </h1>
                        @hasSection('descricao')
                            <p class="mt-2 text-neutroEscuro max-w-prose">@yield('descricao')</p>
                        @endif
                    </div>
                    <div class="shrink-0">@yield('acao')</div>
                </div>
            </header>

            @yield('conteudo')

        </div>
    </main>

</div>

<script>
    /*
     * Utilitários compartilhados pelas telas. Ficam aqui para não repetir
     * formatação de moeda, máscara de CPF e exibição de erro em cada view.
     */
    window.Coop = {
        /* E-mails gerados no fluxo de análise não são endereços reais. */
        dominioEmailProvisorio: @json(\App\Models\Cliente::DOMINIO_EMAIL_PROVISORIO),

        emailProvisorio(email) {
            return String(email ?? '').endsWith(`@${window.Coop.dominioEmailProvisorio}`);
        },

        moeda: new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }),

        percentual(fracao) {
            return `${(fracao * 100).toLocaleString('pt-BR', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            })}%`;
        },

        mascararCpf(valor) {
            const digitos = String(valor ?? '').replace(/\D/g, '').slice(0, 11);

            return digitos
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
        },

        /* Liga a máscara a um input de CPF. */
        ligarMascaraCpf(input) {
            if (!input) return;
            input.addEventListener('input', (evento) => {
                evento.target.value = window.Coop.mascararCpf(evento.target.value);
            });
        },

        /* Limpa mensagens de erro de campo e o alerta geral do formulário. */
        limparErros(form, alerta) {
            alerta?.classList.add('hidden');

            form.querySelectorAll('[data-erro]').forEach((campo) => {
                campo.classList.add('hidden');
                campo.textContent = '';
            });

            form.querySelectorAll('input, select').forEach((campo) => {
                campo.classList.remove('border-magenta');
            });
        },

        /* Distribui os erros de validação do Laravel nos campos do formulário. */
        exibirErros(form, erros) {
            Object.entries(erros ?? {}).forEach(([campo, mensagens]) => {
                const destino = form.querySelector(`[data-erro="${campo}"]`);
                const input = form.querySelector(`[name="${campo}"]`);

                if (destino) {
                    destino.textContent = mensagens[0];
                    destino.classList.remove('hidden');
                }

                input?.classList.add('border-magenta');
            });
        },

        /* Envia JSON e devolve { resposta, corpo } já desserializado. */
        async enviar(url, opcoes = {}) {
            const resposta = await fetch(url, {
                ...opcoes,
                headers: {
                    Accept: 'application/json',
                    ...(opcoes.body ? { 'Content-Type': 'application/json' } : {}),
                    ...opcoes.headers,
                },
            });

            const corpo = await resposta.json().catch(() => ({}));

            return { resposta, corpo };
        },
    };
</script>

@stack('scripts')

</body>
</html>
