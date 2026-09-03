<?php

namespace App\Services;

use App\Exceptions\BureauIndisponivelException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Única peça que conversa com o Bureau de Crédito externo.
 *
 * Isola a integração HTTP do resto da aplicação: quem consome recebe um
 * score utilizável ou uma BureauIndisponivelException — nunca um erro de
 * rede cru nem um payload inesperado.
 */
class BureauCreditoClient
{
    public function __construct(
        private readonly string $urlBase,
        private readonly int $timeout,
    ) {}

    public static function apartirDaConfiguracao(): self
    {
        return new self(
            urlBase: rtrim((string) config('services.score_bureau.url'), '/'),
            timeout: (int) config('services.score_bureau.timeout'),
        );
    }

    /**
     * Consulta o score de crédito do CPF informado.
     *
     * @throws BureauIndisponivelException quando o Bureau está fora,
     *                                     estoura o timeout, responde com
     *                                     erro HTTP ou omite o score.
     */
    public function consultarScore(string $cpf): int
    {
        $url = "{$this->urlBase}/{$cpf}";

        try {
            $resposta = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($url);
        } catch (ConnectionException $e) {
            // Cobre indisponibilidade e timeout (o mock atrasa 5s para
            // CPFs terminados em 5, acima do timeout configurado).
            $this->registrarFalha($cpf, 'conexão ou timeout', [
                'url' => $url,
                'erro' => $e->getMessage(),
            ]);

            throw BureauIndisponivelException::indisponivel($cpf, $e);
        }

        if ($resposta->failed()) {
            $this->registrarFalha($cpf, 'resposta HTTP de erro', [
                'url' => $url,
                'status' => $resposta->status(),
            ]);

            throw BureauIndisponivelException::respostaInvalida($cpf, $resposta->status());
        }

        $score = $resposta->json('score');

        if (! is_numeric($score)) {
            $this->registrarFalha($cpf, 'payload sem score', [
                'url' => $url,
                'payload' => $resposta->json(),
            ]);

            throw BureauIndisponivelException::scoreAusente($cpf);
        }

        return (int) $score;
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    private function registrarFalha(string $cpf, string $motivo, array $contexto = []): void
    {
        Log::warning("Falha na consulta ao Bureau de Crédito: {$motivo}.", [
            'cpf' => $cpf,
            ...$contexto,
        ]);
    }
}
