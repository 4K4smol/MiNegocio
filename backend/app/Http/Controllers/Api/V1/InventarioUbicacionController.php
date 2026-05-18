<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreInventarioUbicacionRequest;
use App\Http\Requests\Api\V1\UpdateInventarioUbicacionRequest;
use App\Http\Resources\Api\V1\InventarioUbicacionResource;
use App\Models\InventarioUbicacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioUbicacionController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return InventarioUbicacion::class;
    }

    protected function resourceClass(): ?string
    {
        return InventarioUbicacionResource::class;
    }

    public function store(StoreInventarioUbicacionRequest $request): JsonResponse
    {
        $record = InventarioUbicacion::query()->create(
            $this->fillEmpresaIdFromUser($request->validated(), $request)
        );

        return $this->created(
            InventarioUbicacionResource::make($record)->resolve()
        );
    }

    public function update(UpdateInventarioUbicacionRequest $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->fill($this->fillEmpresaIdFromUser($request->validated(), $request));
        $record->save();

        return $this->updated(
            InventarioUbicacionResource::make($record->fresh())->resolve()
        );
    }

    public function activar(Request $request, int $id): JsonResponse
    {
        return $this->cambiarActivo($request, $id, true);
    }

    public function desactivar(Request $request, int $id): JsonResponse
    {
        return $this->cambiarActivo($request, $id, false);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        if ($record->items()->exists()) {
            return $this->validationError([
                'ubicacion' => ['No se puede eliminar una ubicacion con inventario asociado. Desactivala si ya no se usa.'],
            ]);
        }

        $record->delete();

        return $this->deleted();
    }

    private function cambiarActivo(Request $request, int $id, bool $activo): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->activo = $activo;
        $record->save();

        return $this->updated(
            InventarioUbicacionResource::make($record->fresh())->resolve(),
            'Ubicacion actualizada correctamente.'
        );
    }

}
