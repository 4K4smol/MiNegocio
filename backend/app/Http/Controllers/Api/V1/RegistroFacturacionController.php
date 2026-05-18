<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\RegistroFacturacion;
use App\Http\Resources\Api\V1\RegistroFacturacionResource;
use App\Services\RegistroFacturacionCadenaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistroFacturacionController extends AbstractCrudController
{
    public function __construct(private readonly RegistroFacturacionCadenaService $cadenaService) {}

    protected function modelClass(): string { return RegistroFacturacion::class; }
    protected function resourceClass(): ?string { return RegistroFacturacionResource::class; }

    public function exportar(Request $request): JsonResponse
    {
        $items = $this->baseQuery($request)->orderBy('id')->get();
        return $this->success(['items' => $items], 'Exportación de registros de facturación.');
    }

    public function validarCadena(Request $request): JsonResponse
    {
        $user = $request->user();
        $empresaId = strtolower((string) $user->role?->nombre) === 'admin'
            ? (int) $request->integer('empresa_id', (int) $user->empresa_id)
            : (int) $user->empresa_id;

        return $this->success($this->cadenaService->validarCadenaEmpresa($empresaId), 'Validación de cadena completada.');
    }
}
