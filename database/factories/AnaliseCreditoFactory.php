<?php

namespace Database\Factories;

use App\Enums\StatusAnalise;
use App\Enums\TipoCredito;
use App\Models\AnaliseCredito;
use App\Models\Cliente;
use App\Support\PoliticaCredito;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnaliseCredito>
 */
class AnaliseCreditoFactory extends Factory
{
    protected $model = AnaliseCredito::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cliente = Cliente::factory();

        return [
            'cliente_id' => $cliente,
            'cpf' => fake()->numerify('###########'),
            'nome' => fake()->name(),
            'renda_mensal' => 10000.00,
            'tipo_credito' => fake()->randomElement(TipoCredito::cases()),
            'valor_solicitado' => 10000.00,
            'status' => StatusAnalise::PENDENTE,
            'score' => null,
            'taxa_juros' => null,
            'valor_parcela' => null,
            'motivo_rejeicao' => null,
        ];
    }

    /**
     * Análise aprovada e coerente: taxa e parcela derivadas da própria
     * PoliticaCredito, para que a simulação exibida faça sentido.
     */
    public function aprovada(int $score = 850): static
    {
        return $this->state(function (array $attributes) use ($score) {
            $politica = new PoliticaCredito;
            $taxa = $politica->taxaPara($score);

            return [
                'status' => StatusAnalise::APROVADO,
                'score' => $score,
                'taxa_juros' => $taxa,
                'valor_parcela' => $politica->calcularParcela(
                    (float) $attributes['valor_solicitado'],
                    $taxa,
                ),
                'motivo_rejeicao' => null,
            ];
        });
    }

    public function reprovada(string $motivo = PoliticaCredito::MOTIVO_SCORE_BAIXO): static
    {
        return $this->state(fn () => [
            'status' => StatusAnalise::REPROVADO,
            'score' => 150,
            'taxa_juros' => null,
            'valor_parcela' => null,
            'motivo_rejeicao' => $motivo,
        ]);
    }

    public function comStatus(StatusAnalise $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function doCliente(Cliente $cliente): static
    {
        return $this->state(fn () => [
            'cliente_id' => $cliente->id,
            'cpf' => $cliente->cpf,
            'nome' => $cliente->nome,
            'renda_mensal' => $cliente->renda_mensal,
        ]);
    }
}
