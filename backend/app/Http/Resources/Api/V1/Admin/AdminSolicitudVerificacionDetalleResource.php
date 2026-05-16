<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1\Admin;

use App\Services\Admin\SolicitudVerificacionAdminService;
use App\Support\VerificacionRegistroRules;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSolicitudVerificacionDetalleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $solicitud = $this->solicitudActual();
        $responsable = $solicitud?->user;
        $estado = $solicitud?->estadoVerificacion?->nombre;

        return [
            'id' => $this->id,
            'empresa' => [
                'id' => $this->id,
                'nombre_fiscal' => $this->nombre_fiscal,
                'nombre_comercial' => $this->nombre_comercial,
                'nif' => $this->nif,
                'correo' => $this->correo,
                'telefono' => $this->telefono,
                'direccion_fiscal' => $this->direccion_fiscal,
                'codigo_postal' => $this->codigo_postal,
                'municipio' => $this->municipio,
                'provincia' => $this->provincia,
                'pais' => $this->pais,
                'tipo_empresa' => $this->tipoEmpresa?->nombre,
                'activa' => (bool) $this->activa,
            ],
            'responsable' => $responsable ? [
                'id' => $responsable->id,
                'name' => $responsable->name,
                'nombre' => $responsable->nombre,
                'apellido1' => $responsable->apellido1,
                'apellido2' => $responsable->apellido2,
                'email' => $responsable->email,
                'telefono' => $responsable->telefono,
                'activo' => (bool) $responsable->activo,
            ] : null,
            'verificaciones_usuario' => $responsable?->verificacion ? [
                'id' => $responsable->verificacion->id,
                'estado_verificacion' => $responsable->verificacion->estadoVerificacion?->nombre,
                'tipo_documento_identidad' => $responsable->verificacion->tipoDocumentoIdentidad?->nombre,
                'numero_documento' => $responsable->verificacion->numero_documento,
                'fecha_verificacion' => $responsable->verificacion->fecha_verificacion,
                'notas_revision' => $responsable->verificacion->notas_revision,
            ] : null,
            'verificaciones_empresa' => $this->verificacion ? [
                'id' => $this->verificacion->id,
                'estado_verificacion' => $this->verificacion->estadoVerificacion?->nombre,
                'referencia_certificado_digital' => $this->verificacion->referencia_certificado_digital,
                'fecha_verificacion' => $this->verificacion->fecha_verificacion,
                'notas_revision' => $this->verificacion->notas_revision,
            ] : null,
            'documentos' => $this->documentosAgrupados($solicitud),
            'historial' => $solicitud?->eventosAdmin?->map(fn ($evento) => [
                'id' => $evento->id,
                'accion' => $evento->accion,
                'estado_anterior' => $evento->estado_anterior,
                'estado_nuevo' => $evento->estado_nuevo,
                'motivo' => $evento->motivo,
                'metadatos' => $evento->metadatos,
                'created_at' => $evento->created_at,
                'admin' => $evento->admin ? [
                    'id' => $evento->admin->id,
                    'nombre' => $evento->admin->nombre ?? $evento->admin->name,
                    'email' => $evento->admin->email,
                ] : null,
            ])->values() ?? [],
            'estado_verificacion' => $estado,
            'estado_actual' => $estado,
            'estado_identidad' => $solicitud?->estado_identidad,
            'estado_empresa' => $solicitud?->estado_empresa,
            'estado_representacion' => $solicitud?->estado_representacion,
            'observaciones' => $solicitud?->observaciones,
            'motivo_rechazo' => $solicitud?->motivo_rechazo,
            'fecha_solicitud' => $solicitud?->created_at,
            'fecha_revision' => $solicitud?->fecha_revision,
            'acciones_disponibles' => $this->accionesDisponibles($estado, $solicitud),
        ];
    }

    private function solicitudActual()
    {
        return $this->relationLoaded('solicitudesVerificacion')
            ? $this->solicitudesVerificacion->sortByDesc('created_at')->first()
            : null;
    }

    private function documentosAgrupados($solicitud): array
    {
        $grupos = [
            'identidad' => [],
            'empresa_actividad' => [],
            'representacion' => [],
            'otros' => [],
        ];

        foreach ($solicitud?->documentos ?? [] as $documento) {
            $grupos[$this->grupoDocumento($documento->tipo_documento)][] = (new AdminDocumentoVerificacionResource($documento))->toArray(request());
        }

        return $grupos;
    }

    private function grupoDocumento(string $tipo): string
    {
        return match ($tipo) {
            'dni_frontal', 'dni_reverso', 'selfie', 'identidad' => 'identidad',
            'documento_fiscal', 'registro_mercantil', 'empresa_actividad' => 'empresa_actividad',
            'documento_representacion', 'poder_apoderamiento', 'representacion' => 'representacion',
            default => 'otros',
        };
    }

    private function accionesDisponibles(?string $estado, $solicitud): array
    {
        if ($estado === SolicitudVerificacionAdminService::ESTADO_RECHAZADA || $estado === SolicitudVerificacionAdminService::ESTADO_APROBADA) {
            return ['ver_detalle'];
        }

        $acciones = ['ver_detalle', 'preview_documentos', 'rechazar', 'solicitar_subsanacion'];

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

        $representacionNoAplica = ! VerificacionRegistroRules::requiereRepresentacion($this->tipoEmpresa?->nombre);

        return $solicitud->estado_identidad === SolicitudVerificacionAdminService::ESTADO_APROBADA
            && $solicitud->estado_empresa === SolicitudVerificacionAdminService::ESTADO_APROBADA
            && ($representacionNoAplica || $solicitud->estado_representacion === SolicitudVerificacionAdminService::ESTADO_APROBADA);
    }
}
