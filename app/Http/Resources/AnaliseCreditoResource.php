<?php

namespace App\Http\Resources;

use App\Models\AnaliseCredito;
use App\Support\PoliticaCredito;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AnaliseCredito
 */
class AnaliseCreditoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->cliente_id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'renda_mensal' => (float) $this->renda_mensal,
            'tipo_credito' => $this->tipo_credito->value,
            'valor_solicitado' => (float) $this->valor_solicitado,
            'status' => $this->status->value,
            'score' => $this->score,
            'taxa_juros' => $this->taxa_juros !== null ? (float) $this->taxa_juros : null,
            'valor_parcela' => $this->valor_parcela !== null ? (float) $this->valor_parcela : null,
            'parcelas' => PoliticaCredito::PARCELAS,
            'valor_total' => $this->valorTotal(),
            'motivo_rejeicao' => $this->motivo_rejeicao,
            'url_simulacao' => $this->status->permiteSimulacao()
                ? url("/simulacao/{$this->id}")
                : null,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Total a pagar ao longo das parcelas — só faz sentido quando aprovada.
     *
     * Derivado do valor solicitado e da taxa, não da parcela já arredondada,
     * para bater com o exemplo do enunciado (R$ 10.000 a 2,9% → R$ 13.480,00
     * e não 12 × R$ 1.123,33 = R$ 13.479,96).
     */
    private function valorTotal(): ?float
    {
        if ($this->taxa_juros === null) {
            return null;
        }

        $valorSolicitado = (float) $this->valor_solicitado;
        $jurosTotais = $valorSolicitado * ((float) $this->taxa_juros / 100) * PoliticaCredito::PARCELAS;

        return round($valorSolicitado + $jurosTotais, 2);
    }
}
