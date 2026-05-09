<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoEstado;
use App\Models\OrdenTrabajoPrioridad;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrdenTrabajoService
{
    public function crearOrden(array $data, User $user): OrdenTrabajo
    {
        return DB::transaction(function () use ($data, $user): OrdenTrabajo {
            $estado = OrdenTrabajoEstado::query()->where('codigo', 'pendiente')->first();
            if (! $estado) {
                throw new RuntimeException('No existe el estado inicial "pendiente". Ejecuta los seeders de estados de orden.');
            }

            $prioridadPorDefecto = OrdenTrabajoPrioridad::query()->where('codigo', 'normal')->first();
            if (! isset($data['prioridad_id']) && ! $prioridadPorDefecto) {
                throw new RuntimeException('No existe la prioridad por defecto "normal". Ejecuta los seeders de prioridades de órdenes.');
            }

            $orden = OrdenTrabajo::query()->create([
                'empresa_id' => $user->empresa_id,
                'cliente_id' => $data['cliente_id'],
                'numero' => 'OT-' . now()->format('YmdHis'),
                'estado_id' => $estado->id,
                'estado_codigo' => $estado->codigo,
                'prioridad_id' => $data['prioridad_id'] ?? $prioridadPorDefecto?->id,
                'fecha_apertura' => now()->toDateString(),
                'fecha_programada_inicio' => $data['fecha_programada_inicio'] ?? null,
                'fecha_programada_fin' => $data['fecha_programada_fin'] ?? null,
                'tecnico_responsable_id' => $data['tecnico_responsable_id'] ?? null,
                'notas_cliente' => $data['notas_cliente'] ?? null,
                'notas_internas' => $data['notas_internas'] ?? null,
            ]);
            foreach ($data['lineas'] as $i => $linea) {
                $calc = $this->calcularLinea($linea);
                $orden->lineas()->create(array_merge($calc, [
                    'servicio_id' => $linea['servicio_id'],
                    'descripcion' => $linea['descripcion'] ?? '',
                    'unidad_snapshot' => 'unidad',
                    'facturable' => (bool) ($linea['facturable'] ?? true),
                    'orden' => $i + 1,
                ]));
            }
            return $orden->fresh(['cliente', 'estado', 'prioridad', 'tecnicoResponsable', 'lineas.servicio']);
        });
    }

    public function actualizarOrden(OrdenTrabajo $orden, array $data, User $user): OrdenTrabajo
    {
        if ($orden->estado?->codigo === 'facturada') {
            return $orden;
        }
        return DB::transaction(function () use ($orden, $data): OrdenTrabajo {
            $orden->fill(collect($data)->except('lineas')->toArray());
            $orden->save();
            if (isset($data['lineas']) && ! in_array($orden->estado?->codigo, ['completada', 'facturada'], true)) {
                $orden->lineas()->delete();
                foreach ($data['lineas'] as $i => $linea) {
                    $calc = $this->calcularLinea($linea);
                    $orden->lineas()->create(array_merge($calc, ['servicio_id' => $linea['servicio_id'], 'descripcion' => $linea['descripcion'] ?? '', 'unidad_snapshot' => 'unidad', 'facturable' => (bool)($linea['facturable'] ?? true), 'orden' => $i + 1]));
                }
            }
            return $orden->fresh(['cliente', 'estado', 'prioridad', 'tecnicoResponsable', 'lineas.servicio']);
        });
    }

    public function completarOrden(OrdenTrabajo $orden, User $user): OrdenTrabajo
    {
        $estado = OrdenTrabajoEstado::query()->where('codigo', 'completada')->first();
        if ($estado) {
            $orden->estado_id = $estado->id;
            $orden->estado_codigo = $estado->codigo;
        }
        $orden->fecha_fin_real = now();
        $orden->fecha_cierre = now();
        $orden->save();
        return $orden->fresh(['cliente', 'estado', 'prioridad', 'tecnicoResponsable', 'lineas.servicio']);
    }

    public function cancelarOrden(OrdenTrabajo $orden, User $user): OrdenTrabajo
    {
        $estado = OrdenTrabajoEstado::query()->where('codigo', 'cancelada')->first();
        if ($estado) {
            $orden->estado_id = $estado->id;
            $orden->estado_codigo = $estado->codigo;
        }
        $orden->fecha_cierre = now();
        $orden->save();
        return $orden->fresh(['cliente', 'estado', 'prioridad', 'tecnicoResponsable', 'lineas.servicio']);
    }

    public function calcularLinea(array $linea): array
    {
        $cantidad = (float)$linea['cantidad'];
        $precio = (float)$linea['precio_unitario'];
        $desc = (float)($linea['descuento_porcentaje'] ?? 0);
        $iva = (float)$linea['iva_porcentaje'];
        $base = round($cantidad * $precio, 2);
        $descuento = round($base * $desc / 100, 2);
        $bi = round($base - $descuento, 2);
        $cuota = round($bi * $iva / 100, 2);
        return ['cantidad' => $cantidad, 'precio_unitario' => $precio, 'descuento_porcentaje' => $desc, 'base_imponible' => $bi, 'iva_porcentaje' => $iva, 'cuota_iva' => $cuota, 'total' => round($bi + $cuota, 2)];
    }

    public function recalcularTotales(OrdenTrabajo $orden): void
    {
        foreach ($orden->lineas as $linea) {
            $calc = $this->calcularLinea($linea->toArray());
            $linea->fill($calc)->save();
        }
    }
}
