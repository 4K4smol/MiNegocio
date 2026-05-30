<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Admin;

use App\Services\Admin\SolicitudVerificacionAdminService;
use App\Support\VerificacionRegistroRules;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSolicitudVerificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $solicitud = $this->solicitudActual();
        $estado = $solicitud?->estadoVerificacion?->nombre;

        return [
            'id' => $this->id,
            'empresa' => [
                'id' => $this->id,
                'nombre_fiscal' => $this->nombre_fiscal,
                'nombre_comercial' => $this->nombre_comercial,
                'nif' => $this->nif,
                'tipo_empresa' => $this->tipoEmpresa?->nombre,
                'activa' => (bool) $this->activa,
            ],
            'responsable' => $this->responsableBasico(),
            'estado_verificacion' => $estado,
            'estado_actual' => $estado,
            'estado_identidad' => $solicitud?->estado_identidad,
            'estado_empresa' => $solicitud?->estado_empresa,
            'estado_representacion' => $solicitud?->estado_representacion,
            'documentos_count' => $solicitud?->documentos_count ?? $solicitud?->documentos?->count() ?? 0,
            'fecha_solicitud' => $solicitud?->created_at,
            'acciones_disponibles' => $this->accionesDisponibles($estado, $solicitud),
        ];
    }

    private function solicitudActual()
    {
        return $this->relationLoaded('solicitudesVerificacion')
            ? $this->solicitudesVerificacion->sortByDesc('created_at')->first()
            : null;
    }

    private function responsableBasico(): ?array
    {
        $responsable = $this->solicitudActual()?->user;

        if ($responsable === null) {
            return null;
        }

        return [
            'id' => $responsable->id,
            'nombre' => $responsable->nombre,
            'apellido1' => $responsable->apellido1,
            'apellido2' => $responsable->apellido2,
            'email' => $responsable->email,
            'activo' => (bool) $responsable->activo,
        ];
    }

    private function accionesDisponibles(?string $estado, $solicitud): array
    {
        if ($estado === SolicitudVerificacionAdminService::ESTADO_RECHAZADA || $estado === SolicitudVerificacionAdminService::ESTADO_APROBADA) {
            return ['ver_detalle'];
        }

        $acciones = ['ver_detalle', 'rechazar'];

        if ($this->puedeAprobar($solicitud)) {
            $acciones[] = 'aprobar';
        }

        return $acciones;
    }

    private function puedeAprobar($solicitud): bool
    {
        if ($solicitud === null) {
            return false;
        }

        $representacionNoAplica = !VerificacionRegistroRules::requiereRepresentacion($this->tipoEmpresa?->nombre);

        return $solicitud->estado_identidad === SolicitudVerificacionAdminService::ESTADO_APROBADA
            && $solicitud->estado_empresa === SolicitudVerificacionAdminService::ESTADO_APROBADA
            && ($representacionNoAplica || $solicitud->estado_representacion === SolicitudVerificacionAdminService::ESTADO_APROBADA);
    }
}
