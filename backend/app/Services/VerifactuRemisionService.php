<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RegistroFacturacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VerifactuRemisionService
{
    public function __construct(private readonly RegistroEventoFacturacionService $eventoService) {}

    public function enviarPendientes(User $user): array
    {
        $query = RegistroFacturacion::query()->whereNull('enviado_aeat_at');
        if (strtolower((string) $user->role?->nombre) !== 'admin') {
            $query->where('empresa_id', $user->empresa_id);
        }

        $pendientes = $query->orderBy('id')->get();
        $ids = []; $errores = [];

        foreach ($pendientes as $registro) {
            DB::transaction(function () use ($registro, $user, &$ids, &$errores): void {
                try {
                    $registro->enviado_aeat_at = now();
                    $registro->ultimo_intento_at = now();
                    $registro->intentos_remision = (int) $registro->intentos_remision + 1;
                    $registro->estado_remision = 'enviado_simulado';
                    $registro->respuesta_aeat = ['estado' => 'ok_simulado', 'registro_id' => $registro->id, 'timestamp' => now()->toISOString()];
                    $registro->save();
                    $ids[] = $registro->id;

                    $this->eventoService->registrar([
                        'empresa_id' => $registro->empresa_id,
                        'user_id' => $user->id,
                        'factura_id' => $registro->factura_id,
                        'registro_facturacion_id' => $registro->id,
                        'codigo_evento' => 'REGISTRO_FACTURACION_ENVIADO_AEAT_SIMULADO',
                        'descripcion' => 'Envío simulado a AEAT realizado.',
                        'payload_json' => $registro->respuesta_aeat,
                    ]);
                } catch (\Throwable $e) {
                    $registro->ultimo_intento_at = now();
                    $registro->intentos_remision = (int) $registro->intentos_remision + 1;
                    $registro->estado_remision = 'error_simulado';
                    $registro->codigo_error_aeat = 'SIM-500';
                    $registro->descripcion_error_aeat = $e->getMessage();
                    $registro->save();
                    $errores[] = ['id' => $registro->id, 'error' => $e->getMessage()];
                }
            });
        }

        return ['total_pendientes' => $pendientes->count(), 'enviados' => count($ids), 'errores' => $errores, 'ids_enviados' => $ids];
    }
}
