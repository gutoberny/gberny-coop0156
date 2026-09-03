<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function dadosValidos(array $sobrescritas = []): array
    {
        return array_merge([
            'nome' => 'João da Silva',
            'cpf' => '12345678901',
            'email' => 'joao.silva@example.com',
            'telefone' => '51999998888',
            'renda_mensal' => 4500.00,
        ], $sobrescritas);
    }

    public function test_cria_cliente_com_dados_validos(): void
    {
        $response = $this->postJson('/api/clientes', $this->dadosValidos());

        $response->assertCreated()
            ->assertJsonPath('data.nome', 'João da Silva')
            ->assertJsonPath('data.cpf', '12345678901')
            ->assertJsonPath('data.email', 'joao.silva@example.com')
            ->assertJsonPath('data.renda_mensal', fn ($valor) => (float) $valor === 4500.0);

        $this->assertDatabaseHas('clientes', [
            'cpf' => '12345678901',
            'email' => 'joao.silva@example.com',
        ]);
    }

    public function test_cria_cliente_sem_telefone_por_ser_opcional(): void
    {
        $response = $this->postJson('/api/clientes', $this->dadosValidos([
            'telefone' => null,
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.telefone', null);
    }

    public function test_falha_ao_criar_cliente_sem_campos_obrigatorios(): void
    {
        $response = $this->postJson('/api/clientes', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['nome', 'cpf', 'email', 'renda_mensal']);
    }

    public function test_falha_ao_criar_cliente_com_cpf_de_formato_invalido(): void
    {
        $response = $this->postJson('/api/clientes', $this->dadosValidos([
            'cpf' => '123456789',
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('cpf');
    }

    public function test_falha_ao_criar_cliente_com_cpf_duplicado(): void
    {
        Cliente::factory()->create(['cpf' => '12345678901']);

        $response = $this->postJson('/api/clientes', $this->dadosValidos([
            'cpf' => '12345678901',
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('cpf');
    }

    public function test_falha_ao_criar_cliente_com_email_duplicado(): void
    {
        Cliente::factory()->create(['email' => 'joao.silva@example.com']);

        $response = $this->postJson('/api/clientes', $this->dadosValidos([
            'email' => 'joao.silva@example.com',
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_falha_ao_criar_cliente_com_renda_negativa(): void
    {
        $response = $this->postJson('/api/clientes', $this->dadosValidos([
            'renda_mensal' => -100,
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('renda_mensal');
    }

    public function test_lista_clientes_de_forma_paginada(): void
    {
        Cliente::factory()->count(20)->create();

        $response = $this->getJson('/api/clientes');

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonStructure([
                'data' => [['id', 'nome', 'cpf', 'email', 'renda_mensal']],
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_lista_clientes_respeita_o_tamanho_de_pagina_informado(): void
    {
        Cliente::factory()->count(8)->create();

        $response = $this->getJson('/api/clientes?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 8);
    }

    public function test_exibe_cliente_existente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->getJson("/api/clientes/{$cliente->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $cliente->id)
            ->assertJsonPath('data.cpf', $cliente->cpf);
    }

    public function test_retorna_404_ao_buscar_cliente_inexistente(): void
    {
        $this->getJson('/api/clientes/9999')->assertNotFound();
    }

    public function test_atualiza_parcialmente_cliente_existente(): void
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'Nome Antigo',
            'renda_mensal' => 3000.00,
        ]);

        $response = $this->putJson("/api/clientes/{$cliente->id}", [
            'nome' => 'Nome Novo',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nome', 'Nome Novo')
            ->assertJsonPath('data.renda_mensal', fn ($valor) => (float) $valor === 3000.0);

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'nome' => 'Nome Novo',
        ]);
    }

    public function test_atualiza_cliente_mantendo_o_proprio_email_sem_conflito(): void
    {
        $cliente = Cliente::factory()->create(['email' => 'mesmo@example.com']);

        $response = $this->putJson("/api/clientes/{$cliente->id}", [
            'email' => 'mesmo@example.com',
            'nome' => 'Outro Nome',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'mesmo@example.com');
    }

    public function test_falha_ao_atualizar_cliente_com_email_de_outro_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        $outro = Cliente::factory()->create(['email' => 'ocupado@example.com']);

        $response = $this->putJson("/api/clientes/{$cliente->id}", [
            'email' => $outro->email,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_retorna_404_ao_atualizar_cliente_inexistente(): void
    {
        $this->putJson('/api/clientes/9999', ['nome' => 'Qualquer'])
            ->assertNotFound();
    }

    public function test_remove_cliente_existente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->deleteJson("/api/clientes/{$cliente->id}");

        $response->assertNoContent();
        $this->assertSame('', $response->getContent());
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    public function test_retorna_404_ao_remover_cliente_inexistente(): void
    {
        $this->deleteJson('/api/clientes/9999')->assertNotFound();
    }
}
