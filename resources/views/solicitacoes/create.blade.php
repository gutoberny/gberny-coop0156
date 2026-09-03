@extends('layouts.app')

@section('titulo', 'Nova solicitação')
@section('descricao', 'Informe os dados do proponente. O score é consultado no Bureau de Crédito no momento do envio.')

@section('acao')
    <a href="/solicitacoes" class="text-sm font-semibold text-verdeEscuro hover:text-verde">Voltar para solicitações</a>
@endsection

@section('conteudo')

    <div class="grid gap-12 lg:grid-cols-2">

        {{-- Formulário --}}
        <section>
            <div id="alerta" class="mb-6 hidden rounded-lg bg-magenta/10 p-4 text-sm" role="alert" aria-live="assertive">
                <p class="font-bold text-magenta">Não foi possível concluir</p>
                <p id="alerta-mensagem" class="mt-1 text-tinta"></p>
            </div>

            <form id="form" class="space-y-6" novalidate>

                <div>
                    <label for="nome" class="mb-1.5 block text-sm font-bold">Nome do proponente</label>
                    <input type="text" id="nome" name="nome" required autocomplete="name"
                           class="w-full rounded-lg border border-neutroClaro px-4 py-2.5 focus:border-verde focus:outline-none">
                    <p data-erro="nome" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="cpf" class="mb-1.5 block text-sm font-bold">CPF</label>
                        <input type="text" id="cpf" name="cpf" required inputmode="numeric" maxlength="14" placeholder="000.000.000-00"
                               class="numero w-full rounded-lg border border-neutroClaro px-4 py-2.5 focus:border-verde focus:outline-none">
                        <p data-erro="cpf" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
                    </div>

                    <div>
                        <label for="renda_mensal" class="mb-1.5 block text-sm font-bold">Renda mensal</label>
                        <input type="number" id="renda_mensal" name="renda_mensal" required step="0.01" min="0" placeholder="3500,00"
                               class="numero w-full rounded-lg border border-neutroClaro px-4 py-2.5 focus:border-verde focus:outline-none">
                        <p data-erro="renda_mensal" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="tipo_credito" class="mb-1.5 block text-sm font-bold">Tipo de crédito</label>
                        <select id="tipo_credito" name="tipo_credito" required
                                class="w-full rounded-lg border border-neutroClaro bg-white px-4 py-2.5 focus:border-verde focus:outline-none">
                            <option value="" disabled selected>Selecione</option>
                            <option value="pessoal">Pessoal</option>
                            <option value="imobiliario">Imobiliário</option>
                            <option value="automotivo">Automotivo</option>
                        </select>
                        <p data-erro="tipo_credito" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
                    </div>

                    <div>
                        <label for="valor_solicitado" class="mb-1.5 block text-sm font-bold">Valor solicitado</label>
                        <input type="number" id="valor_solicitado" name="valor_solicitado" required step="0.01" min="0.01" placeholder="15000,00"
                               class="numero w-full rounded-lg border border-neutroClaro px-4 py-2.5 focus:border-verde focus:outline-none">
                        <p data-erro="valor_solicitado" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
                    </div>
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-bold">
                        E-mail <span class="font-normal text-neutroEscuro">— opcional</span>
                    </label>
                    <input type="email" id="email" name="email" autocomplete="email"
                           class="w-full rounded-lg border border-neutroClaro px-4 py-2.5 focus:border-verde focus:outline-none">
                    <p data-erro="email" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
                    <p class="mt-1.5 text-xs text-neutroEscuro">
                        Se o CPF ainda não tiver cadastro, o cliente é criado com estes dados.
                    </p>
                </div>

                {{-- Prévia calculada no navegador, antes de consultar o Bureau --}}
                <div id="previa" class="hidden rounded-lg bg-neutroClaro/40 p-4">
                    <p class="text-sm font-bold text-verdeEscuro">Estimativa</p>
                    <p class="mt-1 text-xs text-neutroEscuro">
                        Calculada na taxa de 4,5%. A taxa final depende do score.
                    </p>
                    <dl class="mt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-neutroEscuro">Parcela</dt>
                            <dd id="previa-parcela" class="numero font-marca font-semibold"></dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-neutroEscuro">Da renda mensal</dt>
                            <dd id="previa-comprometimento" class="numero font-semibold"></dd>
                        </div>
                    </dl>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" id="enviar"
                            class="rounded-lg bg-verde px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro disabled:cursor-not-allowed disabled:opacity-60">
                        Solicitar análise
                    </button>
                    <a href="/solicitacoes" class="text-sm font-semibold text-neutroEscuro hover:text-tinta">Cancelar</a>
                </div>

            </form>
        </section>

        {{-- Resultado --}}
        <section aria-live="polite">

            <div id="aguardando" class="rounded-xl border border-dashed border-neutroClaro px-6 py-16 text-center">
                <p class="font-marca text-lg font-semibold text-verdeEscuro">O resultado aparece aqui</p>
                <p class="mx-auto mt-2 max-w-xs text-sm text-neutroEscuro">
                    Ao enviar, consultamos o score no Bureau e aplicamos as regras de elegibilidade.
                </p>
            </div>

            <div id="resultado" class="hidden rounded-xl border border-neutroClaro">
                <div class="flex items-start justify-between gap-4 border-b border-neutroClaro px-6 py-5">
                    <div>
                        <p id="res-nome" class="font-marca text-lg font-semibold text-verdeEscuro"></p>
                        <p id="res-cpf" class="numero mt-0.5 text-sm text-neutroEscuro"></p>
                    </div>
                    <span id="res-badge"></span>
                </div>

                <dl class="divide-y divide-neutroClaro px-6">
                    <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                        <dt class="text-neutroEscuro">Score no Bureau</dt>
                        <dd id="res-score" class="numero font-semibold"></dd>
                    </div>

                    {{-- Aprovado --}}
                    <div id="bloco-aprovado" class="hidden divide-y divide-neutroClaro">
                        <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                            <dt class="text-neutroEscuro">Taxa aplicada</dt>
                            <dd id="res-taxa" class="numero font-semibold"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3.5">
                            <dt class="text-sm text-neutroEscuro">Parcela mensal</dt>
                            <dd id="res-parcela" class="numero font-marca text-xl font-bold text-verdeEscuro"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                            <dt class="text-neutroEscuro">Total a pagar</dt>
                            <dd id="res-total" class="numero font-semibold"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                            <dt class="text-neutroEscuro">Da renda mensal</dt>
                            <dd id="res-comprometimento" class="numero font-semibold"></dd>
                        </div>
                    </div>

                    {{-- Reprovado --}}
                    <div id="bloco-reprovado" class="hidden py-4">
                        <dt class="text-sm font-bold text-magenta">Motivo da recusa</dt>
                        <dd id="res-motivo" class="mt-1 text-sm"></dd>
                    </div>
                </dl>

                <div id="acao-simular" class="hidden border-t border-neutroClaro px-6 py-5">
                    <a id="link-simulacao" href="#"
                       class="block rounded-lg bg-verde px-5 py-3 text-center text-sm font-bold text-white transition-colors hover:bg-verdeEscuro">
                        Ver simulação e contratar
                    </a>
                    <p class="mt-3 text-center text-xs text-neutroEscuro">
                        As condições completas ficam na tela de simulação.
                    </p>
                </div>
            </div>

        </section>

    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const PARCELAS = 12;
        const COMPROMETIMENTO_MAXIMO = 0.30;
        const TAXA_ESTIMATIVA = 0.045;

        const el = (id) => document.getElementById(id);
        const form = el('form');
        const enviar = el('enviar');
        const alerta = el('alerta');

        window.Coop.ligarMascaraCpf(el('cpf'));

        const avisar = (mensagem) => {
            el('alerta-mensagem').textContent = mensagem;
            alerta.classList.remove('hidden');
        };

        // -------------------------------------------------------------
        // Estimativa da parcela enquanto o usuário digita
        // -------------------------------------------------------------
        const atualizarPrevia = () => {
            const renda = parseFloat(el('renda_mensal').value);
            const valor = parseFloat(el('valor_solicitado').value);

            if (!Number.isFinite(renda) || !Number.isFinite(valor) || renda <= 0 || valor <= 0) {
                el('previa').classList.add('hidden');
                return;
            }

            const parcela = (valor + valor * TAXA_ESTIMATIVA * PARCELAS) / PARCELAS;
            const comprometimento = parcela / renda;

            el('previa-parcela').textContent = window.Coop.moeda.format(parcela);

            const alvo = el('previa-comprometimento');
            alvo.textContent = window.Coop.percentual(comprometimento);
            alvo.classList.toggle('text-magenta', comprometimento > COMPROMETIMENTO_MAXIMO);

            el('previa').classList.remove('hidden');
        };

        ['renda_mensal', 'valor_solicitado'].forEach((campo) => {
            el(campo).addEventListener('input', atualizarPrevia);
        });

        // -------------------------------------------------------------
        // Resultado
        // -------------------------------------------------------------
        const exibirResultado = (analise) => {
            const aprovado = analise.status === 'aprovado';

            el('res-nome').textContent = analise.nome;
            el('res-cpf').textContent = window.Coop.mascararCpf(analise.cpf);
            el('res-score').textContent = analise.score ?? 'Não informado';

            el('res-badge').innerHTML = aprovado
                ? '<span class="inline-flex items-center rounded-full bg-neutroClaro px-2.5 py-1 text-xs font-bold text-verdeEscuro">Aprovado</span>'
                : '<span class="inline-flex items-center rounded-full bg-magenta/10 px-2.5 py-1 text-xs font-bold text-magenta">Reprovado</span>';

            el('bloco-aprovado').classList.toggle('hidden', !aprovado);
            el('bloco-reprovado').classList.toggle('hidden', aprovado);
            el('acao-simular').classList.toggle('hidden', !aprovado);

            if (aprovado) {
                el('res-taxa').textContent = `${analise.taxa_juros.toLocaleString('pt-BR', { minimumFractionDigits: 1 })}% ao mês`;
                el('res-parcela').textContent = window.Coop.moeda.format(analise.valor_parcela);
                el('res-total').textContent = window.Coop.moeda.format(analise.valor_total);
                el('res-comprometimento').textContent = window.Coop.percentual(analise.valor_parcela / analise.renda_mensal);
                el('link-simulacao').href = analise.url_simulacao ?? `/simulacao/${analise.id}`;
            } else {
                el('res-motivo').textContent = analise.motivo_rejeicao;
            }

            el('aguardando').classList.add('hidden');
            el('resultado').classList.remove('hidden');
        };

        // -------------------------------------------------------------
        // Envio
        // -------------------------------------------------------------
        form.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            window.Coop.limparErros(form, alerta);

            const dados = Object.fromEntries(new FormData(form).entries());

            if (!dados.email) {
                delete dados.email;
            }

            enviar.disabled = true;
            enviar.textContent = 'Consultando o Bureau…';

            try {
                const { resposta, corpo } = await window.Coop.enviar('/api/analise-credito', {
                    method: 'POST',
                    body: JSON.stringify(dados),
                });

                if (resposta.status === 422) {
                    window.Coop.exibirErros(form, corpo.errors);
                    avisar('Revise os campos destacados e envie de novo.');
                    return;
                }

                if (!resposta.ok) {
                    avisar(corpo.message ?? 'Não foi possível concluir a análise. Tente novamente em instantes.');
                    return;
                }

                exibirResultado(corpo.data);
            } catch (erro) {
                avisar('Sem conexão com o servidor. Verifique sua rede e tente novamente.');
            } finally {
                enviar.disabled = false;
                enviar.textContent = 'Solicitar análise';
            }
        });
    });
</script>
@endpush
