<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoRectificacionRequest;
use App\Http\Requests\Api\V1\UpdateTipoRectificacionRequest;
use App\Http\Resources\Api\V1\TipoRectificacionResource;
use App\Models\TipoRectificacion;

class TipoRectificacionController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoRectificacion::class;
    }

    public function store(StoreTipoRectificacionRequest $request)
    {
        $tipo_rectificacion = TipoRectificacion::query()->create($request->validated());

        return $this->created((new TipoRectificacionResource($tipo_rectificacion))->toArray($request));
    }

    public function update(UpdateTipoRectificacionRequest $request, int $id)
    {
        $tipo_rectificacion = TipoRectificacion::query()->find($id);

        if ($tipo_rectificacion === null) {
            return $this->notFound();
        }

        $tipo_rectificacion->fill($request->validated());
        $tipo_rectificacion->save();

        return $this->updated((new TipoRectificacionResource($tipo_rectificacion))->toArray($request));
    }
}
