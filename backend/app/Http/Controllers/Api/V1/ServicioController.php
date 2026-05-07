<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioRequest;
use App\Http\Requests\Api\V1\UpdateServicioRequest;
use App\Http\Resources\Api\V1\ServicioResource;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicioController extends AbstractCrudController
{
    public function index(Request $request): JsonResponse
    {
        $per_page = (int) $request->integer('per_page', 15);
        $servicios = Servicio::query()
            ->with(['empresa'])
            ->paginate($per_page);

        return $this->success(ServicioResource::collection($servicios)->response()->getData(true));
    }

    protected function modelClass(): string
    {
        return Servicio::class;
    }

    protected function resourceClass(): ?string
    {
        return ServicioResource::class;
    }

    public function store(StoreServicioRequest $request)
    {
        $servicio = Servicio::query()->create($this->fillEmpresaIdFromUser($request->validated(), $request));

        return $this->created(
            ServicioResource::make($servicio)->resolve()
        );
    }

    public function update(UpdateServicioRequest $request, int $id)
    {
        $servicio = $this->findRecord($request, $id);

        if ($servicio === null) {
            return $this->notFound();
        }

        $servicio->fill($this->fillEmpresaIdFromUser($request->validated(), $request));
        $servicio->save();

        return $this->updated(
            ServicioResource::make($servicio)->resolve()
        );
    }
}
