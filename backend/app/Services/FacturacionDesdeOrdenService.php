<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoEstado;
use App\Models\TipoFactura;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FacturacionDesdeOrdenService
{
    public function __construct(private readonly RegistroFacturacionService $registroFacturacionService, private readonly RegistroEventoFacturacionService $registroEventoFacturacionService) {}

    private const ROL_ADMIN = 'admin';

    private const ESTADO_ORDEN_COMPLETADA = 'completada';

    private const ESTADO_ORDEN_FACTURADA = 'facturada';

    private const TIPO_FACTURA_ORDINARIA = 'ordinaria';

    private const ESTADO_FACTURA_EMITIDA = 'emitida';

    private const ESTADO_FACTURA_BORRADOR = 'borrador';

    private const SERIE_FACTURA = 'A';

    public function generarDesdeOrden(OrdenTrabajo $orden, User $user): Factura
    {
        return DB::transaction(function () use ($orden, $user): Factura {
            $orden->loadMissing([
                'empresa',
                'cliente.localizacionPrincipal',
                'lineas.servicio',
                'estado',
            ]);

            $this->validarOrdenFacturable($orden, $user);

            $facturables = $orden->lineas
                ->filter(fn ($linea) => (bool) $linea->facturable && (float) $linea->cantidad > (float) ($linea->facturado_cantidad ?? 0))
                ->values();

            if ($facturables->isEmpty()) {
                throw new RuntimeException('La orden no tiene líneas facturables pendientes.');
            }

            $tipoFactura = $this->obtenerTipoFactura();
            $estadoFactura = $this->obtenerEstadoFactura();
            $numeroFactura = $this->generarNumeroFactura((int) $orden->empresa_id);
            $fechaEmision = now()->toDateString();

            $factura = Factura::query()->create([
                'empresa_id' => $orden->empresa_id,
                'cliente_id' => $orden->cliente_id,
                'tipo_factura_id' => $tipoFactura->id,
                'estado_factura_id' => $estadoFactura->id,

                'serie' => self::SERIE_FACTURA,
                'numero' => $numeroFactura,

                'fecha_emision' => $fechaEmision,
                'fecha_operacion' => $fechaEmision,

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

            $lineasFactura = $this->crearLineasFactura($factura, $facturables);

            $this->crearImpuestosFactura($factura, $lineasFactura);

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $factura->empresa_id,
                'user_id' => $user->id,
                'factura_id' => $factura->id,
                'codigo_evento' => 'FACTURA_GENERADA_DESDE_ORDEN',
                'descripcion' => 'Factura generada desde orden de trabajo.',
            ]);

            $registroAlta = $this->registroFacturacionService->crearRegistroFacturacionAlta($factura);

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $factura->empresa_id,
                'user_id' => $user->id,
                'factura_id' => $factura->id,
                'registro_facturacion_id' => $registroAlta->id,
                'codigo_evento' => 'REGISTRO_FACTURACION_ALTA_CREADO',
                'descripcion' => 'Registro de facturación de alta generado.',
            ]);

            $this->marcarOrdenComoFacturada($orden);

            return $factura->fresh(['lineas', 'impuestos']);
        });
    }

    private function validarOrdenFacturable(OrdenTrabajo $orden, User $user): void
    {
        $esAdmin = strtolower((string) $user->role?->nombre) === self::ROL_ADMIN;

        if (! $esAdmin && (int) $orden->empresa_id !== (int) $user->empresa_id) {
            throw new RuntimeException('No puedes facturar órdenes de otra empresa.');
        }

        if (strtolower((string) $orden->estado?->codigo) !== self::ESTADO_ORDEN_COMPLETADA) {
            throw new RuntimeException('Solo se pueden facturar órdenes completadas.');
        }

        if ($this->ordenTieneFacturaAsociada($orden)) {
            throw new RuntimeException('La orden ya tiene una factura asociada.');
        }

        if (! $orden->empresa || ! $orden->cliente) {
            throw new RuntimeException('La orden no tiene empresa o cliente asociado para generar snapshot fiscal.');
        }
    }

    private function ordenTieneFacturaAsociada(OrdenTrabajo $orden): bool
    {
        $lineaIds = $orden->lineas->pluck('id');

        if ($lineaIds->isEmpty()) {
            return false;
        }

        return FacturaLinea::query()
            ->whereIn('orden_trabajo_linea_id', $lineaIds)
            ->exists();
    }

    private function obtenerTipoFactura(): TipoFactura
    {
        $tipo = TipoFactura::query()
            ->where('codigo', self::TIPO_FACTURA_ORDINARIA)
            ->first();

        if (! $tipo) {
            throw new RuntimeException('No existe el tipo de factura "ordinaria". Ejecuta los seeders de tipos de factura.');
        }

        return $tipo;
    }

    private function obtenerEstadoFactura(): EstadoFactura
    {
        $estado = EstadoFactura::query()
            ->where('codigo', self::ESTADO_FACTURA_EMITIDA)
            ->first();

        if (! $estado) {
            $estado = EstadoFactura::query()
                ->where('codigo', self::ESTADO_FACTURA_BORRADOR)
                ->first();
        }

        if (! $estado) {
            throw new RuntimeException('No existe un estado de factura válido ("emitida" o "borrador"). Ejecuta los seeders de estados de factura.');
        }

        return $estado;
    }

    private function generarNumeroFactura(int $empresaId): string
    {
        $anio = now()->year;

        $ultimaFactura = Factura::query()
            ->where('empresa_id', $empresaId)
            ->where('serie', self::SERIE_FACTURA)
            ->where('numero', 'like', 'F-'.$anio.'-%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $siguienteNumero = 1;

        if ($ultimaFactura) {
            $ultimoSecuencial = (int) substr((string) $ultimaFactura->numero, -6);
            $siguienteNumero = $ultimoSecuencial + 1;
        }

        return 'F-'.$anio.'-'.str_pad((string) $siguienteNumero, 6, '0', STR_PAD_LEFT);
    }

    private function crearLineasFactura(Factura $factura, Collection $facturables): Collection
    {
        $lineasFactura = collect();

        foreach ($facturables as $idx => $linea) {
            $cantidadTotal = (float) $linea->cantidad;
            $cantidadFacturada = (float) ($linea->facturado_cantidad ?? 0);
            $cantidadPendiente = round($cantidadTotal - $cantidadFacturada, 4);

            if ($cantidadPendiente <= 0) {
                continue;
            }

            $factorPendiente = $cantidadTotal > 0
                ? $cantidadPendiente / $cantidadTotal
                : 1;

            $baseImponible = round((float) $linea->base_imponible * $factorPendiente, 2);
            $cuotaIva = round((float) $linea->cuota_iva * $factorPendiente, 2);
            $total = round((float) $linea->total * $factorPendiente, 2);

            $lineaFactura = $factura->lineas()->create([
                'orden_trabajo_linea_id' => $linea->id,
                'servicio_id' => $linea->servicio_id,
                'servicio_nombre_snapshot' => $linea->servicio?->nombre,

                'descripcion' => $linea->descripcion,
                'cantidad' => $cantidadPendiente,
                'precio_unitario' => $linea->precio_unitario,

                'base_imponible' => $baseImponible,
                'iva_porcentaje' => $linea->iva_porcentaje,
                'descuento_porcentaje' => $linea->descuento_porcentaje,

                'subtotal' => $baseImponible,
                'total_iva' => $cuotaIva,
                'total' => $total,

                'orden' => $idx + 1,
            ]);

            $lineasFactura->push($lineaFactura);

            $linea->facturado_cantidad = $cantidadTotal;
            $linea->save();
        }

        if ($lineasFactura->isEmpty()) {
            throw new RuntimeException('No se pudo crear ninguna línea de factura.');
        }

        return $lineasFactura;
    }

    private function crearImpuestosFactura(Factura $factura, Collection $lineasFactura): void
    {
        $subtotal = 0.0;
        $cuotaIva = 0.0;

        $grupos = $lineasFactura->groupBy(fn ($linea) => (string) $linea->iva_porcentaje);

        foreach ($grupos as $iva => $items) {
            $base = round((float) $items->sum('base_imponible'), 2);
            $cuota = round((float) $items->sum('total_iva'), 2);

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
    }

    private function marcarOrdenComoFacturada(OrdenTrabajo $orden): void
    {
        $estadoFacturada = OrdenTrabajoEstado::query()
            ->whereRaw('LOWER(codigo) = ?', [self::ESTADO_ORDEN_FACTURADA])
            ->first();

        if (! $estadoFacturada) {
            throw new RuntimeException('No existe el estado de orden "facturada". Ejecuta los seeders de estados de orden.');
        }

        $orden->estado_id = $estadoFacturada->id;
        $orden->estado_codigo = $estadoFacturada->codigo;
        $orden->save();
    }
}
