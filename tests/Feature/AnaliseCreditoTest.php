<?php

namespace Tests\Feature;

use App\Enums\StatusAnalise;
use App\Jobs\ProcessarContratacaoJob;
use App\Models\AnaliseCredito;
use App\Models\Cliente;
use App\Support\PoliticaCredito;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AnaliseCreditoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Todos os testes fazem Http::fake(), então nenhuma chamada real de rede
     * acontece. preventStrayRequests garante que uma requisição não simulada
     * falhe o teste em vez de sair para a rede.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    /**
     * Simula a resposta do Bureau com o score informado.
     */
    private function fakeBureauComScore(int $score): void
    {
        Http::fake([
            '*/api/mock/bureau/*' => Http::response([
                'cpf' => '12345678901',
                'score' => $score,
                'situacao' => 'ativo',
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $sobrescritas = []): array
    {
        return array_merge([
            'nome' => 'João da Silva',
            'cpf' => '12345678901',
            'renda_mensal' => 10000.00,
            'tipo_credito' => 'pessoal',
            'valor_solicitado' => 10000.00,
        ], $sobrescritas);
    }

    // ---------------------------------------------------------------------
    // Aprovações
    // ---------------------------------------------------------------------

    public function test_aprova_analise_com_score_alto_aplicando_taxa_de_2_9(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.status', StatusAnalise::APROVADO->value)
            ->assertJsonPath('data.score', 850)
            ->assertJsonPath('data.taxa_juros', fn ($v) => (float) $v === 2.9)
            ->assertJsonPath('data.valor_parcela', fn ($v) => (float) $v === 1123.33)
            ->assertJsonPath('data.motivo_rejeicao', null)
            ->assertJsonPath('data.url_simulacao', fn ($v) => str_contains((string) $v, '/simulacao/'));

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '12345678901',
            'status' => StatusAnalise::APROVADO->value,
            'score' => 850,
            'taxa_juros' => 2.9,
        ]);
    }

    public function test_aprova_analise_com_score_medio_aplicando_taxa_de_4_5(): void
    {
        $this->fakeBureauComScore(550);

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.status', StatusAnalise::APROVADO->value)
            ->assertJsonPath('data.score', 550)
            ->assertJsonPath('data.taxa_juros', fn ($v) => (float) $v === 4.5)
            ->assertJsonPath('data.valor_parcela', fn ($v) => (float) $v === 1283.33);
    }

    public function test_resposta_aprovada_expoe_o_link_da_tela_de_simulacao(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $id = $response->json('data.id');
        $response->assertJsonPath('data.url_simulacao', url("/simulacao/{$id}"));
    }

    // ---------------------------------------------------------------------
    // Reprovações
    // ---------------------------------------------------------------------

    public function test_reprova_por_renda_mensal_insuficiente(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', $this->payload([
            'renda_mensal' => 1000.00,
            'valor_solicitado' => 1000.00,
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', StatusAnalise::REPROVADO->value)
            ->assertJsonPath('data.motivo_rejeicao', PoliticaCredito::MOTIVO_RENDA_INSUFICIENTE)
            ->assertJsonPath('data.taxa_juros', null)
            ->assertJsonPath('data.valor_parcela', null)
            ->assertJsonPath('data.url_simulacao', null);
    }

    public function test_reprova_por_score_muito_baixo(): void
    {
        $this->fakeBureauComScore(150);

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.status', StatusAnalise::REPROVADO->value)
            ->assertJsonPath('data.score', 150)
            ->assertJsonPath('data.motivo_rejeicao', PoliticaCredito::MOTIVO_SCORE_BAIXO);
    }

    public function test_reprova_por_comprometimento_de_renda_superior_a_30_por_cento(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', $this->payload([
            'renda_mensal' => 3000.00,
            'valor_solicitado' => 100000.00,
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.status', StatusAnalise::REPROVADO->value)
            ->assertJsonPath('data.motivo_rejeicao', PoliticaCredito::MOTIVO_COMPROMETIMENTO);
    }

    public function test_registra_o_score_mesmo_quando_a_reprovacao_vem_da_renda(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', $this->payload([
            'renda_mensal' => 1000.00,
        ]));

        $response->assertJsonPath('data.score', 850);
    }

    // ---------------------------------------------------------------------
    // Resiliência da integração com o Bureau
    // ---------------------------------------------------------------------

    public function test_responde_503_quando_o_bureau_retorna_erro_500(): void
    {
        Http::fake([
            '*/api/mock/bureau/*' => Http::response(
                ['error' => 'Erro interno na comunicação com o provedor de score.'],
                500,
            ),
        ]);

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $response->assertServiceUnavailable()
            ->assertJsonPath('erro', 'bureau_indisponivel')
            ->assertJsonStructure(['message', 'erro']);

        // A solicitação fica registrada em 'pendente' e pode ser retentada.
        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '12345678901',
            'status' => StatusAnalise::PENDENTE->value,
            'score' => null,
        ]);
    }

    public function test_responde_503_quando_o_bureau_estoura_o_timeout(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection timed out'));

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $response->assertServiceUnavailable()
            ->assertJsonPath('erro', 'bureau_indisponivel');

        $this->assertDatabaseHas('analises_credito', [
            'cpf' => '12345678901',
            'status' => StatusAnalise::PENDENTE->value,
        ]);
    }

    public function test_responde_503_quando_o_bureau_devolve_payload_sem_score(): void
    {
        Http::fake([
            '*/api/mock/bureau/*' => Http::response([
                'cpf' => '12345678901',
                'status_bureau' => 'ok',
            ]),
        ]);

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $response->assertServiceUnavailable()
            ->assertJsonPath('erro', 'bureau_indisponivel');
    }

    public function test_consulta_o_bureau_no_endpoint_esperado_com_o_cpf(): void
    {
        $this->fakeBureauComScore(850);

        $this->postJson('/api/analise-credito', $this->payload());

        Http::assertSent(fn ($request) => str_ends_with(
            $request->url(),
            '/api/mock/bureau/12345678901',
        ));
    }

    // ---------------------------------------------------------------------
    // Validação da entrada
    // ---------------------------------------------------------------------

    public function test_falha_de_validacao_quando_faltam_campos_obrigatorios(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nome', 'cpf', 'renda_mensal', 'tipo_credito', 'valor_solicitado',
            ]);

        Http::assertNothingSent();
    }

    public function test_falha_de_validacao_com_tipo_de_credito_invalido(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', $this->payload([
            'tipo_credito' => 'consignado',
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('tipo_credito');
    }

    public function test_aceita_cpf_com_mascara_normalizando_para_apenas_digitos(): void
    {
        $this->fakeBureauComScore(850);

        $response = $this->postJson('/api/analise-credito', $this->payload([
            'cpf' => '123.456.789-01',
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.cpf', '12345678901');
    }

    // ---------------------------------------------------------------------
    // Vínculo com o cliente
    // ---------------------------------------------------------------------

    public function test_cria_o_cliente_automaticamente_ao_solicitar_com_cpf_novo(): void
    {
        $this->fakeBureauComScore(850);

        $this->assertDatabaseCount('clientes', 0);

        $response = $this->postJson('/api/analise-credito', $this->payload());

        $response->assertCreated();

        $this->assertDatabaseHas('clientes', [
            'cpf' => '12345678901',
            'nome' => 'João da Silva',
        ]);

        $cliente = Cliente::where('cpf', '12345678901')->sole();
        $response->assertJsonPath('data.cliente_id', $cliente->id);
    }

    public function test_usa_o_email_informado_ao_criar_o_cliente_no_fluxo_de_analise(): void
    {
        $this->fakeBureauComScore(850);

        $this->postJson('/api/analise-credito', $this->payload([
            'email' => 'joao.informado@example.com',
        ]))->assertCreated();

        $this->assertDatabaseHas('clientes', [
            'cpf' => '12345678901',
            'email' => 'joao.informado@example.com',
        ]);
    }

    public function test_reaproveita_o_cliente_existente_quando_o_cpf_ja_esta_cadastrado(): void
    {
        $this->fakeBureauComScore(850);

        $cliente = Cliente::factory()->create([
            'cpf' => '12345678901',
            'nome' => 'João Já Cadastrado',
        ]);

        $this->postJson('/api/analise-credito', $this->payload())->assertCreated();
        $this->postJson('/api/analise-credito', $this->payload())->assertCreated();

        $this->assertDatabaseCount('clientes', 1);
        $this->assertSame(2, $cliente->analises()->count());
    }

    // ---------------------------------------------------------------------
    // Listagem
    // ---------------------------------------------------------------------

    public function test_lista_analises_de_forma_paginada_da_mais_recente_para_a_mais_antiga(): void
    {
        $analises = AnaliseCredito::factory()->count(3)->create();

        $response = $this->getJson('/api/analise-credito');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.id', $analises->last()->id)
            ->assertJsonStructure([
                'data' => [['id', 'nome', 'cpf', 'status', 'score', 'valor_solicitado']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_lista_analises_filtrando_por_cliente(): void
    {
        $cliente = Cliente::factory()->create();
        AnaliseCredito::factory()->count(2)->doCliente($cliente)->create();
        AnaliseCredito::factory()->count(3)->create();

        $response = $this->getJson("/api/analise-credito?cliente_id={$cliente->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);

        foreach ($response->json('data') as $analise) {
            $this->assertSame($cliente->id, $analise['cliente_id']);
        }
    }

    public function test_lista_analises_vazia_quando_nao_ha_registros(): void
    {
        $this->getJson('/api/analise-credito')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_lista_analises_respeita_o_tamanho_de_pagina(): void
    {
        AnaliseCredito::factory()->count(8)->create();

        $this->getJson('/api/analise-credito?per_page=5')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 8);
    }

    // ---------------------------------------------------------------------
    // Contratação
    // ---------------------------------------------------------------------

    public function test_contrata_analise_aprovada(): void
    {
        $analise = AnaliseCredito::factory()->aprovada()->create();

        $response = $this->postJson("/api/analise-credito/{$analise->id}/contratar");

        $response->assertOk()
            ->assertJsonPath('message', 'Contratação enviada para processamento com sucesso.');

        // Com QUEUE_CONNECTION=sync nos testes, o Job roda inline e a análise
        // chega ao estado final 'contratado'.
        $this->assertDatabaseHas('analises_credito', [
            'id' => $analise->id,
            'status' => StatusAnalise::CONTRATADO->value,
        ]);
    }

    public function test_contratacao_despacha_o_job_e_deixa_a_analise_em_processamento(): void
    {
        Queue::fake();

        $analise = AnaliseCredito::factory()->aprovada()->create();

        $response = $this->postJson("/api/analise-credito/{$analise->id}/contratar");

        $response->assertOk()
            ->assertJsonPath('data.status', StatusAnalise::PROCESSANDO_CONTRATACAO->value);

        Queue::assertPushed(
            ProcessarContratacaoJob::class,
            fn (ProcessarContratacaoJob $job) => $job->analiseId === $analise->id,
        );

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analise->id,
            'status' => StatusAnalise::PROCESSANDO_CONTRATACAO->value,
        ]);
    }

    public function test_job_de_contratacao_finaliza_a_analise_como_contratada(): void
    {
        $analise = AnaliseCredito::factory()
            ->aprovada()
            ->comStatus(StatusAnalise::PROCESSANDO_CONTRATACAO)
            ->create();

        (new ProcessarContratacaoJob($analise->id))->handle();

        $this->assertSame(StatusAnalise::CONTRATADO, $analise->refresh()->status);
    }

    public function test_job_de_contratacao_e_idempotente(): void
    {
        $analise = AnaliseCredito::factory()
            ->aprovada()
            ->comStatus(StatusAnalise::CONTRATADO)
            ->create();

        (new ProcessarContratacaoJob($analise->id))->handle();

        $this->assertSame(StatusAnalise::CONTRATADO, $analise->refresh()->status);
    }

    public function test_job_de_contratacao_nao_quebra_com_analise_inexistente(): void
    {
        (new ProcessarContratacaoJob(9999))->handle();

        $this->assertTrue(true, 'O Job deve apenas registrar aviso, sem lançar exceção.');
    }

    public function test_nao_contrata_analise_reprovada(): void
    {
        Queue::fake();

        $analise = AnaliseCredito::factory()->reprovada()->create();

        $response = $this->postJson("/api/analise-credito/{$analise->id}/contratar");

        $response->assertUnprocessable()
            ->assertJsonPath('erro', 'analise_nao_contratavel')
            ->assertJsonPath('status_atual', StatusAnalise::REPROVADO->value);

        Queue::assertNothingPushed();

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analise->id,
            'status' => StatusAnalise::REPROVADO->value,
        ]);
    }

    public function test_nao_contrata_analise_pendente(): void
    {
        $analise = AnaliseCredito::factory()->create();

        $this->postJson("/api/analise-credito/{$analise->id}/contratar")
            ->assertUnprocessable()
            ->assertJsonPath('status_atual', StatusAnalise::PENDENTE->value);
    }

    public function test_nao_contrata_a_mesma_analise_duas_vezes(): void
    {
        $analise = AnaliseCredito::factory()->aprovada()->create();

        $this->postJson("/api/analise-credito/{$analise->id}/contratar")->assertOk();
        $this->postJson("/api/analise-credito/{$analise->id}/contratar")->assertUnprocessable();
    }

    public function test_retorna_404_ao_contratar_analise_inexistente(): void
    {
        $this->postJson('/api/analise-credito/9999/contratar')->assertNotFound();
    }
}
