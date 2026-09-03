@extends('layouts.app')

@section('titulo', 'Simulação de crédito')
@section('descricao', 'Confira as condições aprovadas antes de confirmar a contratação.')

@section('acao')
    <a href="/solicitacoes" class="text-sm font-semibold text-verdeEscuro hover:text-verde">Voltar para solicitações</a>
@endsection

@section('conteudo')

@php
    $comprometimento = $analise->valor_parcela / $analise->renda_mensal;
    $valorTotal = $analise->valor_parcela * 12;
@endphp

<div class="max-w-2xl space-y-8">

    @if (session('erro'))
        <div class="rounded-lg bg-magenta/10 p-4 text-sm font-semibold text-magenta" role="alert">
            {{ session('erro') }}
        </div>
    @endif

    <div id="alerta" class="hidden rounded-lg bg-magenta/10 p-4 text-sm" role="alert" aria-live="assertive">
        <p class="font-bold text-magenta">Não foi possível concluir</p>
        <p id="alerta-mensagem" class="mt-1 text-tinta"></p>
    </div>

    {{-- Condição aprovada: a parcela é o número que importa --}}
    <section class="rounded-xl border border-neutroClaro">
        <div class="border-b border-neutroClaro bg-neutroClaro/30 px-6 py-6">
            <p class="text-sm text-neutroEscuro">Parcela mensal</p>
            <p class="numero mt-1 font-marca text-4xl font-bold tracking-tight text-verdeEscuro">
                {{ 'R$ '.number_format($analise->valor_parcela, 2, ',', '.') }}
            </p>
            <p class="mt-2 text-sm text-neutroEscuro">
                12 parcelas fixas a {{ number_format($analise->taxa_juros, 1, ',', '.') }}% ao mês,
                comprometendo {{ number_format($comprometimento * 100, 1, ',', '.') }}% da renda declarada.
            </p>
        </div>

        <dl class="divide-y divide-neutroClaro px-6">
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">Valor solicitado</dt>
                <dd class="numero font-semibold">{{ 'R$ '.number_format($analise->valor_solicitado, 2, ',', '.') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">Total a pagar</dt>
                <dd class="numero font-semibold">{{ 'R$ '.number_format($valorTotal, 2, ',', '.') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">Tipo de crédito</dt>
                <dd class="font-semibold">{{ ucfirst($analise->tipo_credito->value) }}</dd>
            </div>
        </dl>
    </section>

    {{-- Proponente --}}
    <section class="rounded-xl border border-neutroClaro px-6 py-2">
        <dl class="divide-y divide-neutroClaro">
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">Proponente</dt>
                <dd class="font-semibold">{{ $analise->nome }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">CPF</dt>
                <dd class="numero font-semibold">{{ $analise->cpf }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">Renda mensal</dt>
                <dd class="numero font-semibold">{{ 'R$ '.number_format($analise->renda_mensal, 2, ',', '.') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">Score no Bureau</dt>
                <dd class="numero font-semibold">{{ $analise->score }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                <dt class="text-neutroEscuro">Situação</dt>
                <dd>@include('partials.badge-status', ['status' => $analise->status])</dd>
            </div>
        </dl>
    </section>

    {{-- Contratação --}}
    <section class="rounded-xl bg-neutroClaro/40 px-6 py-6">
        <h2 class="font-marca text-lg font-semibold text-verdeEscuro">Confirmar contratação</h2>
        <p class="mt-2 max-w-prose text-sm text-neutroEscuro">
            A contratação segue para processamento e não pode ser desfeita.
        </p>

        <div class="mt-6 flex flex-wrap items-center gap-4">
            <button type="button" id="confirmar"
                    class="rounded-lg bg-verde px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro disabled:cursor-not-allowed disabled:opacity-60">
                Confirmar contratação
            </button>
            <a href="/solicitacoes" class="text-sm font-semibold text-neutroEscuro hover:text-tinta">Cancelar</a>
        </div>
    </section>

</div>

{{-- Confirmação --}}
<div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-tinta/60 p-6"
     role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
    <div class="w-full max-w-md rounded-xl bg-white p-8 text-center">
        <h2 id="modal-titulo" class="font-marca text-2xl font-bold text-verdeEscuro">Contratação confirmada</h2>
        <p class="mt-3 text-sm text-neutroEscuro">
            O crédito entrou em processamento. A situação muda para contratado assim que a fila concluir.
        </p>
        <div class="mt-8 flex flex-col gap-3">
            <a href="/solicitacoes" class="rounded-lg bg-verde px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro">
                Ver solicitações
            </a>
            <a href="/solicitacoes/nova" class="text-sm font-semibold text-neutroEscuro hover:text-tinta">
                Nova solicitação
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ANALISE_ID = @json($analise->id);

        const el = (id) => document.getElementById(id);
        const confirmar = el('confirmar');
        const alerta = el('alerta');
        const modal = el('modal');

        let emAndamento = false;

        const avisar = (mensagem) => {
            el('alerta-mensagem').textContent = mensagem;
            alerta.classList.remove('hidden');
        };

        const abrirModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.querySelector('a')?.focus();
        };

        confirmar.addEventListener('click', async () => {
            /* A contratação não é reversível: um clique só. */
            if (emAndamento) return;

            emAndamento = true;
            alerta.classList.add('hidden');
            confirmar.disabled = true;
            confirmar.textContent = 'Processando…';

            try {
                const { resposta, corpo } = await window.Coop.enviar(
                    `/api/analise-credito/${ANALISE_ID}/contratar`,
                    { method: 'POST' },
                );

                if (!resposta.ok) {
                    avisar(corpo.message ?? 'Não foi possível concluir a contratação. Tente novamente em instantes.');
                    emAndamento = false;
                    confirmar.disabled = false;
                    confirmar.textContent = 'Confirmar contratação';
                    return;
                }

                /* Sucesso: a análise saiu do estado contratável, o botão fica travado. */
                confirmar.textContent = 'Contratação enviada';
                abrirModal();
            } catch (erro) {
                avisar('Sem conexão com o servidor. Verifique sua rede e tente novamente.');
                emAndamento = false;
                confirmar.disabled = false;
                confirmar.textContent = 'Confirmar contratação';
            }
        });
    });
</script>
@endpush
