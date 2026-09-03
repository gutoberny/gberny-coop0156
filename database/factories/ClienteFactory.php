<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf' => $this->cpf(),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('519########'),
            'renda_mensal' => fake()->randomFloat(2, 2000, 20000),
        ];
    }

    /**
     * CPF com o último dígito controlado, para casar com o cenário
     * desejado do Bureau mock (ver README do desafio).
     */
    public function comCpfTerminadoEm(string $digito): static
    {
        return $this->state(fn () => [
            'cpf' => substr($this->cpf(), 0, 10).$digito,
        ]);
    }

    public function comRenda(float $renda): static
    {
        return $this->state(fn () => ['renda_mensal' => $renda]);
    }

    /**
     * 11 dígitos numéricos únicos — não valida dígito verificador,
     * já que o desafio exige apenas o formato.
     */
    private function cpf(): string
    {
        return fake()->unique()->numerify('###########');
    }
}
