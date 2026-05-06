<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioItemRequest;
use App\Http\Requests\Api\V1\UpdateInventarioItemRequest;
use App\Http\Resources\Api\V1\InventarioItemResource;
use App\Models\InventarioItem;
use Illuminate\Http\JsonResponse;

class InventarioItemController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return InventarioItem::class;
    }

    protected function resourceClass(): ?string
    {
        return InventarioItemResource::class;
    }

    public function store(StoreInventarioItemRequest $request): JsonResponse
    {
        $record = InventarioItem::query()->create($request->validated());

        return $this->created(
            InventarioItemResource::make($record)->resolve()
        );
    }

    public function update(UpdateInventarioItemRequest $request, int $id): JsonResponse
    {
        $record = InventarioItem::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($request->validated());
        $record->save();

        return $this->updated(
            InventarioItemResource::make($record)->resolve()
        );
    }
}
