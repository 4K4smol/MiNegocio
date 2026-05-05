<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreServicioRequest;
use App\Http\Requests\Api\V1\UpdateServicioRequest;
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

        return $this->success($servicios->toArray());
    }

    protected function modelClass(): string
    {
        return Servicio::class;
    }

    public function store(StoreServicioRequest $request)
    {
        $servicio = Servicio::query()->create($request->validated());

        return $this->created($servicio->toArray());
    }

    public function update(UpdateServicioRequest $request, int $id)
    {
        $servicio = Servicio::query()->find($id);

        if ($servicio === null) {
            return $this->notFound();
        }

        $servicio->fill($request->validated());
        $servicio->save();

        return $this->updated($servicio->toArray());
    }
}
