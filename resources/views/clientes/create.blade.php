@extends('layouts.app')

@section('titulo', 'Novo cliente')
@section('descricao', 'Dados da pessoa associada. O CPF e o e-mail não podem repetir um cadastro existente.')

@section('acao')
    <a href="/clientes" class="text-sm font-semibold text-verdeEscuro hover:text-verde">Voltar para clientes</a>
@endsection

@section('conteudo')

    <div id="alerta" class="mb-6 hidden rounded-lg p-4 text-sm font-semibold" role="alert" aria-live="assertive"></div>

    <form id="form" class="max-w-xl space-y-6" novalidate>

        <div>
            <label for="nome" class="mb-1.5 block text-sm font-bold">Nome completo</label>
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

        <div>
            <label for="email" class="mb-1.5 block text-sm font-bold">E-mail</label>
            <input type="email" id="email" name="email" required autocomplete="email"
                   class="w-full rounded-lg border border-neutroClaro px-4 py-2.5 focus:border-verde focus:outline-none">
            <p data-erro="email" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
        </div>

        <div>
            <label for="telefone" class="mb-1.5 block text-sm font-bold">
                Telefone <span class="font-normal text-neutroEscuro">— opcional</span>
            </label>
            <input type="text" id="telefone" name="telefone" autocomplete="tel"
                   class="numero w-full rounded-lg border border-neutroClaro px-4 py-2.5 focus:border-verde focus:outline-none">
            <p data-erro="telefone" class="mt-1.5 hidden text-xs font-semibold text-magenta"></p>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" id="salvar"
                    class="rounded-lg bg-verde px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-verdeEscuro disabled:cursor-not-allowed disabled:opacity-60">
                Cadastrar cliente
            </button>
            <a href="/clientes" class="text-sm font-semibold text-neutroEscuro hover:text-tinta">Cancelar</a>
        </div>

    </form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('form');
        const salvar = document.getElementById('salvar');
        const alerta = document.getElementById('alerta');

        window.Coop.ligarMascaraCpf(document.getElementById('cpf'));

        const avisar = (mensagem, tipo = 'erro') => {
            const estilos = {
                erro: 'bg-magenta/10 text-magenta',
                sucesso: 'bg-neutroClaro text-verdeEscuro',
            };

            alerta.className = `mb-6 rounded-lg p-4 text-sm font-semibold ${estilos[tipo]}`;
            alerta.textContent = mensagem;
            alerta.classList.remove('hidden');
        };

        form.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            window.Coop.limparErros(form, alerta);

            const dados = Object.fromEntries(new FormData(form).entries());
            dados.cpf = String(dados.cpf).replace(/\D/g, '');

            if (!dados.telefone) {
                delete dados.telefone;
            }

            salvar.disabled = true;
            salvar.textContent = 'Cadastrando…';

            try {
                const { resposta, corpo } = await window.Coop.enviar('/api/clientes', {
                    method: 'POST',
                    body: JSON.stringify(dados),
                });

                if (resposta.status === 422) {
                    window.Coop.exibirErros(form, corpo.errors);
                    avisar('Revise os campos destacados.');
                    return;
                }

                if (!resposta.ok) {
                    avisar(corpo.message ?? 'Não foi possível cadastrar o cliente. Tente novamente.');
                    return;
                }

                /* Cadastro concluído: a lista mostra o registro novo. */
                window.location.href = '/clientes';
            } catch (erro) {
                avisar('Sem conexão com o servidor. Verifique sua rede e tente novamente.');
            } finally {
                salvar.disabled = false;
                salvar.textContent = 'Cadastrar cliente';
            }
        });
    });
</script>
@endpush
