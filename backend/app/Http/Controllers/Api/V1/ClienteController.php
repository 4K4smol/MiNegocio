<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreClienteRequest;
use App\Http\Requests\Api\V1\UpdateClienteRequest;
use App\Models\Cliente;

class ClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Cliente::class;
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
