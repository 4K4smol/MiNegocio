<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoRegistroFacturacionRequest;
use App\Http\Requests\Api\V1\UpdateTipoRegistroFacturacionRequest;
use App\Http\Resources\Api\V1\TipoRegistroFacturacionResource;
use App\Models\TipoRegistroFacturacion;

class TipoRegistroFacturacionController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoRegistroFacturacion::class;
    }

    public function store(StoreTipoRegistroFacturacionRequest $request)
    {
        $tipo_registro_facturacion = TipoRegistroFacturacion::query()->create($request->validated());

        return $this->created((new TipoRegistroFacturacionResource($tipo_registro_facturacion))->toArray($request));
    }

    public function update(UpdateTipoRegistroFacturacionRequest $request, int $id)
    {
        $tipo_registro_facturacion = TipoRegistroFacturacion::query()->find($id);

        if ($tipo_registro_facturacion === null) {
            return $this->notFound();
        }

        $tipo_registro_facturacion->fill($request->validated());
        $tipo_registro_facturacion->save();

        return $this->updated((new TipoRegistroFacturacionResource($tipo_registro_facturacion))->toArray($request));
    }
}
