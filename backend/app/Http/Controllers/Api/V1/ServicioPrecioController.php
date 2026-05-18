<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioPrecioRequest;
use App\Http\Requests\Api\V1\UpdateServicioPrecioRequest;
use App\Http\Resources\Api\V1\ServicioPrecioResource;
use App\Models\Servicio;
use App\Models\ServicioPrecio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicioPrecioController extends AbstractCrudController
{
    protected function modelClass(): string
    {
        return ServicioPrecio::class;
    }

    protected function resourceClass(): ?string
    {
        return ServicioPrecioResource::class;
    }

    protected function baseQuery(Request $request): Builder
    {
        $empresaId = (int) $request->user()->empresa_id;

        return ServicioPrecio::query()
            ->with(['servicio', 'tarifa'])
            ->whereHas('servicio', fn (Builder $query) => $query->where('empresa_id', $empresaId));
    }

    public function indexByServicio(Request $request, int $servicio): JsonResponse
    {
        $servicioModel = Servicio::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->whereKey($servicio)
            ->first();

        if ($servicioModel === null) {
            return $this->notFound('Servicio no encontrado.');
        }

        $precios = $this->baseQuery($request)
            ->where('servicio_id', $servicioModel->id)
            ->orderBy('tipo_tarifa_servicio_id')
            ->get();

        return $this->success(ServicioPrecioResource::collection($precios)->resolve());
    }

    public function storeForServicio(StoreServicioPrecioRequest $request, int $servicio): JsonResponse
    {
        return $this->store($request);
    }

    public function store(StoreServicioPrecioRequest $request): JsonResponse
    {
        $data = $request->validated();

        $record = DB::transaction(function () use ($data): ServicioPrecio {
            if ($this->existePrecioDuplicado($data)) {
                abort(422, 'Ya existe un precio para este servicio y tipo de tarifa.');
            }

            return ServicioPrecio::query()->create($data);
        });

        return $this->created(ServicioPrecioResource::make($record->load(['servicio', 'tarifa']))->resolve());
    }

    public function update(UpdateServicioPrecioRequest $request, int $id): JsonResponse
    {
        $record = $this->findRecord($request, $id);

        if ($record === null) {
            return $this->notFound();
        }

        $data = $request->validated();
        $data['servicio_id'] = $data['servicio_id'] ?? $record->servicio_id;
        $data['tipo_tarifa_servicio_id'] = $data['tipo_tarifa_servicio_id'] ?? $record->tipo_tarifa_servicio_id;

        DB::transaction(function () use ($record, $data): void {
            if ($this->existePrecioDuplicado($data, $record->id)) {
                abort(422, 'Ya existe un precio para este servicio y tipo de tarifa.');
            }

            $record->fill($data);
            $record->save();
        });

        return $this->updated(ServicioPrecioResource::make($record->load(['servicio', 'tarifa']))->resolve());
    }

    private function existePrecioDuplicado(array $data, ?int $ignoreId = null): bool
    {
        return ServicioPrecio::query()
            ->where('servicio_id', $data['servicio_id'])
            ->where('tipo_tarifa_servicio_id', $data['tipo_tarifa_servicio_id'])
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
