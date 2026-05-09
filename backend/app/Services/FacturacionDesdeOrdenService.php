<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Models\ModoRemisionFacturacion;
use App\Models\OrdenTrabajo;
use App\Models\OrdenTrabajoEstado;
use App\Models\RegistroFacturacion;
use App\Models\TipoFactura;
use App\Models\TipoRegistroFacturacion;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FacturacionDesdeOrdenService
{
    private const ROL_ADMIN = 'admin';

    private const ESTADO_ORDEN_COMPLETADA = 'completada';
    private const ESTADO_ORDEN_FACTURADA = 'facturada';

    private const TIPO_FACTURA_ORDINARIA = 'ordinaria';

    private const ESTADO_FACTURA_EMITIDA = 'emitida';
    private const ESTADO_FACTURA_BORRADOR = 'borrador';

    private const TIPO_REGISTRO_ALTA = 'alta';
    private const MODO_REMISION_VERIFACTU = 'verifactu';

    private const SERIE_FACTURA = 'A';
    private const CODIGO_SISTEMA = 'MINNEGOCIO-RRSIF';

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
                ->filter(function ($linea): bool {
                    $cantidad = (float) $linea->cantidad;
                    $facturado = (float) ($linea->facturado_cantidad ?? 0);

                    return (bool) $linea->facturable && $cantidad > $facturado;
                })
                ->values();

            if ($facturables->isEmpty()) {
                throw new RuntimeException('La orden no tiene líneas facturables pendientes.');
            }

            $tipoFactura = $this->obtenerTipoFactura();
            $estadoFactura = $this->obtenerEstadoFactura();

            $fechaEmision = now()->toDateString();
            $numeroFactura = $this->generarNumeroFactura((int) $orden->empresa_id);

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

            $this->crearRegistroFacturacionAlta($factura);

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

        if (! $orden->empresa || ! $orden->cliente) {
            throw new RuntimeException('La orden no tiene empresa o cliente asociado para generar snapshot fiscal.');
        }
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
            ->where('numero', 'like', 'F-' . $anio . '-%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $siguienteNumero = 1;

        if ($ultimaFactura) {
            $ultimoSecuencial = (int) substr((string) $ultimaFactura->numero, -6);
            $siguienteNumero = $ultimoSecuencial + 1;
        }

        return 'F-' . $anio . '-' . str_pad((string) $siguienteNumero, 6, '0', STR_PAD_LEFT);
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

        $grupos = $lineasFactura->groupBy(fn($linea) => (string) $linea->iva_porcentaje);

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

    private function crearRegistroFacturacionAlta(Factura $factura): void
    {
        $tipoRegistroAlta = TipoRegistroFacturacion::query()
            ->where('codigo', self::TIPO_REGISTRO_ALTA)
            ->first();

        $modoVerifactu = ModoRemisionFacturacion::query()
            ->where('codigo', self::MODO_REMISION_VERIFACTU)
            ->first();

        if (! $tipoRegistroAlta || ! $modoVerifactu) {
            throw new RuntimeException('Faltan seeders de tipos/modos de registro de facturación.');
        }

        $registroAnterior = RegistroFacturacion::query()
            ->where('empresa_id', $factura->empresa_id)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $generadoAt = now();
        $fechaExpedicion = is_string($factura->fecha_emision)
            ? $factura->fecha_emision
            : $factura->fecha_emision->toDateString();

        $nombreSistema = (string) config('app.name', 'MiNegocio');
        $versionSistema = (string) config('app.version', '1.0.0');
        $numeroInstalacion = 'EMP-' . $factura->empresa_id;

        $hashPayload = [
            'tipo' => self::TIPO_REGISTRO_ALTA,
            'emisor_nif' => $factura->emisor_nif,
            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'fecha_expedicion' => $fechaExpedicion,
            'tipo_factura_id' => $factura->tipo_factura_id,
            'cuota_total' => number_format((float) $factura->cuota_iva, 2, '.', ''),
            'importe_total' => number_format((float) $factura->total, 2, '.', ''),
            'registro_anterior_hash_64' => $registroAnterior?->hash_actual,
            'generado_at' => $generadoAt->toISOString(),
            'sistema' => [
                'codigo' => self::CODIGO_SISTEMA,
                'nombre' => $nombreSistema,
                'version' => $versionSistema,
                'numero_instalacion' => $numeroInstalacion,
            ],
        ];

        $hashActual = hash(
            'sha256',
            json_encode($hashPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        RegistroFacturacion::query()->create([
            'factura_id' => $factura->id,
            'tipo_registro_facturacion_id' => $tipoRegistroAlta->id,
            'modo_remision_facturacion_id' => $modoVerifactu->id,

            'empresa_id' => $factura->empresa_id,
            'emisor_nif' => $factura->emisor_nif,
            'emisor_nombre_razon_social' => $factura->emisor_nombre_razon_social,

            'serie' => $factura->serie,
            'numero' => $factura->numero,
            'fecha_expedicion' => $fechaExpedicion,
            'tipo_factura_id' => $factura->tipo_factura_id,

            'cuota_total' => $factura->cuota_iva,
            'importe_total' => $factura->total,

            'primer_registro_cadena' => $registroAnterior === null,

            'registro_anterior_nif' => $registroAnterior?->emisor_nif,
            'registro_anterior_serie' => $registroAnterior?->serie,
            'registro_anterior_numero' => $registroAnterior?->numero,
            'registro_anterior_fecha_expedicion' => $registroAnterior?->fecha_expedicion,
            'registro_anterior_hash_64' => $registroAnterior?->hash_actual,

            'tipo_huella' => 'sha256',
            'hash_actual' => $hashActual,

            'generado_at' => $generadoAt,

            'xml_contenido' => $this->buildRegistroFacturacionAltaXml(
                factura: $factura,
                registroAnterior: $registroAnterior,
                hashActual: $hashActual,
                generadoAt: $generadoAt,
                fechaExpedicion: $fechaExpedicion,
                nombreSistema: $nombreSistema,
                versionSistema: $versionSistema,
                numeroInstalacion: $numeroInstalacion
            ),

            'xml_version' => '1.0',

            'codigo_sistema_informatico' => self::CODIGO_SISTEMA,
            'nombre_sistema' => $nombreSistema,
            'version_sistema' => $versionSistema,
            'numero_instalacion' => $numeroInstalacion,

            'tipo_uso_posible_solo_verifactu' => true,
            'tipo_uso_posible_multi_ot' => true,
            'indicador_multiples_ot' => false,

            'productor_nif' => $factura->emisor_nif,
            'productor_nombre' => $nombreSistema,
        ]);
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

    private function buildRegistroFacturacionAltaXml(
        Factura $factura,
        ?RegistroFacturacion $registroAnterior,
        string $hashActual,
        Carbon $generadoAt,
        string $fechaExpedicion,
        string $nombreSistema,
        string $versionSistema,
        string $numeroInstalacion
    ): string {
        $xml = [
            '<RegistroFacturacion tipo="alta">',
            '<Factura>',
            '<EmisorNif>' . $this->xml($factura->emisor_nif) . '</EmisorNif>',
            '<EmisorNombre>' . $this->xml($factura->emisor_nombre_razon_social) . '</EmisorNombre>',
            '<Serie>' . $this->xml($factura->serie) . '</Serie>',
            '<Numero>' . $this->xml($factura->numero) . '</Numero>',
            '<FechaExpedicion>' . $this->xml($fechaExpedicion) . '</FechaExpedicion>',
            '<CuotaTotal>' . number_format((float) $factura->cuota_iva, 2, '.', '') . '</CuotaTotal>',
            '<ImporteTotal>' . number_format((float) $factura->total, 2, '.', '') . '</ImporteTotal>',
            '</Factura>',
            '<Encadenamiento>',
        ];

        if ($registroAnterior) {
            $fechaAnterior = is_string($registroAnterior->fecha_expedicion)
                ? $registroAnterior->fecha_expedicion
                : $registroAnterior->fecha_expedicion->toDateString();

            $xml[] = '<RegistroAnterior>';
            $xml[] = '<EmisorNif>' . $this->xml($registroAnterior->emisor_nif) . '</EmisorNif>';
            $xml[] = '<Serie>' . $this->xml($registroAnterior->serie) . '</Serie>';
            $xml[] = '<Numero>' . $this->xml($registroAnterior->numero) . '</Numero>';
            $xml[] = '<FechaExpedicion>' . $this->xml($fechaAnterior) . '</FechaExpedicion>';
            $xml[] = '<Hash>' . $this->xml($registroAnterior->hash_actual) . '</Hash>';
            $xml[] = '</RegistroAnterior>';
        } else {
            $xml[] = '<PrimerRegistroCadena>true</PrimerRegistroCadena>';
        }

        $xml[] = '</Encadenamiento>';
        $xml[] = '<Huella tipo="sha256">' . $this->xml($hashActual) . '</Huella>';
        $xml[] = '<SistemaInformatico>';
        $xml[] = '<Codigo>' . self::CODIGO_SISTEMA . '</Codigo>';
        $xml[] = '<Nombre>' . $this->xml($nombreSistema) . '</Nombre>';
        $xml[] = '<Version>' . $this->xml($versionSistema) . '</Version>';
        $xml[] = '<NumeroInstalacion>' . $this->xml($numeroInstalacion) . '</NumeroInstalacion>';
        $xml[] = '</SistemaInformatico>';
        $xml[] = '<GeneradoAt>' . $this->xml($generadoAt->toISOString()) . '</GeneradoAt>';
        $xml[] = '</RegistroFacturacion>';

        return implode('', $xml);
    }

    private function xml(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
