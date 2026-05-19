<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioItemRequest;
use App\Http\Requests\Api\V1\UpdateInventarioItemRequest;
use App\Http\Resources\Api\V1\InventarioItemResource;
use App\Models\InventarioItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['unidadMedida', 'ubicacion'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->when($request->filled('ubicacion_id'), fn (Builder $query) => $query->where('ubicacion_id', $request->integer('ubicacion_id')))
            ->when($request->boolean('stock_bajo'), fn (Builder $query) => $query->whereColumn('stock_actual', '<=', 'stock_minimo'))
            ->when(
                $request->string('sort')->toString() === 'created_at',
                fn (Builder $query) => $query->orderByDesc('created_at'),
                fn (Builder $query) => $query->orderBy('nombre')
            );
    }

    public function store(StoreInventarioItemRequest $request): JsonResponse
    {
        $record = InventarioItem::query()->create($this->fillEmpresaIdFromUser($request->validated(), $request));

        return $this->created(
            InventarioItemResource::make($record->load(['unidadMedida', 'ubicacion']))->resolve()
        );
    }

    public function update(UpdateInventarioItemRequest $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($this->fillEmpresaIdFromUser($request->validated(), $request));
        $record->save();

        return $this->updated(
            InventarioItemResource::make($record->load(['unidadMedida', 'ubicacion']))->resolve()
        );
    }
}
