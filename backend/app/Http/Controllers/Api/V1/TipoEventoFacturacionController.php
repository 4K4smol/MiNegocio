<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreTipoEventoFacturacionRequest;
use App\Http\Requests\Api\V1\UpdateTipoEventoFacturacionRequest;
use App\Http\Resources\Api\V1\TipoEventoFacturacionResource;
use App\Models\TipoEventoFacturacion;
use Illuminate\Http\JsonResponse;

class TipoEventoFacturacionController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return TipoEventoFacturacion::class;
    }

    protected function resourceClass(): ?string
    {
        return TipoEventoFacturacionResource::class;
    }

    public function store(StoreTipoEventoFacturacionRequest $request): JsonResponse
    {
        $tipoEventoFacturacion = TipoEventoFacturacion::query()
            ->create($request->validated());

        return $this->created(
            TipoEventoFacturacionResource::make($tipoEventoFacturacion)->resolve($request)
        );
    }

    public function update(UpdateTipoEventoFacturacionRequest $request, int $id): JsonResponse
    {
        $tipoEventoFacturacion = TipoEventoFacturacion::query()->find($id);

        if ($tipoEventoFacturacion === null) {
            return $this->notFound();
        }

        $tipoEventoFacturacion->fill($request->validated());
        $tipoEventoFacturacion->save();

        return $this->updated(
            TipoEventoFacturacionResource::make($tipoEventoFacturacion)->resolve($request)
        );
    }
}
