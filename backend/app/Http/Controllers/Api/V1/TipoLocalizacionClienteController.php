<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoLocalizacionClienteRequest;
use App\Http\Requests\Api\V1\UpdateTipoLocalizacionClienteRequest;
use App\Http\Resources\Api\V1\TipoLocalizacionClienteResource;
use App\Models\TipoLocalizacionCliente;

class TipoLocalizacionClienteController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoLocalizacionCliente::class;
    }

    public function store(StoreTipoLocalizacionClienteRequest $request)
    {
        $tipo_localizacion_cliente = TipoLocalizacionCliente::query()->create($request->validated());

        return $this->created((new TipoLocalizacionClienteResource($tipo_localizacion_cliente))->toArray($request));
    }

    public function update(UpdateTipoLocalizacionClienteRequest $request, int $id)
    {
        $tipo_localizacion_cliente = TipoLocalizacionCliente::query()->find($id);

        if ($tipo_localizacion_cliente === null) {
            return $this->notFound();
        }

        $tipo_localizacion_cliente->fill($request->validated());
        $tipo_localizacion_cliente->save();

        return $this->updated((new TipoLocalizacionClienteResource($tipo_localizacion_cliente))->toArray($request));
    }
}
