<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ModuloResource;
use App\Models\Empresa;
use App\Models\Modulo;
use App\Services\ModuloService;
use Illuminate\Http\JsonResponse;

class AdminEmpresaModuloController extends ApiController
{
    public function __construct(private readonly ModuloService $moduloService) {}

    public function index(Empresa $empresa): JsonResponse
    {
        $modulos = Modulo::query()
            ->with(['empresaModulos' => fn ($query) => $query->where('empresa_id', $empresa->id)])
            ->orderBy('orden_visual')
            ->orderBy('nombre')
            ->get()
            ->map(function (Modulo $modulo): array {
                $empresaModulo = $modulo->empresaModulos->first();

                return ModuloResource::make($modulo)->resolve() + [
                    'contratado' => $empresaModulo !== null,
                    'activo_empresa' => (bool) ($empresaModulo?->activo ?? false),
                    'fecha_activacion' => $empresaModulo?->fecha_activacion,
                    'fecha_desactivacion' => $empresaModulo?->fecha_desactivacion,
                ];
            });

        return $this->success([
            'empresa_id' => $empresa->id,
            'modulos' => $modulos,
        ]);
    }

    public function activar(Empresa $empresa, string $modulo): JsonResponse
    {
        $empresaModulo = $this->moduloService->activarModulo($empresa->id, $modulo);

        return $this->updated([
            'empresa_id' => $empresa->id,
            'modulo_id' => $empresaModulo->modulo_id,
            'activo' => (bool) $empresaModulo->activo,
        ], 'Modulo activado correctamente.');
    }

    public function desactivar(Empresa $empresa, string $modulo): JsonResponse
    {
        $this->moduloService->desactivarModulo($empresa->id, $modulo);

        return $this->updated([
            'empresa_id' => $empresa->id,
            'modulo' => $modulo,
            'activo' => false,
        ], 'Modulo desactivado correctamente.');
    }
}
