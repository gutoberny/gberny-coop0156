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
     */
    private function valorTotal(): ?float
    {
        if ($this->valor_parcela === null) {
            return null;
        }

        return round((float) $this->valor_parcela * PoliticaCredito::PARCELAS, 2);
    }
}
