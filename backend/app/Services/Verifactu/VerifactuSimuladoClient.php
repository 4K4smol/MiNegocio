<?php

declare(strict_types=1);

namespace App\Services\Verifactu;

use App\Models\RegistroFacturacion;
use App\Models\EstadoRemisionFacturacion;
use App\Models\User;
use App\Services\RegistroEventoFacturacionService;
use Illuminate\Support\Facades\DB;
use Throwable;

class VerifactuSimuladoClient implements VerifactuClientInterface
{
    public function __construct(private readonly RegistroEventoFacturacionService $eventoService) {}

    public function enviarPendientes(User $user): array
    {
        // Remision simulada: este cliente no conecta con AEAT ni envia datos reales.
        $query = RegistroFacturacion::query()
            ->where(function ($query): void {
                $query->where('estado_remision', 'pendiente')
                    ->orWhereNull('estado_remision');
            })
            ->whereNull('enviado_aeat_at');

        if (strtolower((string) $user->role?->nombre) !== 'admin') {
            $query->where('empresa_id', $user->empresa_id);
        }

        $pendientes = $query->orderBy('id')->pluck('id');
        $ids = [];
        $errores = [];

        foreach ($pendientes as $registroId) {
            try {
                DB::transaction(function () use ($registroId, $user, &$ids): void {
                    $registro = RegistroFacturacion::query()
                        ->whereKey($registroId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($registro->enviado_aeat_at !== null || !in_array($registro->estado_remision, [null, 'pendiente'], true)) {
                        return;
                    }

                    $respuestaSimulada = [
                        'estado' => 'ok_simulado',
                        'registro_id' => $registro->id,
                        'timestamp' => now()->toISOString(),
                        'modo' => 'simulado',
                    ];

                    $registro->enviado_aeat_at = now();
                    $registro->remitido_at = $registro->enviado_aeat_at;
                    $registro->ultimo_intento_at = now();
                    $registro->intentos_remision = (int) $registro->intentos_remision + 1;
                    $registro->estado_remision = 'enviado_simulado';
                    $registro->estado_remision_facturacion_id = EstadoRemisionFacturacion::query()
                        ->where('codigo', 'enviado')
                        ->value('id');
                    $registro->codigo_error_aeat = null;
                    $registro->descripcion_error_aeat = null;
                    $registro->respuesta_aeat = $respuestaSimulada;
                    $registro->save();

                    $this->eventoService->registrar([
                        'empresa_id' => $registro->empresa_id,
                        'user_id' => $user->id,
                        'factura_id' => $registro->factura_id,
                        'registro_facturacion_id' => $registro->id,
                        'codigo_evento' => 'REGISTRO_FACTURACION_ENVIADO_AEAT_SIMULADO',
                        'descripcion' => 'Envio simulado a AEAT realizado.',
                        'payload_json' => $respuestaSimulada,
                    ]);

                    $ids[] = $registro->id;
                });
            } catch (Throwable $e) {
                $errores[] = $this->registrarErrorSimulado((int) $registroId, $user, $e);
            }
        }

        return [
            'total_pendientes' => $pendientes->count(),
            'enviados' => count($ids),
            'errores' => $errores,
            'ids_enviados' => $ids,
            'ejecutado_at' => now()->toISOString(),
            'modo' => 'simulado',
        ];
    }

    private function registrarErrorSimulado(int $registroId, User $user, Throwable $e): array
    {
        $error = ['id' => $registroId, 'error' => $e->getMessage()];

        try {
            DB::transaction(function () use ($registroId, $user, $e): void {
                $registro = RegistroFacturacion::query()
                    ->whereKey($registroId)
                    ->lockForUpdate()
                    ->first();

                if (!$registro) {
                    return;
                }

                $respuestaSimulada = [
                    'estado' => 'error_simulado',
                    'registro_id' => $registro->id,
                    'timestamp' => now()->toISOString(),
                    'modo' => 'simulado',
                    'error' => $e->getMessage(),
                ];

                $registro->ultimo_intento_at = now();
                $registro->intentos_remision = (int) $registro->intentos_remision + 1;
                $registro->estado_remision = 'error_simulado';
                $registro->estado_remision_facturacion_id = EstadoRemisionFacturacion::query()
                    ->where('codigo', 'error')
                    ->value('id');
                $registro->codigo_error_aeat = 'SIM-500';
                $registro->descripcion_error_aeat = $e->getMessage();
                $registro->respuesta_aeat = $respuestaSimulada;
                $registro->save();

                $this->eventoService->registrar([
                    'empresa_id' => $registro->empresa_id,
                    'user_id' => $user->id,
                    'factura_id' => $registro->factura_id,
                    'registro_facturacion_id' => $registro->id,
                    'codigo_evento' => 'REGISTRO_FACTURACION_ERROR_AEAT_SIMULADO',
                    'descripcion' => 'Error en envio simulado a AEAT.',
                    'payload_json' => $respuestaSimulada,
                ]);
            });
        } catch (Throwable) {
            // El lote no debe detenerse por un fallo al registrar el error de un registro.
        }

        return $error;
    }
}
