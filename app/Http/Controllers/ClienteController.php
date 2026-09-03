<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ClienteController extends Controller
{
    /**
     * Lista paginada de clientes.
     *
     * GET /api/clientes
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $porPagina = min((int) $request->integer('per_page', 15), 100);

        $clientes = Cliente::query()
            ->latest('id')
            ->paginate($porPagina);

        return ClienteResource::collection($clientes);
    }

    /**
     * Cadastra um novo cliente.
     *
     * POST /api/clientes
     */
    public function store(StoreClienteRequest $request): JsonResponse
    {
        $cliente = Cliente::create($request->validated());

        return ClienteResource::make($cliente)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Exibe os dados de um cliente específico.
     *
     * GET /api/clientes/{id}
     */
    public function show(int $cliente): ClienteResource
    {
        $encontrado = Cliente::with('analises')->findOrFail($cliente);

        return ClienteResource::make($encontrado);
    }

    /**
     * Atualiza os dados de um cliente existente (total ou parcialmente).
     *
     * PUT/PATCH /api/clientes/{id}
     */
    public function update(UpdateClienteRequest $request, int $cliente): ClienteResource
    {
        $encontrado = Cliente::findOrFail($cliente);

        $encontrado->update($request->validated());

        return ClienteResource::make($encontrado->refresh());
    }

    /**
     * Remove um cliente do sistema.
     *
     * DELETE /api/clientes/{id}
     */
    public function destroy(int $cliente): Response
    {
        Cliente::findOrFail($cliente)->delete();

        return response()->noContent();
    }
}
