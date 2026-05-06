<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioUnidadMedidaRequest;
use App\Http\Requests\Api\V1\UpdateInventarioUnidadMedidaRequest;
use App\Http\Resources\Api\V1\InventarioUnidadMedidaResource;
use App\Models\InventarioUnidadMedida;
use Illuminate\Http\JsonResponse;

class InventarioUnidadMedidaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return InventarioUnidadMedida::class;
    }

    protected function resourceClass(): ?string
    {
        return InventarioUnidadMedidaResource::class;
    }

    public function store(StoreInventarioUnidadMedidaRequest $request): JsonResponse
    {
        $record = InventarioUnidadMedida::query()->create($request->validated());

        return $this->created(
            InventarioUnidadMedidaResource::make($record)->resolve()
        );
    }

    public function update(UpdateInventarioUnidadMedidaRequest $request, int $id): JsonResponse
    {
        $record = InventarioUnidadMedida::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            InventarioUnidadMedidaResource::make($record)->resolve()
        );
    }
}
