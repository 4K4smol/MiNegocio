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

class OrdenTrabajoCreacionService
{
    public function __construct(private readonly CalendarioEventoService $calendarioEventoService) {}

    public function crear(array $data, User $user): OrdenTrabajo
    {
        return DB::transaction(function () use ($data, $user): OrdenTrabajo {
            $estado = OrdenTrabajoEstado::query()->where('codigo', 'pendiente')->first();
            if (! $estado) {
                throw new RuntimeException('No existe el estado inicial "pendiente". Ejecuta los seeders de estados de orden.');
            }

            $prioridad = $this->resolverPrioridad($data['prioridad_id'] ?? null, $data['prioridad_codigo'] ?? null);

            $orden = OrdenTrabajo::query()->create([
                'empresa_id' => $user->empresa_id,
                'cliente_id' => $data['cliente_id'],
                'localizacion_cliente_id' => $data['localizacion_cliente_id'] ?? null,
                'numero' => $this->generarNumero((int) $user->empresa_id),
                'estado_id' => $estado->id,
                'estado_codigo' => $estado->codigo,
                'prioridad_id' => $prioridad->id,
                'prioridad_codigo' => $prioridad->codigo,
                'fecha_apertura' => now()->toDateString(),
                'fecha_programada_inicio' => $data['fecha_programada_inicio'] ?? null,
                'fecha_programada_fin' => $data['fecha_programada_fin'] ?? null,
                'tecnico_responsable_id' => $data['tecnico_responsable_id'] ?? null,
                'notas_cliente' => $data['notas_cliente'] ?? null,
                'notas_internas' => $data['notas_internas'] ?? null,
            ]);

            foreach ($data['lineas'] as $index => $linea) {
                $this->crearLinea($orden, $linea, $index + 1, (int) $user->empresa_id);
            }

            $this->calendarioEventoService->crearOActualizarDesdeOrden($orden, $user);

            return $orden->fresh([
                'cliente.localizaciones',
                'localizacionCliente',
                'estado',
                'prioridad',
                'tecnicoResponsable',
                'lineas.servicio',
                'eventosCalendario',
            ]);
        });
    }

    private function resolverPrioridad(?int $prioridadId, ?string $prioridadCodigo): OrdenTrabajoPrioridad
    {
        if ($prioridadId) {
            $prioridad = OrdenTrabajoPrioridad::query()->whereKey($prioridadId)->first();
        } elseif ($prioridadCodigo) {
            $prioridad = OrdenTrabajoPrioridad::query()->where('codigo', $prioridadCodigo)->first();
        } else {
            $prioridad = OrdenTrabajoPrioridad::query()->where('codigo', 'normal')->first();
        }

        if (! $prioridad) {
            throw new RuntimeException('No existe la prioridad por defecto "normal". Ejecuta los seeders de prioridades de órdenes.');
        }

        return $prioridad;
    }

    private function crearLinea(OrdenTrabajo $orden, array $linea, int $ordenLinea, int $empresaId): void
    {
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

        $calc = $this->calcularLinea([
            ...$linea,
            'precio_unitario' => $precioUnitario,
            'iva_porcentaje' => $ivaPorcentaje,
        ]);

        $tarifa = $precio?->tipoTarifaServicio ?: $precio?->tarifa;

        $orden->lineas()->create(array_merge($calc, [
            'servicio_id' => $servicio->id,
            'estado_codigo' => 'pendiente',
            'descripcion' => $linea['descripcion'] ?? $servicio->nombre,
            'unidad_snapshot' => $servicio->unidad_servicio ?: 'unidad',
            'facturable' => (bool) ($linea['facturable'] ?? true),
            'orden' => $ordenLinea,
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

    private function resolverPrecio(array $linea, Servicio $servicio): ?ServicioPrecio
    {
        $query = $servicio->precios()->with('tipoTarifaServicio');

        if (! empty($linea['servicio_precio_id'])) {
            return (clone $query)->whereKey((int) $linea['servicio_precio_id'])->first();
        }

        if (! empty($linea['tipo_tarifa_servicio_id'])) {
            return (clone $query)
                ->where('tipo_tarifa_servicio_id', (int) $linea['tipo_tarifa_servicio_id'])
                ->first();
        }

        return (clone $query)->orderBy('id')->first();
    }

    public function calcularLinea(array $linea): array
    {
        $cantidad = (float) $linea['cantidad'];
        $precio = (float) $linea['precio_unitario'];
        $desc = (float) ($linea['descuento_porcentaje'] ?? 0);
        $iva = (float) $linea['iva_porcentaje'];
        $base = round($cantidad * $precio, 2);
        $descuento = round($base * $desc / 100, 2);
        $baseImponible = round($base - $descuento, 2);
        $cuotaIva = round($baseImponible * $iva / 100, 2);

        return [
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'descuento_porcentaje' => $desc,
            'base_imponible' => $baseImponible,
            'iva_porcentaje' => $iva,
            'cuota_iva' => $cuotaIva,
            'total' => round($baseImponible + $cuotaIva, 2),
        ];
    }

    private function generarNumero(int $empresaId): string
    {
        $prefix = 'OT-' . now()->format('Ymd') . '-';
        $count = OrdenTrabajo::query()
            ->where('empresa_id', $empresaId)
            ->where('numero', 'like', $prefix . '%')
            ->lockForUpdate()
            ->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
