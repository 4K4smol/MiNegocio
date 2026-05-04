<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreClienteRequest;
use App\Http\Requests\Api\V1\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Cliente::class;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $clientes = Cliente::query()
            ->with(['tipoCliente', 'empresa'])
            ->paginate($perPage);

        return $this->success($clientes->toArray());
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::query()->create($request->validated());

        return $this->created($cliente->toArray());
    }

    public function update(UpdateClienteRequest $request, int $id)
    {
        $cliente = Cliente::query()->find($id);

        if ($cliente === null) {
            return $this->notFound();
        }

        $cliente->fill($request->validated());
        $cliente->save();

        return $this->updated($cliente->toArray());
    }
}
