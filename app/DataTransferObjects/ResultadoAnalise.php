<?php

namespace App\DataTransferObjects;

use App\Enums\StatusAnalise;

/**
 * Resultado da avaliação de crédito produzido pela PoliticaCredito.
 *
 * Em aprovações, taxa e parcela estão preenchidos e o motivo é nulo.
 * Em reprovações, o inverso.
 */
readonly class ResultadoAnalise
{
    public function __construct(
        public StatusAnalise $status,
        public ?float $taxaJuros = null,
        public ?float $valorParcela = null,
        public ?string $motivoRejeicao = null,
    ) {}

    public static function aprovado(float $taxaJuros, float $valorParcela): self
    {
        return new self(
            status: StatusAnalise::APROVADO,
            taxaJuros: $taxaJuros,
            valorParcela: $valorParcela,
        );
    }

    public static function reprovado(string $motivo): self
    {
        return new self(
            status: StatusAnalise::REPROVADO,
            motivoRejeicao: $motivo,
        );
    }

    public function aprovada(): bool
    {
        return $this->status === StatusAnalise::APROVADO;
    }

    /**
     * Atributos prontos para persistir na análise.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'status' => $this->status,
            'taxa_juros' => $this->taxaJuros,
            'valor_parcela' => $this->valorParcela,
            'motivo_rejeicao' => $this->motivoRejeicao,
        ];
    }
}
