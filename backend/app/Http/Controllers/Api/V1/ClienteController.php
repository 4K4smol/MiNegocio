<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreClienteRequest;
use App\Http\Requests\Api\V1\UpdateClienteRequest;
use App\Http\Resources\Api\V1\ClienteResource;
use App\Models\Cliente;

class ClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Cliente::class;
    }

    protected function resourceClass(): ?string
    {
        return ClienteResource::class;
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::query()->create($this->fillEmpresaIdFromUser($request->validated(), $request));

        return $this->created(
            ClienteResource::make($cliente)->resolve()
        );
    }

    public function update(UpdateClienteRequest $request, int $id)
    {
        $cliente = $this->findRecord($request, $id);

        if ($cliente === null) {
            return $this->notFound();
        }

        $cliente->fill($this->fillEmpresaIdFromUser($request->validated(), $request));
        $cliente->save();

        return $this->updated(
            ClienteResource::make($cliente)->resolve()
        );
    }
}
