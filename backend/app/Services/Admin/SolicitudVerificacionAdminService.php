<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\AdminVerificacionEvento;
use App\Models\Empresa;
use App\Models\EstadoVerificacion;
use App\Models\SolicitudVerificacion;
use App\Models\User;
use App\Support\VerificacionRegistroRules;
use Illuminate\Support\Facades\DB;

class SolicitudVerificacionAdminService
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_EN_REVISION = 'en_revision';
    public const ESTADO_SUBSANACION = 'subsanacion';
    public const ESTADO_APROBADA = 'aprobada';
    public const ESTADO_RECHAZADA = 'rechazada';

    private const FASES = [
        'identidad' => 'estado_identidad',
        'empresa' => 'estado_empresa',
        'representacion' => 'estado_representacion',
    ];

    /**
     * @param array{observaciones?: string|null} $data
     */
    public function aprobar(Empresa $empresa, User $admin, array $data = []): Empresa
    {
        return DB::transaction(function () use ($empresa, $admin, $data): Empresa {
            $solicitud = $this->bloquearSolicitudActual($empresa);
            $estadoAnterior = $solicitud->estadoVerificacion?->nombre;

            if (! in_array($estadoAnterior, [self::ESTADO_PENDIENTE, self::ESTADO_EN_REVISION, self::ESTADO_SUBSANACION], true)) {
                abort(422, 'La solicitud no se puede aprobar desde su estado actual.');
            }

            abort_unless($this->puedeAprobarSolicitud($solicitud), 422, 'Faltan fases obligatorias de verificacion.');

            $aprobadaId = $this->estadoId(self::ESTADO_APROBADA);
            $observaciones = $data['observaciones'] ?? null;

            $solicitud->update([
                'estado_verificacion_id' => $aprobadaId,
                'observaciones' => $observaciones ?? $solicitud->observaciones,
                'motivo_rechazo' => null,
                'revisado_por' => $admin->id,
                'fecha_revision' => now(),
            ]);

            $solicitud->user()->update(['activo' => true]);
            $solicitud->empresa()->update(['activa' => true]);

            $solicitud->user?->verificacion()->update([
                'estado_verificacion_id' => $aprobadaId,
                'fecha_verificacion' => now(),
                'notas_revision' => $observaciones,
                'revisado_por' => $admin->id,
            ]);

            $solicitud->empresa?->verificacion()->update([
                'estado_verificacion_id' => $aprobadaId,
                'fecha_verificacion' => now(),
                'notas_revision' => $observaciones,
                'revisado_por' => $admin->id,
            ]);

            $this->registrarEvento($admin, $solicitud, 'aprobar_solicitud', $estadoAnterior, self::ESTADO_APROBADA, $observaciones);

            return $empresa->fresh();
        });
    }

    public function aprobarFase(Empresa $empresa, User $admin, string $fase): Empresa
    {
        return $this->actualizarFase($empresa, $admin, $fase, self::ESTADO_APROBADA);
    }

    public function rechazarFase(Empresa $empresa, User $admin, string $fase, string $motivo): Empresa
    {
        return $this->actualizarFase($empresa, $admin, $fase, self::ESTADO_RECHAZADA, $motivo);
    }

    /**
     * @param array{motivo: string} $data
     */
    public function rechazar(Empresa $empresa, User $admin, array $data): Empresa
    {
        return DB::transaction(function () use ($empresa, $admin, $data): Empresa {
            $solicitud = $this->bloquearSolicitudActual($empresa);
            $estadoAnterior = $solicitud->estadoVerificacion?->nombre;

            $rechazadaId = $this->estadoId(self::ESTADO_RECHAZADA);
            $motivo = $data['motivo'];

            $solicitud->update([
                'estado_verificacion_id' => $rechazadaId,
                'motivo_rechazo' => $motivo,
                'observaciones' => $motivo,
                'revisado_por' => $admin->id,
                'fecha_revision' => now(),
            ]);

            $solicitud->user()->update(['activo' => false]);
            $solicitud->empresa()->update(['activa' => false]);

            $this->registrarEvento($admin, $solicitud, 'rechazar_solicitud', $estadoAnterior, self::ESTADO_RECHAZADA, $motivo);

            return $empresa->fresh();
        });
    }

    /**
     * @param array{motivo: string, documentos_requeridos?: list<string>} $data
     */
    public function solicitarSubsanacion(Empresa $empresa, User $admin, array $data): Empresa
    {
        return DB::transaction(function () use ($empresa, $admin, $data): Empresa {
            $solicitud = $this->bloquearSolicitudActual($empresa);
            $estadoAnterior = $solicitud->estadoVerificacion?->nombre;

            $motivo = $data['motivo'];

            $solicitud->update([
                'estado_verificacion_id' => $this->estadoId(self::ESTADO_SUBSANACION),
                'observaciones' => $motivo,
                'motivo_rechazo' => null,
                'revisado_por' => $admin->id,
                'fecha_revision' => now(),
            ]);

            $solicitud->user()->update(['activo' => false]);
            $solicitud->empresa()->update(['activa' => false]);

            $this->registrarEvento(
                $admin,
                $solicitud,
                'solicitar_subsanacion',
                $estadoAnterior,
                self::ESTADO_SUBSANACION,
                $motivo,
                ['documentos_requeridos' => $data['documentos_requeridos'] ?? []],
            );

            return $empresa->fresh();
        });
    }

    public function registrarVisualizacionDocumento(User $admin, \App\Models\DocumentoVerificacion $documento): void
    {
        $solicitud = $documento->solicitudVerificacion;

        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $admin->id,
            'user_id' => $solicitud?->user_id,
            'empresa_id' => $solicitud?->empresa_id,
            'solicitud_verificacion_id' => $solicitud?->id,
            'accion' => 'visualizar_documento',
            'metadatos' => [
                'documento_id' => $documento->id,
                'tipo_documento' => $documento->tipo_documento,
            ],
        ]);
    }

    private function bloquearSolicitudActual(Empresa $empresa): SolicitudVerificacion
    {
        return SolicitudVerificacion::query()
            ->with(['estadoVerificacion', 'user.verificacion', 'empresa.tipoEmpresa', 'empresa.verificacion'])
            ->where('empresa_id', $empresa->id)
            ->latest()
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function estadoId(string $nombre): int
    {
        return (int) EstadoVerificacion::query()->where('nombre', $nombre)->firstOrFail()->id;
    }

    private function actualizarFase(Empresa $empresa, User $admin, string $fase, string $estado, ?string $motivo = null): Empresa
    {
        abort_unless(array_key_exists($fase, self::FASES), 404, 'Fase de verificacion no encontrada.');

        return DB::transaction(function () use ($empresa, $admin, $fase, $estado, $motivo): Empresa {
            $solicitud = $this->bloquearSolicitudActual($empresa);
            $campo = self::FASES[$fase];

            if ($fase === 'representacion' && $solicitud->{$campo} === null && ! $this->requiereRepresentacion($solicitud)) {
                abort(422, 'La fase de representacion no aplica a esta solicitud.');
            }

            $estadoAnterior = $solicitud->{$campo};

            $solicitud->update([
                $campo => $estado,
                'estado_verificacion_id' => $this->estadoId(self::ESTADO_EN_REVISION),
                'motivo_rechazo' => $estado === self::ESTADO_RECHAZADA ? $motivo : null,
                'observaciones' => $motivo ?? $solicitud->observaciones,
                'revisado_por' => $admin->id,
                'fecha_revision' => now(),
            ]);

            $this->registrarEvento(
                $admin,
                $solicitud,
                $estado === self::ESTADO_APROBADA ? 'aprobar_fase_'.$fase : 'rechazar_fase_'.$fase,
                $estadoAnterior,
                $estado,
                $motivo,
                ['fase' => $fase],
            );

            return $empresa->fresh();
        });
    }

    private function puedeAprobarSolicitud(SolicitudVerificacion $solicitud): bool
    {
        return $solicitud->estado_identidad === self::ESTADO_APROBADA
            && $solicitud->estado_empresa === self::ESTADO_APROBADA
            && (! $this->requiereRepresentacion($solicitud) || $solicitud->estado_representacion === self::ESTADO_APROBADA);
    }

    private function requiereRepresentacion(SolicitudVerificacion $solicitud): bool
    {
        return VerificacionRegistroRules::requiereRepresentacion($solicitud->empresa?->tipoEmpresa?->nombre);
    }

    /**
     * @param array<string, mixed>|null $metadatos
     */
    private function registrarEvento(
        User $admin,
        SolicitudVerificacion $solicitud,
        string $accion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $motivo = null,
        ?array $metadatos = null,
    ): void {
        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $admin->id,
            'user_id' => $solicitud->user_id,
            'empresa_id' => $solicitud->empresa_id,
            'solicitud_verificacion_id' => $solicitud->id,
            'accion' => $accion,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'metadatos' => $metadatos,
        ]);
    }
}
