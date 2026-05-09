<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\RegistroEventoFacturacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistroEventoFacturacionController extends AbstractCrudController
{
    protected function modelClass(): string { return RegistroEventoFacturacion::class; }

    public function exportar(Request $request): JsonResponse
    {
        return $this->success(
            ['items' => $this->baseQuery($request)->orderBy('id')->get()],
            'Exportación de eventos de facturación.'
        );
    }
}
