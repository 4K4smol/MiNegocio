<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoEventoFacturacionRequest;
use App\Http\Requests\Api\V1\UpdateTipoEventoFacturacionRequest;
use App\Http\Resources\Api\V1\TipoEventoFacturacionResource;
use App\Models\TipoEventoFacturacion;

class TipoEventoFacturacionController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoEventoFacturacion::class;
    }

    public function store(StoreTipoEventoFacturacionRequest $request)
    {
        $tipo_evento_facturacion = TipoEventoFacturacion::query()->create($request->validated());

        return $this->created((new TipoEventoFacturacionResource($tipo_evento_facturacion))->toArray($request));
    }

    public function update(UpdateTipoEventoFacturacionRequest $request, int $id)
    {
        $tipo_evento_facturacion = TipoEventoFacturacion::query()->find($id);

        if ($tipo_evento_facturacion === null) {
            return $this->notFound();
        }

        $tipo_evento_facturacion->fill($request->validated());
        $tipo_evento_facturacion->save();

        return $this->updated((new TipoEventoFacturacionResource($tipo_evento_facturacion))->toArray($request));
    }
}
