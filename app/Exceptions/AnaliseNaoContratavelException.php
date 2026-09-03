<?php

namespace App\Exceptions;

use App\Enums\StatusAnalise;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lançada ao tentar contratar uma análise que não está aprovada —
 * uma reprovada, uma ainda pendente ou uma já contratada.
 *
 * Traduzida pelo Laravel em HTTP 422, já que o recurso existe mas o
 * estado atual não permite a operação.
 */
class AnaliseNaoContratavelException extends DomainException
{
    public function __construct(public readonly StatusAnalise $statusAtual)
    {
        parent::__construct(
            "Apenas análises aprovadas podem ser contratadas. Status atual: {$statusAtual->value}.",
        );
    }

    public function render(Request $request): ?JsonResponse
    {
        if (! $request->expectsJson()) {
            return null;
        }

        return response()->json([
            'message' => $this->getMessage(),
            'erro' => 'analise_nao_contratavel',
            'status_atual' => $this->statusAtual->value,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
