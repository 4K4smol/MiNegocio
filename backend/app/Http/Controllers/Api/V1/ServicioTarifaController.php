<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioTarifaRequest;
use App\Http\Requests\Api\V1\UpdateServicioTarifaRequest;
use App\Http\Resources\Api\V1\ServicioTarifaResource;
use App\Models\ServicioTarifa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicioTarifaController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return ServicioTarifa::class;
    }

    protected function resourceClass(): ?string
    {
        return ServicioTarifaResource::class;
    }

    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = (string) $request->string('search');
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('activo'), fn (Builder $query) => $query->where('activo', $request->boolean('activo')))
            ->orderByDesc('es_default')
            ->orderBy('nombre');
    }

    public function store(StoreServicioTarifaRequest $request): JsonResponse
    {
        $data = $this->fillEmpresaIdFromUser($request->validated(), $request);

        if (empty($data['empresa_id'])) {
            return $this->validationError(['empresa_id' => ['No se ha podido determinar la empresa asociada al usuario.']]);
        }

        $record = DB::transaction(function () use ($data): ServicioTarifa {
            if (! empty($data['es_default'])) {
                ServicioTarifa::query()->where('empresa_id', $data['empresa_id'])->update(['es_default' => false]);
            }

            return ServicioTarifa::query()->create($data);
        });

        return $this->created(ServicioTarifaResource::make($record)->resolve());
    }

    public function update(UpdateServicioTarifaRequest $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $data = $this->fillEmpresaIdFromUser($request->validated(), $request);

        DB::transaction(function () use ($record, $data): void {
            if (array_key_exists('es_default', $data) && (bool) $data['es_default'] === true) {
                ServicioTarifa::query()
                    ->where('empresa_id', $record->empresa_id)
                    ->whereKeyNot($record->id)
                    ->update(['es_default' => false]);
            }

            $record->fill($data);
            $record->save();
        });

        return $this->updated(ServicioTarifaResource::make($record->fresh())->resolve());
    }

    public function activar(Request $request, int $id): JsonResponse
    {
        return $this->cambiarActivo($request, $id, true);
    }

    public function desactivar(Request $request, int $id): JsonResponse
    {
        return $this->cambiarActivo($request, $id, false);
    }

    public function marcarDefault(Request $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        DB::transaction(function () use ($record): void {
            ServicioTarifa::query()
                ->where('empresa_id', $record->empresa_id)
                ->whereKeyNot($record->id)
                ->update(['es_default' => false]);

            $record->activo = true;
            $record->es_default = true;
            $record->save();
        });

        return $this->updated(ServicioTarifaResource::make($record->fresh())->resolve());
    }

    private function cambiarActivo(Request $request, int $id, bool $activo): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $record->activo = $activo;
        if (! $activo) {
            $record->es_default = false;
        }
        $record->save();

        return $this->updated(ServicioTarifaResource::make($record)->resolve());
    }
}
