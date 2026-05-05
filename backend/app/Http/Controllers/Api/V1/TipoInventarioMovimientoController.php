<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoInventarioMovimientoRequest;
use App\Http\Requests\Api\V1\UpdateTipoInventarioMovimientoRequest;
use App\Http\Resources\Api\V1\TipoInventarioMovimientoResource;
use App\Models\TipoInventarioMovimiento;

class TipoInventarioMovimientoController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoInventarioMovimiento::class;
    }

    protected function resourceClass(): ?string
    {
        return TipoInventarioMovimientoResource::class;
    }

    public function store(StoreTipoInventarioMovimientoRequest $request)
    {
        $tipo_inventario_movimiento = TipoInventarioMovimiento::query()->create($request->validated());

        return $this->created(
            TipoInventarioMovimientoResource::make($tipo_inventario_movimiento)->resolve()
        );
    }

    public function update(UpdateTipoInventarioMovimientoRequest $request, int $id)
    {
        $tipo_inventario_movimiento = TipoInventarioMovimiento::query()->find($id);

        if ($tipo_inventario_movimiento === null) {
            return $this->notFound();
        }

        $tipo_inventario_movimiento->fill($request->validated());
        $tipo_inventario_movimiento->save();

        return $this->updated(
            TipoInventarioMovimientoResource::make($tipo_inventario_movimiento)->resolve()
        );
    }
}
