<?php

namespace App\Services;

use App\Enums\StatusAnalise;
use App\Exceptions\AnaliseNaoContratavelException;
use App\Exceptions\BureauIndisponivelException;
use App\Jobs\ProcessarContratacaoJob;
use App\Models\AnaliseCredito;
use App\Models\Cliente;
use App\Support\PoliticaCredito;

/**
 * Orquestra o caso de uso de análise de crédito.
 *
 * Responsabilidade: coordenar as peças (cliente, Bureau, política de
 * crédito) e persistir o resultado. As regras vivem em PoliticaCredito e
 * o HTTP vive em BureauCreditoClient — aqui só há fluxo.
 */
class AnaliseCreditoService
{
    public function __construct(
        private readonly BureauCreditoClient $bureau,
        private readonly PoliticaCredito $politica,
    ) {}

    /**
     * Executa a análise completa: resolve o cliente, registra a solicitação,
     * consulta o Bureau e aplica as regras de elegibilidade.
     *
     * @param  array<string, mixed>  $dados
     *
     * @throws BureauIndisponivelException a análise fica em 'pendente',
     *                                     permitindo nova tentativa.
     */
    public function solicitar(array $dados): AnaliseCredito
    {
        $cliente = $this->resolverCliente($dados);

        $analise = $this->registrarSolicitacao($cliente, $dados);

        // Consultamos o Bureau sempre, na ordem descrita no enunciado, para
        // que o score fique registrado mesmo quando a reprovação vem da renda.
        $score = $this->bureau->consultarScore($analise->cpf);

        $resultado = $this->politica->avaliar(
            rendaMensal: (float) $analise->renda_mensal,
            score: $score,
            valorSolicitado: (float) $analise->valor_solicitado,
        );

        $analise->update([
            'score' => $score,
            ...$resultado->toAttributes(),
        ]);

        return $analise->refresh();
    }

    /**
     * Confirma a contratação de uma análise aprovada.
     *
     * A finalização é assíncrona: a análise entra em
     * 'processando_contratacao' e o Job conclui para 'contratado'.
     *
     * @throws AnaliseNaoContratavelException
     */
    public function contratar(AnaliseCredito $analise): AnaliseCredito
    {
        if (! $analise->status->permiteSimulacao()) {
            throw new AnaliseNaoContratavelException($analise->status);
        }

        $analise->update(['status' => StatusAnalise::PROCESSANDO_CONTRATACAO]);

        ProcessarContratacaoJob::dispatch($analise->id);

        return $analise->refresh();
    }

    /**
     * Localiza o cliente pelo CPF ou o cadastra com os dados recebidos.
     *
     * @param  array<string, mixed>  $dados
     */
    private function resolverCliente(array $dados): Cliente
    {
        $cliente = Cliente::firstOrCreate(
            ['cpf' => $dados['cpf']],
            [
                'nome' => $dados['nome'],
                'email' => $dados['email'] ?? $this->emailProvisorio($dados['cpf']),
                'telefone' => $dados['telefone'] ?? null,
                'renda_mensal' => $dados['renda_mensal'],
            ],
        );

        // Cliente recorrente que informou uma renda diferente: a renda atual
        // é o dado mais recente e passa a valer no cadastro.
        if (! $cliente->wasRecentlyCreated
            && (float) $cliente->renda_mensal !== (float) $dados['renda_mensal']) {
            $cliente->update(['renda_mensal' => $dados['renda_mensal']]);
        }

        return $cliente;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function registrarSolicitacao(Cliente $cliente, array $dados): AnaliseCredito
    {
        return $cliente->analises()->create([
            'cpf' => $dados['cpf'],
            'nome' => $dados['nome'],
            'renda_mensal' => $dados['renda_mensal'],
            'tipo_credito' => $dados['tipo_credito'],
            'valor_solicitado' => $dados['valor_solicitado'],
            'status' => StatusAnalise::PENDENTE,
        ]);
    }

    /**
     * O fluxo de solicitação não exige e-mail, mas a tabela de clientes o
     * requer e o mantém único. Derivamos um endereço determinístico a partir
     * do CPF para que o mesmo CPF nunca gere um cliente duplicado.
     */
    private function emailProvisorio(string $cpf): string
    {
        return "{$cpf}@nao-informado.coop0156.local";
    }
}
