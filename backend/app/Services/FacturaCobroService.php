<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Models\FacturaCobro;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FacturaCobroService
{
    public function __construct(private readonly FacturaHistorialService $historialService) {}

    public function registrarCobro(Factura $factura, array $data, User $user): Factura
    {
        return DB::transaction(function () use ($factura, $data, $user): Factura {
            $factura->loadMissing(['estadoFactura', 'cobros']);

            if ((int) $factura->empresa_id !== (int) $user->empresa_id && strtolower((string) $user->role?->nombre) !== 'admin') {
                throw new RuntimeException('No puedes registrar cobros de facturas de otra empresa.');
            }

            if (in_array($factura->estadoFactura?->codigo, ['borrador', 'anulada'], true)) {
                throw new RuntimeException('Solo se pueden registrar cobros sobre facturas emitidas o enviadas.');
            }

            $importe = round((float) $data['importe'], 2);
            if ($importe <= 0) {
                throw new RuntimeException('El importe del cobro debe ser mayor que cero.');
            }

            FacturaCobro::query()->create([
                'factura_id' => $factura->id,
                'empresa_id' => $factura->empresa_id,
                'fecha_cobro' => $data['fecha_cobro'] ?? now()->toDateString(),
                'importe' => $importe,
                'metodo_pago' => $data['metodo_pago'] ?? 'transferencia',
                'referencia' => $data['referencia'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'created_by' => $user->id,
            ]);

            $totalCobrado = (float) $factura->cobros()->sum('importe');
            $estadoAnteriorId = $factura->estado_factura_id;
            $nuevoEstado = $this->estadoPorCobro($totalCobrado, (float) $factura->total);

            $factura->estado_factura_id = $nuevoEstado->id;
            $factura->pagada = $nuevoEstado->codigo === 'pagada';
            $factura->fecha_pago = $factura->pagada ? ($data['fecha_cobro'] ?? now()->toDateString()) : null;
            $factura->observaciones_pago = $data['observaciones'] ?? $factura->observaciones_pago;
            $factura->save();

            $this->historialService->registrar(
                $factura,
                'cobro_registrado',
                $user,
                $estadoAnteriorId,
                $factura->estado_factura_id,
                'Cobro registrado.',
                ['importe' => $importe, 'total_cobrado' => $totalCobrado]
            );

            return $factura->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion', 'cobros', 'historial']);
        });
    }

    private function estadoPorCobro(float $totalCobrado, float $totalFactura): EstadoFactura
    {
        $codigo = $totalCobrado + 0.0001 >= $totalFactura ? 'pagada' : 'pagada_parcial';
        $estado = EstadoFactura::query()->where('codigo', $codigo)->first();

        if (! $estado) {
            throw new RuntimeException('No existe el estado de factura "' . $codigo . '". Ejecuta los seeders.');
        }

        return $estado;
    }
}
