<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioMovimientoRequest;
use App\Http\Resources\Api\V1\InventarioMovimientoResource;
use App\Models\InventarioMovimiento;
use App\Services\InventarioMovimientoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioMovimientoController extends AbstractCrudController
{
    public function __construct(private readonly InventarioMovimientoService $movimientoService)
    {
    }

    protected function modelClass(): string
    {
        return InventarioMovimiento::class;
    }

    protected function resourceClass(): ?string
    {
        return InventarioMovimientoResource::class;
    }

    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->with(['item.unidadMedida', 'item.ubicacion', 'item.existencias.ubicacion', 'tipoMovimiento', 'ubicacionOrigen', 'ubicacionDestino', 'user'])
            ->when($request->filled('inventario_item_id'), fn (Builder $query) => $query->where('inventario_item_id', $request->integer('inventario_item_id')))
            ->when($request->filled('tipo_movimiento_id'), fn (Builder $query) => $query->where('tipo_movimiento_id', $request->integer('tipo_movimiento_id')))
            ->orderByDesc('fecha_movimiento');
    }

    public function store(StoreInventarioMovimientoRequest $request): JsonResponse
    {
        $record = $this->movimientoService->registrar($request->validated(), $request->user());

        return $this->created(
            InventarioMovimientoResource::make($record)->resolve()
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->error('Los movimientos de inventario no se pueden modificar.', 405);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        return $this->error('Los movimientos de inventario no se pueden eliminar.', 405);
    }
}
