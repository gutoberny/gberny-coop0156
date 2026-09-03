<?php

namespace Tests\Unit;

use App\Enums\StatusAnalise;
use App\Support\PoliticaCredito;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A PoliticaCredito não toca banco nem HTTP, então testa como unidade pura,
 * sem bootstrap do Laravel.
 */
class PoliticaCreditoTest extends TestCase
{
    private PoliticaCredito $politica;

    protected function setUp(): void
    {
        parent::setUp();
        $this->politica = new PoliticaCredito;
    }

    public function test_calcula_parcela_com_juros_simples_conforme_exemplo_do_enunciado(): void
    {
        // R$ 10.000,00 a 2,9% a.m. em 12x → parcela de R$ 1.123,33
        $this->assertSame(1123.33, $this->politica->calcularParcela(10000.00, 2.9));
    }

    public function test_aprova_score_alto_com_taxa_preferencial(): void
    {
        $resultado = $this->politica->avaliar(
            rendaMensal: 10000.00,
            score: 850,
            valorSolicitado: 10000.00,
        );

        $this->assertTrue($resultado->aprovada());
        $this->assertSame(StatusAnalise::APROVADO, $resultado->status);
        $this->assertSame(2.9, $resultado->taxaJuros);
        $this->assertSame(1123.33, $resultado->valorParcela);
        $this->assertNull($resultado->motivoRejeicao);
    }

    public function test_aprova_score_medio_com_taxa_padrao(): void
    {
        $resultado = $this->politica->avaliar(
            rendaMensal: 10000.00,
            score: 550,
            valorSolicitado: 10000.00,
        );

        $this->assertTrue($resultado->aprovada());
        $this->assertSame(4.5, $resultado->taxaJuros);
        // 10.000 + (10.000 × 4,5% × 12) = 15.400 → 15.400 / 12
        $this->assertSame(1283.33, $resultado->valorParcela);
    }

    public function test_reprova_por_renda_insuficiente(): void
    {
        $resultado = $this->politica->avaliar(
            rendaMensal: 1000.00,
            score: 850,
            valorSolicitado: 1000.00,
        );

        $this->assertFalse($resultado->aprovada());
        $this->assertSame(StatusAnalise::REPROVADO, $resultado->status);
        $this->assertSame(PoliticaCredito::MOTIVO_RENDA_INSUFICIENTE, $resultado->motivoRejeicao);
        $this->assertNull($resultado->taxaJuros);
        $this->assertNull($resultado->valorParcela);
    }

    public function test_renda_insuficiente_tem_precedencia_sobre_score_baixo(): void
    {
        $resultado = $this->politica->avaliar(
            rendaMensal: 800.00,
            score: 150,
            valorSolicitado: 1000.00,
        );

        $this->assertSame(PoliticaCredito::MOTIVO_RENDA_INSUFICIENTE, $resultado->motivoRejeicao);
    }

    public function test_reprova_por_score_baixo(): void
    {
        $resultado = $this->politica->avaliar(
            rendaMensal: 10000.00,
            score: 150,
            valorSolicitado: 1000.00,
        );

        $this->assertFalse($resultado->aprovada());
        $this->assertSame(PoliticaCredito::MOTIVO_SCORE_BAIXO, $resultado->motivoRejeicao);
    }

    public function test_reprova_por_comprometimento_de_renda(): void
    {
        // Parcela de ~R$ 11.233,33 contra 30% de R$ 3.000,00 (R$ 900,00)
        $resultado = $this->politica->avaliar(
            rendaMensal: 3000.00,
            score: 850,
            valorSolicitado: 100000.00,
        );

        $this->assertFalse($resultado->aprovada());
        $this->assertSame(PoliticaCredito::MOTIVO_COMPROMETIMENTO, $resultado->motivoRejeicao);
    }

    public function test_aprova_quando_a_parcela_fica_exatamente_no_limite_de_30_por_cento(): void
    {
        // Parcela de R$ 1.123,33 → renda cujo limite de 30% é igual à parcela
        $renda = round(1123.33 / PoliticaCredito::COMPROMETIMENTO_MAXIMO, 2);

        $resultado = $this->politica->avaliar($renda, 850, 10000.00);

        $this->assertTrue(
            $resultado->aprovada(),
            'Parcela exatamente em 30% da renda deve ser aprovada (a regra reprova apenas acima de 30%).',
        );
    }

    #[DataProvider('fronteirasDeRenda')]
    public function test_fronteira_da_renda_minima(float $renda, bool $esperaAprovacao): void
    {
        $resultado = $this->politica->avaliar($renda, 850, 100.00);

        $this->assertSame($esperaAprovacao, $resultado->aprovada());
    }

    /**
     * @return array<string, array{float, bool}>
     */
    public static function fronteirasDeRenda(): array
    {
        return [
            'um centavo abaixo do mínimo' => [1499.99, false],
            'exatamente no mínimo' => [1500.00, true],
            'acima do mínimo' => [1500.01, true],
        ];
    }

    #[DataProvider('fronteirasDeScore')]
    public function test_fronteira_das_faixas_de_score(int $score, ?float $taxaEsperada): void
    {
        $resultado = $this->politica->avaliar(50000.00, $score, 10000.00);

        $this->assertSame($taxaEsperada, $resultado->taxaJuros);
    }

    /**
     * @return array<string, array{int, float|null}>
     */
    public static function fronteirasDeScore(): array
    {
        return [
            'score 399 reprova' => [399, null],
            'score 400 entra na taxa padrão' => [400, PoliticaCredito::TAXA_PADRAO],
            'score 699 ainda é taxa padrão' => [699, PoliticaCredito::TAXA_PADRAO],
            'score 700 vira taxa preferencial' => [700, PoliticaCredito::TAXA_PREFERENCIAL],
            'score 850 é taxa preferencial' => [850, PoliticaCredito::TAXA_PREFERENCIAL],
        ];
    }
}
