<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\V1\Admin\AprobarSolicitudVerificacionRequest;
use App\Http\Requests\Api\V1\Admin\RechazarSolicitudVerificacionRequest;
use App\Http\Requests\Api\V1\Admin\SolicitarSubsanacionRequest;
use App\Http\Resources\Api\V1\Admin\AdminSolicitudVerificacionDetalleResource;
use App\Http\Resources\Api\V1\Admin\AdminSolicitudVerificacionResource;
use App\Models\Empresa;
use App\Services\Admin\SolicitudVerificacionAdminService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SolicitudVerificacionController extends ApiController
{
    public function __construct(private readonly SolicitudVerificacionAdminService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $orden = $request->string('orden', 'desc')->toString() === 'asc' ? 'asc' : 'desc';
        $estado = $request->string('estado', $request->string('estado_verificacion')->toString())->toString();

        $empresas = Empresa::query()
            ->with([
                'tipoEmpresa',
                'solicitudesVerificacion' => fn ($query) => $query
                    ->with(['estadoVerificacion', 'user'])
                    ->withCount('documentos')
                    ->latest(),
            ])
            ->whereHas('solicitudesVerificacion')
            ->when($estado !== '', fn (Builder $query) =>
                $query->whereHas('solicitudesVerificacion.estadoVerificacion', fn (Builder $estadoQuery) =>
                    $estadoQuery->where('nombre', $estado)
                )
            )
            ->when($request->filled('tipo_empresa'), fn (Builder $query) =>
                $query->whereHas('tipoEmpresa', fn (Builder $tipoQuery) =>
                    $tipoQuery->where('nombre', $request->string('tipo_empresa')->toString())
                )
            )
            ->when($request->filled('texto'), function (Builder $query) use ($request): void {
                $texto = '%'.$request->string('texto')->toString().'%';

                $query->where(function (Builder $search) use ($texto): void {
                    $search->where('nombre_fiscal', 'like', $texto)
                        ->orWhere('nombre_comercial', 'like', $texto)
                        ->orWhere('nif', 'like', $texto)
                        ->orWhereHas('solicitudesVerificacion.user', fn (Builder $userQuery) =>
                            $userQuery->where('email', 'like', $texto)
                                ->orWhere('nombre', 'like', $texto)
                                ->orWhere('apellido1', 'like', $texto)
                        );
                });
            })
            ->orderBy(
                \App\Models\SolicitudVerificacion::query()
                    ->select('created_at')
                    ->whereColumn('solicitudes_verificacion.empresa_id', 'empresas.id')
                    ->latest()
                    ->limit(1),
                $orden,
            )
            ->paginate((int) $request->integer('per_page', 20));

        return $this->success(AdminSolicitudVerificacionResource::collection($empresas)->response()->getData(true));
    }

    public function show(Empresa $empresa): JsonResponse
    {
        return $this->success(new AdminSolicitudVerificacionDetalleResource($this->cargarEmpresa($empresa)));
    }

    public function aprobar(AprobarSolicitudVerificacionRequest $request, Empresa $empresa): JsonResponse
    {
        $empresa = $this->service->aprobar($empresa, $request->user(), $request->validated());

        return $this->success(
            new AdminSolicitudVerificacionDetalleResource($this->cargarEmpresa($empresa)),
            'Solicitud aprobada correctamente.',
        );
    }

    public function rechazar(RechazarSolicitudVerificacionRequest $request, Empresa $empresa): JsonResponse
    {
        $empresa = $this->service->rechazar($empresa, $request->user(), $request->validated());

        return $this->success(
            new AdminSolicitudVerificacionDetalleResource($this->cargarEmpresa($empresa)),
            'Solicitud rechazada correctamente.',
        );
    }

    public function solicitarSubsanacion(SolicitarSubsanacionRequest $request, Empresa $empresa): JsonResponse
    {
        $empresa = $this->service->solicitarSubsanacion($empresa, $request->user(), $request->validated());

        return $this->success(
            new AdminSolicitudVerificacionDetalleResource($this->cargarEmpresa($empresa)),
            'Subsanacion solicitada correctamente.',
        );
    }

    private function cargarEmpresa(Empresa $empresa): Empresa
    {
        return $empresa->load([
            'tipoEmpresa',
            'verificacion.estadoVerificacion',
            'solicitudesVerificacion' => fn ($query) => $query->with([
                'estadoVerificacion',
                'user.verificacion.estadoVerificacion',
                'user.verificacion.tipoDocumentoIdentidad',
                'documentos',
                'eventosAdmin.admin',
            ])->latest(),
        ]);
    }
}
