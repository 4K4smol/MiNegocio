<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioRequest;
use App\Http\Requests\Api\V1\UpdateServicioRequest;
use App\Http\Resources\Api\V1\ServicioResource;
use App\Models\Servicio;
use App\Models\ServicioPrecio;
use App\Services\Servicios\TarifasEmpresaService;
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

        $precioBase = $data['precio_base'] ?? null;
        $servicioData = collect($data)
            ->except(['precio_base', 'iva_porcentaje', 'retencion_porcentaje', 'moneda', 'vigente_desde'])
            ->all();

        $servicio = Servicio::query()->create($servicioData);

        if ($precioBase !== null) {
            $tarifa = app(TarifasEmpresaService::class)->obtenerTarifaDefault((int) $data['empresa_id']);
            ServicioPrecio::query()->create([
                'servicio_id' => $servicio->id,
                'servicio_tarifa_id' => $tarifa->id,
                'precio_base' => $precioBase,
                'iva_porcentaje' => $data['iva_porcentaje'] ?? 21,
                'retencion_porcentaje' => $data['retencion_porcentaje'] ?? null,
                'moneda' => $data['moneda'] ?? 'EUR',
                'vigente_desde' => $data['vigente_desde'] ?? now()->toDateTimeString(),
            ]);
        }

        return $this->created(ServicioResource::make($servicio->loadCount('precios'))->resolve());
    }

    public function update(UpdateServicioRequest $request, int $id): JsonResponse
    {
        $servicio = $this->findRecord($request, $id);

        if ($servicio === null) {
            return $this->notFound();
        }

        $servicio->fill($this->fillEmpresaIdFromUser($request->validated(), $request));
        $servicio->save();

        return $this->updated(ServicioResource::make($servicio->loadCount('precios'))->resolve());
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

        return $this->updated(ServicioResource::make($servicio->loadCount('precios'))->resolve());
    }
}
