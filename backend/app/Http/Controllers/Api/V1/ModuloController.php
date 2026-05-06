<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreModuloRequest;
use App\Http\Requests\Api\V1\UpdateModuloRequest;
use App\Http\Resources\Api\V1\ModuloResource;
use App\Models\Modulo;
use Illuminate\Http\JsonResponse;

class ModuloController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Modulo::class;
    }

    protected function resourceClass(): ?string
    {
        return ModuloResource::class;
    }

    public function store(StoreModuloRequest $request): JsonResponse
    {
        $record = Modulo::query()->create($request->validated());

        return $this->created(
            ModuloResource::make($record)->resolve()
        );
    }

    public function update(UpdateModuloRequest $request, int $id): JsonResponse
    {
        $record = Modulo::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            ModuloResource::make($record)->resolve()
        );
    }
}
