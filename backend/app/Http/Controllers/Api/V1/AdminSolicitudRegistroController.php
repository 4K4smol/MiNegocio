<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\RechazarSolicitudRegistroRequest;
use App\Http\Resources\Api\V1\SolicitudRegistroResource;
use App\Models\DocumentoVerificacion;
use App\Models\EstadoVerificacion;
use App\Models\SolicitudVerificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminSolicitudRegistroController extends ApiController
{
    private const ESTADO_PENDIENTE = 'pendiente';
    private const ESTADO_APROBADA = 'aprobada';
    private const ESTADO_RECHAZADA = 'rechazada';

    public function index(): JsonResponse
    {
        $estadoPendiente = EstadoVerificacion::query()
            ->where('nombre', self::ESTADO_PENDIENTE)
            ->firstOrFail();

        $solicitudes = SolicitudVerificacion::query()
            ->with([
                'user',
                'empresa',
                'estadoVerificacion',
            ])
            ->where('estado_verificacion_id', $estadoPendiente->id)
            ->latest()
            ->paginate(20);

        return $this->success(
            SolicitudRegistroResource::collection($solicitudes)
                ->response()
                ->getData(true)
        );
    }

    public function show(SolicitudVerificacion $solicitud): JsonResponse
    {
        $solicitud->load([
            'user',
            'empresa',
            'estadoVerificacion',
            'documentos',
        ]);

        return $this->success(
            new SolicitudRegistroResource($solicitud)
        );
    }

    public function aprobar(
        Request $request,
        SolicitudVerificacion $solicitud
    ): JsonResponse {
        return $this->cambiarEstado(
            request: $request,
            solicitud: $solicitud,
            estadoNombre: self::ESTADO_APROBADA
        );
    }

    public function rechazar(
        RechazarSolicitudRegistroRequest $request,
        SolicitudVerificacion $solicitud
    ): JsonResponse {
        return $this->cambiarEstado(
            request: $request,
            solicitud: $solicitud,
            estadoNombre: self::ESTADO_RECHAZADA,
            nota: $request->validated()['motivo_rechazo'] ?? null
        );
    }

    public function verDocumento(DocumentoVerificacion $documento): BinaryFileResponse
    {
        $disk = Storage::disk('verificaciones');

        abort_unless(
            $disk->exists($documento->archivo),
            404
        );

        return response()->download(
            $disk->path($documento->archivo),
            $documento->nombre_original
        );
    }

    private function cambiarEstado(
        Request $request,
        SolicitudVerificacion $solicitud,
        string $estadoNombre,
        ?string $nota = null
    ): JsonResponse {
        DB::transaction(function () use ($request, $solicitud, $estadoNombre, $nota): void {
            $solicitudBloqueada = SolicitudVerificacion::query()
                ->whereKey($solicitud->id)
                ->lockForUpdate()
                ->firstOrFail();

            $estadoPendiente = EstadoVerificacion::query()
                ->where('nombre', self::ESTADO_PENDIENTE)
                ->firstOrFail();

            if ((int) $solicitudBloqueada->estado_verificacion_id !== (int) $estadoPendiente->id) {
                abort(409, 'La solicitud ya ha sido revisada.');
            }

            $nuevoEstado = EstadoVerificacion::query()
                ->where('nombre', $estadoNombre)
                ->firstOrFail();

            $solicitudBloqueada->update([
                'estado_verificacion_id' => $nuevoEstado->id,
                'observaciones' => $nota,
                'revisado_por' => $request->user()->id,
                'fecha_revision' => now(),
            ]);

            $activo = $estadoNombre === self::ESTADO_APROBADA;

            $solicitudBloqueada->user()->update([
                'activo' => $activo,
            ]);

            $solicitudBloqueada->empresa()->update([
                'activa' => $activo,
            ]);
        });

        return $this->success(
            new SolicitudRegistroResource(
                $solicitud->fresh([
                    'user',
                    'empresa',
                    'estadoVerificacion',
                    'documentos',
                ])
            ),
            'Solicitud actualizada.'
        );
    }
}
