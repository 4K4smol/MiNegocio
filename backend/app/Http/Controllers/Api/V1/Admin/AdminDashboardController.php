<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\Admin\AdminAuditoriaEventoResource;
use App\Http\Resources\Api\V1\Admin\AdminSolicitudVerificacionResource;
use App\Models\AdminVerificacionEvento;
use App\Models\Empresa;
use App\Models\SolicitudVerificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends ApiController
{
    public function index(): JsonResponse
    {
        $solicitudesRecientes = Empresa::query()
            ->with([
                'tipoEmpresa',
                'solicitudesVerificacion' => fn ($query) => $query
                    ->with(['estadoVerificacion', 'user'])
                    ->withCount('documentos')
                    ->latest(),
            ])
            ->whereHas('solicitudesVerificacion')
            ->latest()
            ->limit(6)
            ->get();

        $eventosRecientes = AdminVerificacionEvento::query()
            ->with(['admin', 'usuario', 'empresa'])
            ->latest()
            ->limit(8)
            ->get();

        return $this->success([
            'metricas' => [
                'total_empresas' => Empresa::query()->count(),
                'empresas_activas' => Empresa::query()->where('activa', true)->count(),
                'empresas_pendientes' => $this->solicitudesPorEstado('pendiente'),
                'empresas_aprobadas' => $this->solicitudesPorEstado('aprobada'),
                'empresas_rechazadas' => $this->solicitudesPorEstado('rechazada'),
                'usuarios_total' => User::query()->count(),
                'usuarios_activos' => User::query()->where('activo', true)->count(),
                'solicitudes_pendientes' => $this->solicitudesPorEstado('pendiente'),
                'documentos_pendientes' => SolicitudVerificacion::query()
                    ->where(function (Builder $query): void {
                        $query->where('estado_identidad', 'pendiente')
                            ->orWhere('estado_empresa', 'pendiente')
                            ->orWhere('estado_representacion', 'pendiente');
                    })
                    ->count(),
            ],
            'solicitudes_recientes' => AdminSolicitudVerificacionResource::collection($solicitudesRecientes),
            'eventos_recientes' => AdminAuditoriaEventoResource::collection($eventosRecientes),
        ]);
    }

    private function solicitudesPorEstado(string $estado): int
    {
        return SolicitudVerificacion::query()
            ->whereHas('estadoVerificacion', fn (Builder $query) => $query->where('nombre', $estado))
            ->count();
    }
}
