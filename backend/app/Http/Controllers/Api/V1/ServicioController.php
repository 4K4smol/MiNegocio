<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioRequest;
use App\Http\Requests\Api\V1\UpdateServicioRequest;
use App\Http\Resources\Api\V1\ServicioResource;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicioController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return Servicio::class;
    }

    protected function resourceClass(): ?string
    {
        return ServicioResource::class;
    }

    protected function baseQuery(Request $request): Builder
    {
        $query = parent::baseQuery($request)
            ->withCount('precios')
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('activo'), fn (Builder $query) => $query->where('activo', $request->boolean('activo')))
            ->when($request->filled('tipo_negocio'), fn (Builder $query) => $query->where('tipo_negocio', $request->string('tipo_negocio')))
            ->orderBy('nombre');

        if (str_contains((string) $request->query('include'), 'precios')) {
            $query->with(['precios.tarifa']);
        }

        return $query;
    }

    public function store(StoreServicioRequest $request): JsonResponse
    {
        $data = $this->fillEmpresaIdFromUser($request->validated(), $request);

        if (empty($data['empresa_id'])) {
            return $this->validationError(['empresa_id' => ['No se ha podido determinar la empresa asociada al usuario.']]);
        }

        $servicio = Servicio::query()->create($data);

        return $this->created(
            ServicioResource::make($servicio->fresh()->loadCount('precios'))->resolve()
        );
    }

    public function update(UpdateServicioRequest $request, int $id): JsonResponse
    {
        $servicio = $this->findRecord($request, $id);

        if ($servicio === null) {
            return $this->notFound();
        }

        $servicio->fill($this->fillEmpresaIdFromUser($request->validated(), $request));
        $servicio->save();

        return $this->updated(
            ServicioResource::make($servicio->fresh()->loadCount('precios'))->resolve()
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

    private function cambiarActivo(Request $request, int $id, bool $activo): JsonResponse
    {
        $servicio = $this->findRecord($request, $id);

        if ($servicio === null) {
            return $this->notFound();
        }

        $servicio->activo = $activo;
        $servicio->save();

        return $this->updated(
            ServicioResource::make($servicio->fresh()->loadCount('precios'))->resolve()
        );
    }
}
