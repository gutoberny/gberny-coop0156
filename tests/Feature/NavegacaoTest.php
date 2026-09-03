<?php

namespace Tests\Feature;

use App\Models\AnaliseCredito;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Garante que as telas do painel respondem e que a simulação continua
 * protegida por status.
 */
class NavegacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_raiz_redireciona_para_clientes(): void
    {
        $this->get('/')->assertRedirect('/clientes');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function telas(): array
    {
        return [
            'lista de clientes' => ['/clientes', 'Clientes'],
            'novo cliente' => ['/clientes/novo', 'Novo cliente'],
            'lista de solicitações' => ['/solicitacoes', 'Solicitações de crédito'],
            'nova solicitação' => ['/solicitacoes/nova', 'Nova solicitação'],
        ];
    }

    #[DataProvider('telas')]
    public function test_telas_do_painel_respondem(string $rota, string $titulo): void
    {
        $this->get($rota)
            ->assertOk()
            ->assertSee($titulo);
    }

    public function test_navegacao_lateral_aparece_em_todas_as_telas(): void
    {
        foreach (array_column(self::telas(), 0) as $rota) {
            $this->get($rota)
                ->assertOk()
                ->assertSee('Coop0156')
                ->assertSee('href="/solicitacoes"', escape: false);
        }
    }

    public function test_tela_de_simulacao_exibe_analise_aprovada(): void
    {
        $analise = AnaliseCredito::factory()->aprovada()->create();

        $this->get("/simulacao/{$analise->id}")
            ->assertOk()
            ->assertSee($analise->nome)
            ->assertSee('Confirmar contratação');
    }

    public function test_tela_de_simulacao_redireciona_analise_nao_aprovada(): void
    {
        $analise = AnaliseCredito::factory()->reprovada()->create();

        $this->get("/simulacao/{$analise->id}")->assertRedirect('/');
    }

    public function test_tela_de_simulacao_retorna_404_para_analise_inexistente(): void
    {
        $this->get('/simulacao/9999')->assertNotFound();
    }
}
