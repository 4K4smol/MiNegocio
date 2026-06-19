<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioItemRequest;
use App\Http\Requests\Api\V1\UpdateInventarioItemRequest;
use App\Http\Resources\Api\V1\InventarioItemResource;
use App\Models\InventarioExistencia;
use App\Models\InventarioItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->with(['unidadMedida', 'ubicacion', 'existencias.ubicacion'])
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->when($request->filled('ubicacion_id'), function (Builder $query) use ($request): void {
                $query->whereHas('existencias', fn (Builder $existencias) => $existencias
                    ->where('ubicacion_id', $request->integer('ubicacion_id'))
                    ->where('cantidad', '>', 0));
            })
            ->when($request->boolean('stock_bajo'), fn (Builder $query) => $query->whereColumn('stock_actual', '<=', 'stock_minimo'))
            ->when(
                $request->string('sort')->toString() === 'created_at',
                fn (Builder $query) => $query->orderByDesc('created_at'),
                fn (Builder $query) => $query->orderBy('nombre')
            );
    }

    public function store(StoreInventarioItemRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request): InventarioItem {
            $data = $this->fillEmpresaIdFromUser($request->validated(), $request);
            $record = InventarioItem::query()->create($data);
            $this->sincronizarExistenciaInicial($record, $data);

            return $record;
        });

        return $this->created(
            InventarioItemResource::make($record->load(['unidadMedida', 'ubicacion', 'existencias.ubicacion']))->resolve()
        );
    }

    public function update(UpdateInventarioItemRequest $request, int $id): JsonResponse
    {
        $record = $this->findRecordForMutation($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        DB::transaction(function () use ($record, $request): void {
            $data = $this->fillEmpresaIdFromUser($request->validated(), $request);
            $record->fill($data);
            $record->save();
            $this->sincronizarExistenciaInicial($record, $data);
        });

        return $this->updated(
            InventarioItemResource::make($record->load(['unidadMedida', 'ubicacion', 'existencias.ubicacion']))->resolve()
        );
    }

    private function sincronizarExistenciaInicial(InventarioItem $record, array $data): void
    {
        if (!array_key_exists('stock_actual', $data) && !array_key_exists('ubicacion_id', $data)) {
            return;
        }

        $ubicacionId = $data['ubicacion_id'] ?? $record->ubicacion_id;
        $stockActual = (float) ($data['stock_actual'] ?? $record->stock_actual ?? 0);

        if ($ubicacionId === null && $stockActual > 0) {
            $ubicacionId = $this->ubicacionSinAsignarId((int) $record->empresa_id);
        }

        if ($ubicacionId === null) {
            $record->existencias()->update(['cantidad' => 0]);
            $record->stock_actual = 0;
            $record->save();

            return;
        }

        $record->existencias()
            ->where('ubicacion_id', '!=', $ubicacionId)
            ->update(['cantidad' => 0]);

        InventarioExistencia::query()->updateOrCreate(
            [
                'inventario_item_id' => $record->id,
                'ubicacion_id' => $ubicacionId,
            ],
            [
                'empresa_id' => $record->empresa_id,
                'cantidad' => $stockActual,
            ]
        );

        $record->stock_actual = $stockActual;
        $record->ubicacion_id = $ubicacionId;
        $record->save();
    }

    private function ubicacionSinAsignarId(int $empresaId): int
    {
        $ubicacionId = DB::table('inventario_ubicaciones')->where([
            'empresa_id' => $empresaId,
            'nombre' => 'Sin ubicacion',
        ])->value('id');

        if ($ubicacionId !== null) {
            return (int) $ubicacionId;
        }

        return (int) DB::table('inventario_ubicaciones')->insertGetId([
            'empresa_id' => $empresaId,
            'nombre' => 'Sin ubicacion',
            'descripcion' => 'Ubicacion creada automaticamente para stock sin ubicacion.',
            'observaciones' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function findRecordForMutation(Request $request, int $id): ?InventarioItem
    {
        $query = InventarioItem::query()
            ->with(['unidadMedida', 'ubicacion', 'existencias.ubicacion'])
            ->whereKey($id);

        $user = $request->user();

        if ($user instanceof User && $user->role?->nombre !== 'admin') {
            $query->where('empresa_id', $user->empresa_id);
        }

        return $query->first();
    }
}
