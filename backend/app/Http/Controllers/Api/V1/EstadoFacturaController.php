<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreEstadoFacturaRequest;
use App\Http\Requests\Api\V1\UpdateEstadoFacturaRequest;
use App\Http\Resources\Api\V1\EstadoFacturaResource;
use App\Models\EstadoFactura;
use Illuminate\Http\JsonResponse;

class EstadoFacturaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return EstadoFactura::class;
    }

    protected function resourceClass(): ?string
    {
        return EstadoFacturaResource::class;
    }

    public function store(StoreEstadoFacturaRequest $request): JsonResponse
    {
        $record = EstadoFactura::query()->create($request->validated());

        return $this->created(
            EstadoFacturaResource::make($record)->resolve()
        );
    }

    public function update(UpdateEstadoFacturaRequest $request, int $id): JsonResponse
    {
        $record = EstadoFactura::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            EstadoFacturaResource::make($record)->resolve()
        );
    }
}
