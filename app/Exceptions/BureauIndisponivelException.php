<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Lançada quando o Bureau de Crédito não entrega um score utilizável.
 *
 * Cobre os três modos de falha da integração: indisponibilidade/timeout,
 * resposta HTTP de erro e payload sem a chave 'score'. O controller a
 * traduz em HTTP 503, mantendo a análise em 'pendente'.
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
}
