<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioCategoriaRequest;
use App\Http\Requests\Api\V1\UpdateInventarioCategoriaRequest;
use App\Http\Resources\Api\V1\InventarioCategoriaResource;
use App\Models\InventarioCategoria;
use Illuminate\Http\JsonResponse;

class InventarioCategoriaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return InventarioCategoria::class;
    }

    protected function resourceClass(): ?string
    {
        return InventarioCategoriaResource::class;
    }

    public function store(StoreInventarioCategoriaRequest $request): JsonResponse
    {
        $record = InventarioCategoria::query()->create($this->fillEmpresaIdFromUser($request->validated(), $request));

        return $this->created(
            InventarioCategoriaResource::make($record)->resolve()
        );
    }

    public function update(UpdateInventarioCategoriaRequest $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($this->fillEmpresaIdFromUser($request->validated(), $request));
        $record->save();

        return $this->updated(
            InventarioCategoriaResource::make($record)->resolve()
        );
    }
}
