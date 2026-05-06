<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioPrecioRequest;
use App\Http\Requests\Api\V1\UpdateServicioPrecioRequest;
use App\Http\Resources\Api\V1\ServicioPrecioResource;
use App\Models\ServicioPrecio;
use Illuminate\Http\JsonResponse;

class ServicioPrecioController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return ServicioPrecio::class;
    }

    protected function resourceClass(): ?string
    {
        return ServicioPrecioResource::class;
    }

    public function store(StoreServicioPrecioRequest $request): JsonResponse
    {
        $record = ServicioPrecio::query()->create($request->validated());

        return $this->created(
            ServicioPrecioResource::make($record)->resolve()
        );
    }

    public function update(UpdateServicioPrecioRequest $request, int $id): JsonResponse
    {
        $record = ServicioPrecio::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            ServicioPrecioResource::make($record)->resolve()
        );
    }
}
