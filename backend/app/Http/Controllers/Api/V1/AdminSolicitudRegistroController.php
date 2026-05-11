<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\RechazarSolicitudRegistroRequest;
use App\Http\Resources\Api\V1\EmpresaResource;
use App\Http\Resources\Api\V1\SolicitudRegistroResource;
use App\Models\AdminVerificacionEvento;
use App\Models\DocumentoVerificacion;
use App\Models\Empresa;
use App\Models\EstadoVerificacion;
use App\Models\Modulo;
use App\Models\Role;
use App\Models\SolicitudVerificacion;
use App\Models\TipoDocumentoIdentidad;
use App\Models\TipoEmpresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminSolicitudRegistroController extends ApiController
{
    private const ESTADO_PENDIENTE = 'pendiente';
    private const ESTADO_APROBADA = 'aprobada';
    private const ESTADO_RECHAZADA = 'rechazada';
    private const ESTADO_EN_REVISION = 'en_revision';

    public function dashboard(): JsonResponse
    {
        return $this->success([
            'total_empresas' => Empresa::query()->count(),
            'empresas_pendientes' => $this->solicitudesPorEstado(self::ESTADO_PENDIENTE),
            'empresas_aprobadas' => $this->solicitudesPorEstado(self::ESTADO_APROBADA),
            'empresas_rechazadas' => $this->solicitudesPorEstado(self::ESTADO_RECHAZADA),
            'usuarios_pendientes' => SolicitudVerificacion::query()
                ->whereHas('estadoVerificacion', fn (Builder $query) => $query->where('nombre', self::ESTADO_PENDIENTE))
                ->count(),
            'documentos_pendientes' => SolicitudVerificacion::query()
                ->where(function (Builder $query): void {
                    $query->where('estado_identidad', self::ESTADO_PENDIENTE)
                        ->orWhere('estado_empresa', self::ESTADO_PENDIENTE)
                        ->orWhere('estado_representacion', self::ESTADO_PENDIENTE);
                })
                ->count(),
            'solicitudes_recientes' => SolicitudRegistroResource::collection(
                $this->solicitudQuery()->latest()->limit(6)->get()
            ),
            'alertas' => AdminVerificacionEvento::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'accion', 'motivo', 'created_at']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $solicitudes = $this->solicitudQuery()
            ->when($request->filled('estado_verificacion'), fn (Builder $query) =>
                $query->whereHas('estadoVerificacion', fn (Builder $estadoQuery) =>
                    $estadoQuery->where('nombre', $request->string('estado_verificacion')->toString())
                )
            )
            ->when($request->filled('tipo_empresa'), fn (Builder $query) =>
                $query->whereHas('empresa.tipoEmpresa', fn (Builder $tipoQuery) =>
                    $tipoQuery->where('nombre', $request->string('tipo_empresa')->toString())
                )
            )
            ->when($request->filled('fecha_desde'), fn (Builder $query) =>
                $query->whereDate('created_at', '>=', $request->date('fecha_desde'))
            )
            ->when($request->filled('fecha_hasta'), fn (Builder $query) =>
                $query->whereDate('created_at', '<=', $request->date('fecha_hasta'))
            )
            ->when($request->filled('texto'), function (Builder $query) use ($request): void {
                $texto = '%'.$request->string('texto')->toString().'%';
                $query->where(function (Builder $search) use ($texto): void {
                    $search->whereHas('user', fn (Builder $userQuery) =>
                        $userQuery->where('nombre', 'like', $texto)
                            ->orWhere('apellido1', 'like', $texto)
                            ->orWhere('email', 'like', $texto)
                    )->orWhereHas('empresa', fn (Builder $empresaQuery) =>
                        $empresaQuery->where('nombre_fiscal', 'like', $texto)
                            ->orWhere('nombre_comercial', 'like', $texto)
                            ->orWhere('nif', 'like', $texto)
                    );
                });
            })
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return $this->success(
            SolicitudRegistroResource::collection($solicitudes)->response()->getData(true)
        );
    }

    public function show(SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->success(new SolicitudRegistroResource($this->cargarSolicitud($solicitud)));
    }

    public function aprobarIdentidad(Request $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->actualizarFase($request, $solicitud, 'estado_identidad', self::ESTADO_APROBADA, 'aprobar_identidad');
    }

    public function rechazarIdentidad(RechazarSolicitudRegistroRequest $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->actualizarFase($request, $solicitud, 'estado_identidad', self::ESTADO_RECHAZADA, 'rechazar_identidad', $request->validated()['motivo_rechazo']);
    }

    public function aprobarEmpresa(Request $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->actualizarFase($request, $solicitud, 'estado_empresa', self::ESTADO_APROBADA, 'aprobar_empresa');
    }

    public function rechazarEmpresa(RechazarSolicitudRegistroRequest $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->actualizarFase($request, $solicitud, 'estado_empresa', self::ESTADO_RECHAZADA, 'rechazar_empresa', $request->validated()['motivo_rechazo']);
    }

    public function aprobarRepresentacion(Request $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->actualizarFase($request, $solicitud, 'estado_representacion', self::ESTADO_APROBADA, 'aprobar_representacion');
    }

    public function rechazarRepresentacion(RechazarSolicitudRegistroRequest $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->actualizarFase($request, $solicitud, 'estado_representacion', self::ESTADO_RECHAZADA, 'rechazar_representacion', $request->validated()['motivo_rechazo']);
    }

    public function aprobarTotal(Request $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        $solicitud = $solicitud->load('empresa.tipoEmpresa');

        abort_unless($this->puedeAprobarTotal($solicitud), 422, 'Faltan fases obligatorias de verificacion.');

        return $this->cambiarEstadoGlobal($request, $solicitud, self::ESTADO_APROBADA, 'aprobar_total');
    }

    public function rechazarTotal(RechazarSolicitudRegistroRequest $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->cambiarEstadoGlobal($request, $solicitud, self::ESTADO_RECHAZADA, 'rechazar_total', $request->validated()['motivo_rechazo']);
    }

    public function empresas(Request $request): JsonResponse
    {
        $empresas = Empresa::query()
            ->with(['tipoEmpresa', 'usuarios', 'solicitudesVerificacion.estadoVerificacion', 'modulos'])
            ->when($request->filled('estado'), function (Builder $query) use ($request): void {
                $estado = $request->string('estado')->toString();
                if ($estado === 'activa') {
                    $query->where('activa', true);
                } elseif ($estado === 'inactiva') {
                    $query->where('activa', false);
                } else {
                    $query->whereHas('solicitudesVerificacion.estadoVerificacion', fn (Builder $estadoQuery) =>
                        $estadoQuery->where('nombre', $estado)
                    );
                }
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

    public function empresa(Empresa $empresa): JsonResponse
    {
        return $this->success(new EmpresaResource($empresa->load([
            'tipoEmpresa',
            'usuarios.role',
            'solicitudesVerificacion.estadoVerificacion',
            'modulos',
        ])));
    }

    public function activarEmpresa(Request $request, Empresa $empresa): JsonResponse
    {
        return $this->actualizarEmpresaActiva($request, $empresa, true);
    }

    public function desactivarEmpresa(Request $request, Empresa $empresa): JsonResponse
    {
        return $this->actualizarEmpresaActiva($request, $empresa, false);
    }

    public function catalogos(): JsonResponse
    {
        return $this->success([
            'estados_verificacion' => EstadoVerificacion::query()->orderBy('id')->get(),
            'tipos_empresa' => TipoEmpresa::query()->orderBy('id')->get(),
            'tipos_documento_identidad' => TipoDocumentoIdentidad::query()->orderBy('id')->get(),
            'modulos' => Modulo::query()->orderBy('orden_visual')->orderBy('nombre')->get(),
            'roles' => Role::query()->orderBy('id')->get(),
            'readonly' => true,
        ]);
    }

    public function verDocumento(DocumentoVerificacion $documento)
    {
        $disk = Storage::disk('verificaciones');

        abort_unless($disk->exists($documento->archivo), 404);

        return response()->download(
            $disk->path($documento->archivo),
            $documento->nombre_original ?? basename($documento->archivo)
        );
    }

    public function aprobar(Request $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->aprobarTotal($request, $solicitud);
    }

    public function rechazar(RechazarSolicitudRegistroRequest $request, SolicitudVerificacion $solicitud): JsonResponse
    {
        return $this->rechazarTotal($request, $solicitud);
    }

    private function solicitudQuery(): Builder
    {
        return SolicitudVerificacion::query()
            ->with(['user', 'empresa.tipoEmpresa', 'estadoVerificacion', 'documentos', 'eventosAdmin.admin']);
    }

    private function cargarSolicitud(SolicitudVerificacion $solicitud): SolicitudVerificacion
    {
        return $solicitud->load(['user', 'empresa.tipoEmpresa', 'estadoVerificacion', 'documentos', 'eventosAdmin.admin']);
    }

    private function solicitudesPorEstado(string $estado): int
    {
        return SolicitudVerificacion::query()
            ->whereHas('estadoVerificacion', fn (Builder $query) => $query->where('nombre', $estado))
            ->count();
    }

    private function actualizarFase(Request $request, SolicitudVerificacion $solicitud, string $campo, string $estado, string $accion, ?string $motivo = null): JsonResponse
    {
        DB::transaction(function () use ($request, $solicitud, $campo, $estado, $accion, $motivo): void {
            $bloqueada = SolicitudVerificacion::query()->whereKey($solicitud->id)->lockForUpdate()->firstOrFail();
            $anterior = $bloqueada->{$campo};

            $bloqueada->update([
                $campo => $estado,
                'motivo_rechazo' => $motivo,
                'observaciones' => $motivo ?? $bloqueada->observaciones,
                'estado_verificacion_id' => $this->estadoId(self::ESTADO_EN_REVISION),
                'revisado_por' => $request->user()->id,
                'fecha_revision' => now(),
            ]);

            $this->registrarEvento($request, $bloqueada, $accion, $anterior, $estado, $motivo);
        });

        return $this->success(new SolicitudRegistroResource($this->cargarSolicitud($solicitud->fresh())), 'Solicitud actualizada.');
    }

    private function cambiarEstadoGlobal(Request $request, SolicitudVerificacion $solicitud, string $estado, string $accion, ?string $motivo = null): JsonResponse
    {
        DB::transaction(function () use ($request, $solicitud, $estado, $accion, $motivo): void {
            $bloqueada = SolicitudVerificacion::query()->whereKey($solicitud->id)->lockForUpdate()->firstOrFail();
            $anterior = $bloqueada->estadoVerificacion?->nombre;

            $bloqueada->update([
                'estado_verificacion_id' => $this->estadoId($estado),
                'motivo_rechazo' => $motivo,
                'observaciones' => $motivo ?? $bloqueada->observaciones,
                'revisado_por' => $request->user()->id,
                'fecha_revision' => now(),
            ]);

            $activo = $estado === self::ESTADO_APROBADA;
            $bloqueada->user()->update(['activo' => $activo]);
            $bloqueada->empresa()->update(['activa' => $activo]);

            $this->registrarEvento($request, $bloqueada, $accion, $anterior, $estado, $motivo);
        });

        return $this->success(new SolicitudRegistroResource($this->cargarSolicitud($solicitud->fresh())), 'Solicitud actualizada.');
    }

    private function actualizarEmpresaActiva(Request $request, Empresa $empresa, bool $activa): JsonResponse
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

        return $this->success(new EmpresaResource($empresa->fresh(['tipoEmpresa', 'usuarios', 'modulos'])), 'Empresa actualizada.');
    }

    private function puedeAprobarTotal(SolicitudVerificacion $solicitud): bool
    {
        $requiereRepresentacion = $solicitud->empresa?->tipoEmpresa?->nombre !== 'autonomo';

        return $solicitud->estado_identidad === self::ESTADO_APROBADA
            && $solicitud->estado_empresa === self::ESTADO_APROBADA
            && (! $requiereRepresentacion || $solicitud->estado_representacion === self::ESTADO_APROBADA);
    }

    private function estadoId(string $nombre): int
    {
        return (int) EstadoVerificacion::query()->where('nombre', $nombre)->firstOrFail()->id;
    }

    private function registrarEvento(Request $request, SolicitudVerificacion $solicitud, string $accion, ?string $anterior, ?string $nuevo, ?string $motivo = null): void
    {
        AdminVerificacionEvento::query()->create([
            'user_admin_id' => $request->user()->id,
            'user_id' => $solicitud->user_id,
            'empresa_id' => $solicitud->empresa_id,
            'solicitud_verificacion_id' => $solicitud->id,
            'accion' => $accion,
            'estado_anterior' => $anterior,
            'estado_nuevo' => $nuevo,
            'motivo' => $motivo,
        ]);
    }
}
