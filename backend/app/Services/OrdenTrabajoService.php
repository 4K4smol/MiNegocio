<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoEstado;
use App\Models\OrdenTrabajoPrioridad;
use App\Models\Servicio;
use App\Models\ServicioPrecio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrdenTrabajoService
{
    public function __construct(private readonly CalendarioEventoService $calendarioEventoService) {}

    public function crear(array $data, User $user): OrdenTrabajo
    {
        return $this->crearOrden($data, $user);
    }

    public function crearOrden(array $data, User $user): OrdenTrabajo
    {
        return DB::transaction(function () use ($data, $user): OrdenTrabajo {
            $empresaId = (int) $user->empresa_id;
            $estado = $this->resolverEstadoInicial($data);
            $prioridad = $this->resolverPrioridad($data['prioridad_id'] ?? null, $data['prioridad_codigo'] ?? null);

            $orden = OrdenTrabajo::query()->create([
                'empresa_id' => $empresaId,
                'cliente_id' => $data['cliente_id'],
                'localizacion_cliente_id' => $data['localizacion_cliente_id'] ?? null,
                'numero' => null,
                'estado_id' => $estado->id,
                'estado_codigo' => $estado->codigo,
                'prioridad_id' => $prioridad?->id,
                'prioridad_codigo' => $prioridad?->codigo,
                'fecha_apertura' => now()->toDateString(),
                'fecha_programada_inicio' => $this->fechaProgramadaInicio($data),
                'fecha_programada_fin' => $data['fecha_programada_fin'] ?? null,
                'tecnico_responsable_id' => $data['tecnico_responsable_id'] ?? null,
                'notas_cliente' => $data['notas_cliente'] ?? null,
                'notas_internas' => $data['notas_internas'] ?? ($data['observaciones'] ?? null),
            ]);

            $orden->update([
                'numero' => $this->generarNumeroDesdeId($orden),
            ]);

            $totales = $this->crearLineas($orden, $data['lineas'], $empresaId);
            $orden->update($totales);

            $this->calendarioEventoService->crearOActualizarDesdeOrden($orden, $user);

            return $this->cargarRelaciones($orden);
        });
    }

    public function actualizarOrden(OrdenTrabajo $orden, array $data, User $user): OrdenTrabajo
    {
        if ($orden->estado?->codigo === 'facturada') {
            return $this->cargarRelaciones($orden);
        }

        return DB::transaction(function () use ($orden, $data, $user): OrdenTrabajo {
            $estado = $this->resolverEstadoInicial($data);
            $prioridad = $this->resolverPrioridad($data['prioridad_id'] ?? null, $data['prioridad_codigo'] ?? null);

            $orden->fill([
                'cliente_id' => $data['cliente_id'] ?? $orden->cliente_id,
                'localizacion_cliente_id' => array_key_exists('localizacion_cliente_id', $data)
                    ? $data['localizacion_cliente_id']
                    : $orden->localizacion_cliente_id,
                'estado_id' => $estado->id,
                'estado_codigo' => $estado->codigo,
                'prioridad_id' => $prioridad?->id ?? $orden->prioridad_id,
                'prioridad_codigo' => $prioridad?->codigo ?? $orden->prioridad_codigo,
                'fecha_programada_inicio' => $this->fechaProgramadaInicio($data) ?? $orden->fecha_programada_inicio,
                'fecha_programada_fin' => array_key_exists('fecha_programada_fin', $data)
                    ? $data['fecha_programada_fin']
                    : $orden->fecha_programada_fin,
                'tecnico_responsable_id' => $data['tecnico_responsable_id'] ?? $orden->tecnico_responsable_id,
                'notas_cliente' => $data['notas_cliente'] ?? $orden->notas_cliente,
                'notas_internas' => $data['notas_internas'] ?? ($data['observaciones'] ?? $orden->notas_internas),
            ]);
            $orden->save();

            if (isset($data['lineas']) && !in_array($orden->estado?->codigo, ['completada', 'facturada'], true)) {
                $orden->lineas()->delete();
                $totales = $this->crearLineas($orden, $data['lineas'], (int) $orden->empresa_id);
                $orden->update($totales);
            }

            $this->calendarioEventoService->crearOActualizarDesdeOrden($orden, $user);

            return $this->cargarRelaciones($orden);
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
        $this->calendarioEventoService->marcarDesdeOrden($orden, 'completada');

        return $this->cargarRelaciones($orden);
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
        $this->calendarioEventoService->marcarDesdeOrden($orden, 'cancelada');

        return $this->cargarRelaciones($orden);
    }

    public function calcularLinea(array $linea): array
    {
        $cantidad = (float) $linea['cantidad'];
        $precio = (float) $linea['precio_unitario'];
        $descuentoPorcentaje = (float) ($linea['descuento_porcentaje'] ?? 0);
        $ivaPorcentaje = (float) $linea['iva_porcentaje'];
        $subtotalBruto = round($cantidad * $precio, 2);
        $descuentoImporte = round($subtotalBruto * $descuentoPorcentaje / 100, 2);
        $subtotal = round($subtotalBruto - $descuentoImporte, 2);
        $ivaImporte = round($subtotal * $ivaPorcentaje / 100, 2);

        return [
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'descuento_porcentaje' => $descuentoPorcentaje,
            'base_imponible' => $subtotal,
            'iva_porcentaje' => $ivaPorcentaje,
            'cuota_iva' => $ivaImporte,
            'total' => round($subtotal + $ivaImporte, 2),
        ];
    }

    public function recalcularTotales(OrdenTrabajo $orden): void
    {
        $totales = $this->calcularTotalesDesdeLineas($orden);
        $orden->update($totales);
    }

    private function crearLineas(OrdenTrabajo $orden, array $lineas, int $empresaId): array
    {
        foreach ($lineas as $index => $linea) {
            $servicio = Servicio::query()
                ->whereKey((int) $linea['servicio_id'])
                ->where('empresa_id', $empresaId)
                ->firstOrFail();

            $precio = $this->resolverPrecio($linea, $servicio);
            $precioUnitario = array_key_exists('precio_unitario', $linea)
                ? (float) $linea['precio_unitario']
                : (float) ($precio?->precio_base ?? 0);
            $ivaPorcentaje = array_key_exists('iva_porcentaje', $linea)
                ? (float) $linea['iva_porcentaje']
                : (float) ($precio?->iva_porcentaje ?? 21);
            $tarifa = $precio?->tipoTarifaServicio ?: $precio?->tarifa;

            $orden->lineas()->create(array_merge($this->calcularLinea([
                ...$linea,
                'precio_unitario' => $precioUnitario,
                'iva_porcentaje' => $ivaPorcentaje,
            ]), [
                'servicio_id' => $servicio->id,
                'estado_codigo' => 'pendiente',
                'descripcion' => $linea['descripcion'] ?? $servicio->nombre,
                'unidad_snapshot' => $servicio->unidad_servicio ?: 'unidad',
                'facturable' => (bool) ($linea['facturable'] ?? true),
                'orden' => $index + 1,
                'meta' => [
                    'observaciones' => $linea['observaciones'] ?? null,
                    'servicio_codigo' => $servicio->codigo,
                    'servicio_precio_id' => $precio?->id,
                    'tipo_tarifa_servicio_id' => $precio?->tipo_tarifa_servicio_id ?? ($linea['tipo_tarifa_servicio_id'] ?? null),
                    'tipo_tarifa_codigo' => $tarifa?->codigo,
                    'tipo_tarifa_nombre' => $tarifa?->nombre,
                ],
            ]));
        }

        return $this->calcularTotalesDesdeLineas($orden->fresh('lineas'));
    }

    private function calcularTotalesDesdeLineas(OrdenTrabajo $orden): array
    {
        $orden->loadMissing('lineas');

        $subtotal = round((float) $orden->lineas->sum('base_imponible'), 2);
        $ivaTotal = round((float) $orden->lineas->sum('cuota_iva'), 2);

        return [
            'subtotal' => $subtotal,
            'iva_total' => $ivaTotal,
            'total' => round($subtotal + $ivaTotal, 2),
        ];
    }

    private function resolverEstadoInicial(array $data): OrdenTrabajoEstado
    {
        $codigo = $this->fechaProgramadaInicio($data) ? 'programada' : 'pendiente';
        $estado = OrdenTrabajoEstado::query()->where('codigo', $codigo)->first();

        if (!$estado) {
            throw new RuntimeException('No existe el estado inicial "' . $codigo . '". Ejecuta los seeders de estados de orden.');
        }

        return $estado;
    }

    private function resolverPrioridad(?int $prioridadId, ?string $prioridadCodigo): ?OrdenTrabajoPrioridad
    {
        if ($prioridadId) {
            return OrdenTrabajoPrioridad::query()->whereKey($prioridadId)->first();
        }

        if ($prioridadCodigo) {
            return OrdenTrabajoPrioridad::query()->where('codigo', $prioridadCodigo)->first();
        }

        return OrdenTrabajoPrioridad::query()->where('codigo', 'normal')->first();
    }

    private function resolverPrecio(array $linea, Servicio $servicio): ?ServicioPrecio
    {
        $query = $servicio->precios()->with('tipoTarifaServicio');

        if (!empty($linea['servicio_precio_id'])) {
            return (clone $query)->whereKey((int) $linea['servicio_precio_id'])->first();
        }

        if (!empty($linea['tipo_tarifa_servicio_id'])) {
            return (clone $query)
                ->where('tipo_tarifa_servicio_id', (int) $linea['tipo_tarifa_servicio_id'])
                ->first();
        }

        return (clone $query)->orderBy('id')->first();
    }

    private function fechaProgramadaInicio(array $data): ?string
    {
        return $data['fecha_programada_inicio'] ?? $data['fecha_programada'] ?? null;
    }

    private function generarNumeroDesdeId(OrdenTrabajo $orden): string
    {
        return sprintf('OT-%s-%06d', $orden->created_at->format('Ymd'), $orden->id);
    }

    private function cargarRelaciones(OrdenTrabajo $orden): OrdenTrabajo
    {
        return $orden->fresh([
            'cliente.localizaciones',
            'localizacionCliente',
            'estado',
            'prioridad',
            'tecnicoResponsable',
            'lineas.servicio',
            'eventosCalendario',
        ]);
    }
}
