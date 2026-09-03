<?php

namespace App\Support;

use App\DataTransferObjects\ResultadoAnalise;

/**
 * Motor de regras de elegibilidade de crédito.
 *
 * Peça deliberadamente pura: não acessa banco, HTTP nem configuração.
 * Recebe os números da proposta e devolve a decisão, o que a torna
 * testável isoladamente (ver tests/Unit/PoliticaCreditoTest.php).
 */
class PoliticaCredito
{
    /** Renda mensal mínima para elegibilidade. */
    public const RENDA_MINIMA = 1500.00;

    /** Score abaixo deste valor reprova a proposta. */
    public const SCORE_MINIMO = 400;

    /** Score a partir do qual o proponente recebe a taxa preferencial. */
    public const SCORE_PREFERENCIAL = 700;

    /** Taxa mensal aplicada à faixa de score 400–699. */
    public const TAXA_PADRAO = 4.5;

    /** Taxa mensal aplicada à faixa de score >= 700. */
    public const TAXA_PREFERENCIAL = 2.9;

    /** Número fixo de parcelas do produto. */
    public const PARCELAS = 12;

    /** Fração máxima da renda que a parcela pode comprometer. */
    public const COMPROMETIMENTO_MAXIMO = 0.30;

    public const MOTIVO_RENDA_INSUFICIENTE = 'Renda mínima insuficiente';

    public const MOTIVO_SCORE_BAIXO = 'Score de crédito muito baixo';

    public const MOTIVO_COMPROMETIMENTO = 'Comprometimento de renda superior a 30%';

    /**
     * Avalia a proposta e devolve a decisão de crédito.
     */
    public function avaliar(float $rendaMensal, int $score, float $valorSolicitado): ResultadoAnalise
    {
        if ($rendaMensal < self::RENDA_MINIMA) {
            return ResultadoAnalise::reprovado(self::MOTIVO_RENDA_INSUFICIENTE);
        }

        if ($score < self::SCORE_MINIMO) {
            return ResultadoAnalise::reprovado(self::MOTIVO_SCORE_BAIXO);
        }

        $taxaJuros = $this->taxaPara($score);
        $valorParcela = $this->calcularParcela($valorSolicitado, $taxaJuros);

        if ($valorParcela > $this->comprometimentoMaximo($rendaMensal)) {
            return ResultadoAnalise::reprovado(self::MOTIVO_COMPROMETIMENTO);
        }

        return ResultadoAnalise::aprovado($taxaJuros, $valorParcela);
    }

    /**
     * Taxa mensal correspondente à faixa de score.
     */
    public function taxaPara(int $score): float
    {
        return $score >= self::SCORE_PREFERENCIAL
            ? self::TAXA_PREFERENCIAL
            : self::TAXA_PADRAO;
    }

    /**
     * Valor da parcela em juros simples sobre o valor solicitado.
     *
     * Ex.: R$ 10.000,00 a 2,9% a.m. em 12x
     *   juros total = 10.000 × 0,029 × 12 = 3.480,00
     *   total       = 13.480,00
     *   parcela     = 1.123,33
     */
    public function calcularParcela(float $valorSolicitado, float $taxaJuros): float
    {
        $jurosTotais = $valorSolicitado * ($taxaJuros / 100) * self::PARCELAS;

        return round(($valorSolicitado + $jurosTotais) / self::PARCELAS, 2);
    }

    /**
     * Maior parcela admissível para a renda informada.
     */
    public function comprometimentoMaximo(float $rendaMensal): float
    {
        return round($rendaMensal * self::COMPROMETIMENTO_MAXIMO, 2);
    }
}
