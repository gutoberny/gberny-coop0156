<?php

namespace App\Jobs;

use App\Enums\StatusAnalise;
use App\Models\AnaliseCredito;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Finaliza a contratação de forma assíncrona.
 *
 * O endpoint de contratação apenas move a análise para
 * 'processando_contratacao' e devolve a resposta imediatamente; este Job
 * conclui o processo para 'contratado'. Num sistema real seria aqui que
 * ficariam a averbação, a emissão do contrato e a notificação ao cliente.
 *
 * Recebe o ID em vez do model para não serializar estado que pode estar
 * desatualizado quando o worker pegar o Job.
 */
class ProcessarContratacaoJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** Número de tentativas antes de considerar o Job falho. */
    public int $tries = 3;

    /** Espera progressiva entre as tentativas, em segundos. */
    public function backoff(): array
    {
        return [5, 15];
    }

    public function __construct(public int $analiseId)
    {
        //
    }

    public function handle(): void
    {
        $analise = AnaliseCredito::find($this->analiseId);

        if ($analise === null) {
            Log::warning('Contratação ignorada: análise inexistente.', [
                'analise_id' => $this->analiseId,
            ]);

            return;
        }

        // Guarda de idempotência: se o worker reprocessar o Job, uma análise
        // já contratada não é alterada de novo.
        if ($analise->status === StatusAnalise::CONTRATADO) {
            Log::info('Contratação já concluída anteriormente; nada a fazer.', [
                'analise_id' => $analise->id,
            ]);

            return;
        }

        $analise->update(['status' => StatusAnalise::CONTRATADO]);

        Log::info('Contratação de crédito concluída com sucesso.', [
            'analise_id' => $analise->id,
            'cliente_id' => $analise->cliente_id,
            'cpf' => $analise->cpf,
            'valor_solicitado' => (float) $analise->valor_solicitado,
            'valor_parcela' => (float) $analise->valor_parcela,
            'taxa_juros' => (float) $analise->taxa_juros,
        ]);
    }

    /**
     * Registra a falha definitiva após esgotadas as tentativas.
     */
    public function failed(?\Throwable $e): void
    {
        Log::error('Falha ao processar a contratação de crédito.', [
            'analise_id' => $this->analiseId,
            'erro' => $e?->getMessage(),
        ]);
    }
}
