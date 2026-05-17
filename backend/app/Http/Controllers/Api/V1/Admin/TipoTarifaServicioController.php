<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Admin\StoreTipoTarifaServicioRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTipoTarifaServicioRequest;
use App\Http\Resources\Api\V1\TipoTarifaServicioResource;
use App\Models\TipoTarifaServicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoTarifaServicioController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = TipoTarifaServicio::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where(function ($inner) use ($search): void {
                    $inner->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->orderBy('orden')
            ->orderBy('nombre');

        return $this->success(TipoTarifaServicioResource::collection($query->get())->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $tipo = TipoTarifaServicio::query()->find($id);

        if ($tipo === null) {
            return $this->notFound();
        }

        return $this->success(TipoTarifaServicioResource::make($tipo)->resolve());
    }

    public function store(StoreTipoTarifaServicioRequest $request): JsonResponse
    {
        $tipo = TipoTarifaServicio::query()->create($request->validated());

        return $this->created(TipoTarifaServicioResource::make($tipo)->resolve());
    }

    public function update(UpdateTipoTarifaServicioRequest $request, int $id): JsonResponse
    {
        $tipo = TipoTarifaServicio::query()->find($id);

        if ($tipo === null) {
            return $this->notFound();
        }

        $tipo->fill($request->validated());
        $tipo->save();

        return $this->updated(TipoTarifaServicioResource::make($tipo->fresh())->resolve());
    }

    public function activar(int $id): JsonResponse
    {
        return $this->cambiarActivo($id, true);
    }

    public function desactivar(int $id): JsonResponse
    {
        return $this->cambiarActivo($id, false);
    }

    private function cambiarActivo(int $id, bool $activo): JsonResponse
    {
        $tipo = TipoTarifaServicio::query()->find($id);

        if ($tipo === null) {
            return $this->notFound();
        }

        $tipo->activo = $activo;
        $tipo->save();

        return $this->updated(TipoTarifaServicioResource::make($tipo)->resolve());
    }
}
