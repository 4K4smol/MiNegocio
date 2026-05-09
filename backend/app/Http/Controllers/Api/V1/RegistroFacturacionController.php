<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\RegistroFacturacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistroFacturacionController extends AbstractCrudController
{
    protected function modelClass(): string { return RegistroFacturacion::class; }

    public function exportar(Request $request): JsonResponse
    {
        $items = $this->baseQuery($request)->orderBy('id')->get();
        return $this->success(['items' => $items], 'Exportación de registros de facturación.');
    }
}
