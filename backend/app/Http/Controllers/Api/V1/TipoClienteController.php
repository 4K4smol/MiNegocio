<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoClienteRequest;
use App\Http\Requests\Api\V1\UpdateTipoClienteRequest;
use App\Http\Resources\Api\V1\TipoClienteResource;
use App\Models\TipoCliente;

class TipoClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoCliente::class;
    }

    public function store(StoreTipoClienteRequest $request)
    {
        $tipo_cliente = TipoCliente::query()->create($request->validated());

        return $this->created((new TipoClienteResource($tipo_cliente))->toArray($request));
    }

    public function update(UpdateTipoClienteRequest $request, int $id)
    {
        $tipo_cliente = TipoCliente::query()->find($id);

        if ($tipo_cliente === null) {
            return $this->notFound();
        }

        $tipo_cliente->fill($request->validated());
        $tipo_cliente->save();

        return $this->updated((new TipoClienteResource($tipo_cliente))->toArray($request));
    }
}
