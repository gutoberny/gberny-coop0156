<?php

namespace App\Http\Controllers;

use App\Http\Requests\SolicitarAnaliseRequest;
use App\Http\Resources\AnaliseCreditoResource;
use App\Models\AnaliseCredito;
use App\Services\AnaliseCreditoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * O controller apenas traduz HTTP: valida a entrada via Form Request,
 * delega ao AnaliseCreditoService e serializa a resposta. As regras de
 * negócio e a integração externa ficam nas suas próprias camadas, e as
 * exceções de domínio se traduzem em status codes pelo render() de cada
 * uma (503 para Bureau indisponível, 422 para análise não contratável).
 */
class AnaliseCreditoController extends Controller
{
    public function __construct(
        private readonly AnaliseCreditoService $service,
    ) {}

    /**
     * Lista paginada das análises, da mais recente para a mais antiga.
     *
     * GET /api/analise-credito
     * GET /api/analise-credito?cliente_id=1
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $porPagina = min((int) $request->integer('per_page', 15), 100);

        $analises = AnaliseCredito::query()
            ->when(
                $request->filled('cliente_id'),
                fn ($query) => $query->where('cliente_id', $request->integer('cliente_id')),
            )
            ->latest('id')
            ->paginate($porPagina);

        return AnaliseCreditoResource::collection($analises);
    }

    /**
     * Solicita uma nova análise de crédito.
     *
     * POST /api/analise-credito
     */
    public function solicitar(SolicitarAnaliseRequest $request): JsonResponse
    {
        $analise = $this->service->solicitar($request->validated());

        return AnaliseCreditoResource::make($analise)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Confirma a contratação de uma análise de crédito aprovada.
     *
     * POST /api/analise-credito/{id}/contratar
     */
    public function contratar(int $id): JsonResponse
    {
        $analise = AnaliseCredito::findOrFail($id);

        $contratada = $this->service->contratar($analise);

        return AnaliseCreditoResource::make($contratada)
            ->additional([
                'message' => 'Contratação enviada para processamento com sucesso.',
            ])
            ->response();
    }
}
