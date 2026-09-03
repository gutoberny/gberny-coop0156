<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Lançada quando o Bureau de Crédito não entrega um score utilizável.
 *
 * Cobre os três modos de falha da integração: indisponibilidade/timeout,
 * resposta HTTP de erro e payload sem a chave 'score'.
 *
 * O método render() é chamado pelo Laravel automaticamente, traduzindo a
 * exceção em HTTP 503 com mensagem limpa — sem stack trace e sem 500
 * inesperado. A análise permanece em 'pendente' e pode ser retentada.
 */
class BureauIndisponivelException extends RuntimeException
{
    public static function indisponivel(string $cpf, ?Throwable $anterior = null): self
    {
        return new self(
            "Não foi possível consultar o Bureau de Crédito para o CPF {$cpf}.",
            previous: $anterior,
        );
    }

    public static function respostaInvalida(string $cpf, int $statusHttp): self
    {
        return new self(
            "O Bureau de Crédito respondeu com status {$statusHttp} para o CPF {$cpf}.",
        );
    }

    public static function scoreAusente(string $cpf): self
    {
        return new self(
            "A resposta do Bureau de Crédito para o CPF {$cpf} não contém o score.",
        );
    }

    public function render(Request $request): ?JsonResponse
    {
        if (! $request->expectsJson()) {
            return null;
        }

        return response()->json([
            'message' => 'O Bureau de Crédito está indisponível no momento. '
                .'Sua solicitação foi registrada e você pode tentar novamente em instantes.',
            'erro' => 'bureau_indisponivel',
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
