<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\TipoTarifaServicioResource;
use App\Models\TipoTarifaServicio;
use Illuminate\Http\JsonResponse;

class TipoTarifaServicioController extends ApiController
{
    public function index(): JsonResponse
    {
        $tipos = TipoTarifaServicio::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return $this->success(TipoTarifaServicioResource::collection($tipos)->resolve());
    }
}
