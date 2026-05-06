<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioUbicacionRequest;
use App\Http\Requests\Api\V1\UpdateInventarioUbicacionRequest;
use App\Http\Resources\Api\V1\InventarioUbicacionResource;
use App\Models\InventarioUbicacion;
use Illuminate\Http\JsonResponse;

class InventarioUbicacionController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return InventarioUbicacion::class;
    }

    protected function resourceClass(): ?string
    {
        return InventarioUbicacionResource::class;
    }

    public function store(StoreInventarioUbicacionRequest $request): JsonResponse
    {
        $record = InventarioUbicacion::query()->create($request->validated());

        return $this->created(
            InventarioUbicacionResource::make($record)->resolve()
        );
    }

    public function update(UpdateInventarioUbicacionRequest $request, int $id): JsonResponse
    {
        $record = InventarioUbicacion::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            InventarioUbicacionResource::make($record)->resolve()
        );
    }
}
