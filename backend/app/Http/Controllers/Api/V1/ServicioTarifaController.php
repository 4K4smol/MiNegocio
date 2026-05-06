<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioTarifaRequest;
use App\Http\Requests\Api\V1\UpdateServicioTarifaRequest;
use App\Http\Resources\Api\V1\ServicioTarifaResource;
use App\Models\ServicioTarifa;
use Illuminate\Http\JsonResponse;

class ServicioTarifaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return ServicioTarifa::class;
    }

    protected function resourceClass(): ?string
    {
        return ServicioTarifaResource::class;
    }

    public function store(StoreServicioTarifaRequest $request): JsonResponse
    {
        $record = ServicioTarifa::query()->create($request->validated());

        return $this->created(
            ServicioTarifaResource::make($record)->resolve()
        );
    }

    public function update(UpdateServicioTarifaRequest $request, int $id): JsonResponse
    {
        $record = ServicioTarifa::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            ServicioTarifaResource::make($record)->resolve()
        );
    }
}
