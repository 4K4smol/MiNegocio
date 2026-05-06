<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioMovimientoRequest;
use App\Http\Requests\Api\V1\UpdateInventarioMovimientoRequest;
use App\Http\Resources\Api\V1\InventarioMovimientoResource;
use App\Models\InventarioMovimiento;
use Illuminate\Http\JsonResponse;

class InventarioMovimientoController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return InventarioMovimiento::class;
    }

    protected function resourceClass(): ?string
    {
        return InventarioMovimientoResource::class;
    }

    public function store(StoreInventarioMovimientoRequest $request): JsonResponse
    {
        $record = InventarioMovimiento::query()->create($request->validated());

        return $this->created(
            InventarioMovimientoResource::make($record)->resolve()
        );
    }

    public function update(UpdateInventarioMovimientoRequest $request, int $id): JsonResponse
    {
        $record = InventarioMovimiento::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            InventarioMovimientoResource::make($record)->resolve()
        );
    }
}
