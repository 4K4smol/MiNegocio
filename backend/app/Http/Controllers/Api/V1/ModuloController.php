<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreModuloRequest;
use App\Http\Requests\Api\V1\UpdateModuloRequest;
use App\Http\Resources\Api\V1\ModuloResource;
use App\Models\AdminVerificacionEvento;
use App\Models\Modulo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function activar(Request $request, int $id): JsonResponse
    {
        return $this->actualizarActivo($request, $id, true);
    }

    public function desactivar(Request $request, int $id): JsonResponse
    {
        return $this->actualizarActivo($request, $id, false);
    }

    private function actualizarActivo(Request $request, int $id, bool $activo): JsonResponse
    {
        $record = Modulo::query()->find($id);

        if ($record === null) {
            return $this->notFound();
        }

        $anterior = $record->activo ? 'activo' : 'inactivo';
        $record->update(['activo' => $activo]);

        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'accion' => $activo ? 'activar_modulo_global' : 'desactivar_modulo_global',
            'estado_anterior' => $anterior,
            'estado_nuevo' => $activo ? 'activo' : 'inactivo',
            'metadatos' => ['modulo_id' => $record->id, 'codigo' => $record->codigo],
        ]);

        return $this->updated(ModuloResource::make($record->fresh())->resolve(), 'Modulo actualizado correctamente.');
    }
}
