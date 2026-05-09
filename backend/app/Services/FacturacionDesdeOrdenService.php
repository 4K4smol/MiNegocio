<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoEstado;
use App\Models\TipoFactura;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FacturacionDesdeOrdenService
{
    public function generarDesdeOrden(OrdenTrabajo $orden, User $user): Factura
    {
        if ($user->role?->nombre !== 'admin' && (int) $orden->empresa_id !== (int) $user->empresa_id) {
            throw new RuntimeException('No puedes facturar órdenes de otra empresa.');
        }
        if (strtolower((string) $orden->estado?->codigo) !== 'completada') {
            throw new RuntimeException('Solo se pueden facturar órdenes completadas.');
        }

        return DB::transaction(function () use ($orden): Factura {
            $orden->loadMissing(['empresa', 'cliente', 'lineas.servicio', 'estado']);
            $facturables = $orden->lineas->filter(fn($l) => $l->facturable && (float) $l->cantidad > (float) $l->facturado_cantidad)->values();
            if ($facturables->isEmpty()) {
                throw new RuntimeException('La orden no tiene líneas facturables pendientes.');
            }

            $tipo = TipoFactura::query()->first();
            $estadoFactura = EstadoFactura::query()->first();
            if (!$tipo || !$estadoFactura) {
                throw new RuntimeException('Faltan seeders de tipos/estados de factura.');
            }

            $factura = Factura::query()->create([
                'empresa_id' => $orden->empresa_id,
                'cliente_id' => $orden->cliente_id,
                'tipo_factura_id' => $tipo->id,
                'estado_factura_id' => $estadoFactura->id,
                'serie' => 'A',
                'numero' => 'F-' . date('Y') . '-' . str_pad((string) (Factura::query()->where('empresa_id', $orden->empresa_id)->count() + 1), 6, '0', STR_PAD_LEFT),
                'fecha_emision' => now()->toDateString(),
                'fecha_operacion' => now()->toDateString(),
                'emisor_nif' => $orden->empresa->nif,
                'emisor_nombre_razon_social' => $orden->empresa->nombre_fiscal,
                'emisor_domicilio_fiscal' => $orden->empresa->direccion_fiscal,
                'receptor_nif' => $orden->cliente->dni_cif,
                'receptor_nombre_razon_social' => $orden->cliente->razon_social ?: $orden->cliente->nombre_completo,
                'receptor_domicilio_fiscal' => $orden->cliente->localizacionPrincipal?->direccion ?? 'N/D',
                'receptor_cp' => $orden->cliente->localizacionPrincipal?->codigo_postal,
                'receptor_municipio' => $orden->cliente->localizacionPrincipal?->municipio,
                'receptor_provincia' => $orden->cliente->localizacionPrincipal?->provincia,
                'receptor_pais' => $orden->cliente->localizacionPrincipal?->pais,
            ]);

            foreach ($facturables as $idx => $linea) {
                $factura->lineas()->create([
                    'orden_trabajo_linea_id' => $linea->id,
                    'servicio_id' => $linea->servicio_id,
                    'servicio_nombre_snapshot' => $linea->servicio?->nombre,
                    'descripcion' => $linea->descripcion,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'base_imponible' => $linea->base_imponible,
                    'iva_porcentaje' => $linea->iva_porcentaje,
                    'descuento_porcentaje' => $linea->descuento_porcentaje,
                    'subtotal' => $linea->base_imponible,
                    'total_iva' => $linea->cuota_iva,
                    'total' => $linea->total,
                    'orden' => $idx + 1,
                ]);
                $linea->facturado_cantidad = $linea->cantidad;
                $linea->save();
            }

            $grupos = $facturables->groupBy(fn($l) => (string) $l->iva_porcentaje);
            $subtotal = 0.0;
            $cuotaIva = 0.0;
            foreach ($grupos as $iva => $items) {
                $base = round((float) $items->sum('base_imponible'), 2);
                $cuota = round((float) $items->sum('cuota_iva'), 2);
                $factura->impuestos()->create([
                    'impuesto_codigo' => 'IVA',
                    'impuesto_nombre' => 'Impuesto sobre el Valor Añadido',
                    'tipo_porcentaje' => (float) $iva,
                    'base_imponible' => $base,
                    'cuota' => $cuota,
                ]);
                $subtotal += $base;
                $cuotaIva += $cuota;
            }

            $factura->subtotal = round($subtotal, 2);
            $factura->cuota_iva = round($cuotaIva, 2);
            $factura->total = round($subtotal + $cuotaIva, 2);
            $factura->save();

            $estadoFacturada = OrdenTrabajoEstado::query()->whereRaw('LOWER(codigo)=?', ['facturada'])->first();
            if ($estadoFacturada) {
                $orden->estado_id = $estadoFacturada->id;
                $orden->estado_codigo = $estadoFacturada->codigo;
                $orden->save();
            }

            return $factura->fresh(['lineas', 'impuestos']);
        });
    }
}
