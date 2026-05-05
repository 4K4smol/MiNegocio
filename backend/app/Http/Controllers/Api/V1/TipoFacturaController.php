<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoFacturaRequest;
use App\Http\Requests\Api\V1\UpdateTipoFacturaRequest;
use App\Http\Resources\Api\V1\TipoFacturaResource;
use App\Models\TipoFactura;

class TipoFacturaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoFactura::class;
    }

    protected function resourceClass(): ?string
    {
        return TipoFacturaResource::class;
    }

    public function store(StoreTipoFacturaRequest $request)
    {
        $tipo_factura = TipoFactura::query()->create($request->validated());

        return $this->created(
            TipoFacturaResource::make($tipo_factura)->resolve()
        );
    }

    public function update(UpdateTipoFacturaRequest $request, int $id)
    {
        $tipo_factura = TipoFactura::query()->find($id);

        if ($tipo_factura === null) {
            return $this->notFound();
        }

        $tipo_factura->fill($request->validated());
        $tipo_factura->save();

        return $this->updated(
            TipoFacturaResource::make($tipo_factura)->resolve()
        );
    }
}
