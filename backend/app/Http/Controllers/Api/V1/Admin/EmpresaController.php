<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\EmpresaResource;
use App\Models\AdminVerificacionEvento;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $empresas = Empresa::query()
            ->with(['tipoEmpresa', 'usuarios', 'solicitudesVerificacion.estadoVerificacion', 'modulos'])
            ->when($request->filled('estado'), function (Builder $query) use ($request): void {
                $estado = $request->string('estado')->toString();

                if ($estado === 'activa') {
                    $query->where('activa', true);
                    return;
                }

                if ($estado === 'inactiva') {
                    $query->where('activa', false);
                    return;
                }

                $query->whereHas('solicitudesVerificacion.estadoVerificacion', fn (Builder $estadoQuery) =>
                    $estadoQuery->where('nombre', $estado)
                );
            })
            ->when($request->filled('tipo_empresa'), fn (Builder $query) =>
                $query->whereHas('tipoEmpresa', fn (Builder $tipoQuery) =>
                    $tipoQuery->where('nombre', $request->string('tipo_empresa')->toString())
                )
            )
            ->when($request->filled('texto'), function (Builder $query) use ($request): void {
                $texto = '%'.$request->string('texto')->toString().'%';

                $query->where(fn (Builder $search) =>
                    $search->where('nombre_fiscal', 'like', $texto)
                        ->orWhere('nombre_comercial', 'like', $texto)
                        ->orWhere('nif', 'like', $texto)
                );
            })
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return $this->success(EmpresaResource::collection($empresas)->response()->getData(true));
    }

    public function show(Empresa $empresa): JsonResponse
    {
        return $this->success(new EmpresaResource($empresa->load([
            'tipoEmpresa',
            'usuarios.role',
            'solicitudesVerificacion.estadoVerificacion',
            'modulos',
        ])));
    }

    public function activar(Request $request, Empresa $empresa): JsonResponse
    {
        return $this->actualizarActiva($request, $empresa, true);
    }

    public function desactivar(Request $request, Empresa $empresa): JsonResponse
    {
        return $this->actualizarActiva($request, $empresa, false);
    }

    private function actualizarActiva(Request $request, Empresa $empresa, bool $activa): JsonResponse
    {
        $anterior = $empresa->activa ? 'activa' : 'inactiva';
        $empresa->update(['activa' => $activa]);

        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'empresa_id' => $empresa->id,
            'accion' => $activa ? 'activar_empresa' : 'desactivar_empresa',
            'estado_anterior' => $anterior,
            'estado_nuevo' => $activa ? 'activa' : 'inactiva',
        ]);

        return $this->success(
            new EmpresaResource($empresa->fresh(['tipoEmpresa', 'usuarios', 'modulos'])),
            'Empresa actualizada.',
        );
    }
}
