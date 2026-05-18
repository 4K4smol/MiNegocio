<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cliente;
use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Models\Servicio;
use App\Models\TipoFactura;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FacturaService
{
    private const ROL_ADMIN = 'admin';

    private const SERIE_DEFAULT = 'A';

    private const TIPOS_FISCALES = ['ordinaria', 'simplificada', 'rectificativa', 'recapitulativa'];

    public function __construct(
        private readonly NumeracionFacturaService $numeracionFacturaService,
        private readonly RegistroFacturacionService $registroFacturacionService,
        private readonly RegistroEventoFacturacionService $registroEventoFacturacionService,
        private readonly FacturaHistorialService $historialService
    ) {}

    public function crearBorrador(array $data, User $user): Factura
    {
        return DB::transaction(function () use ($data, $user): Factura {
            $empresaId = $this->empresaIdDesdeUsuario($user, $data);
            $cliente = $this->clienteEmpresa((int) $data['cliente_id'], $empresaId);
            $tipoFactura = $this->tipoFactura((string) ($data['tipo_factura_codigo'] ?? 'ordinaria'));
            $estadoBorrador = $this->estadoFactura('borrador');

            $factura = Factura::query()->create($this->datosCabecera($data, $user, $empresaId, $cliente, $tipoFactura, $estadoBorrador));
            $this->sincronizarLineas($factura, collect($data['lineas'] ?? []), $empresaId);

            $this->historialService->registrar($factura, 'factura_creada', $user, null, $estadoBorrador->id, 'Factura creada en borrador.');

            return $factura->fresh($this->relacionesCompletas());
        });
    }

    public function actualizarBorrador(Factura $factura, array $data, User $user): Factura
    {
        return DB::transaction(function () use ($factura, $data, $user): Factura {
            $factura->loadMissing(['estadoFactura', 'tipoFactura']);
            $this->validarAcceso($factura, $user);

            if ($factura->estadoFactura?->codigo !== 'borrador') {
                throw new RuntimeException('Solo se pueden editar facturas en borrador.');
            }

            $empresaId = (int) $factura->empresa_id;
            $cliente = isset($data['cliente_id'])
                ? $this->clienteEmpresa((int) $data['cliente_id'], $empresaId)
                : $factura->cliente()->firstOrFail();

            $tipoFactura = isset($data['tipo_factura_codigo'])
                ? $this->tipoFactura((string) $data['tipo_factura_codigo'])
                : $factura->tipoFactura()->firstOrFail();

            $factura->fill($this->datosCabecera($data, $user, $empresaId, $cliente, $tipoFactura, $factura->estadoFactura, $factura));
            $factura->updated_by = $user->id;
            $factura->save();

            if (array_key_exists('lineas', $data)) {
                $factura->lineas()->delete();
                $factura->impuestos()->delete();
                $this->sincronizarLineas($factura, collect($data['lineas'] ?? []), $empresaId);
            }

            $this->historialService->registrar($factura, 'factura_actualizada_borrador', $user, $factura->estado_factura_id, $factura->estado_factura_id, 'Factura actualizada en borrador.');

            return $factura->fresh($this->relacionesCompletas());
        });
    }

    public function emitir(Factura $factura, User $user, array $data = []): Factura
    {
        return DB::transaction(function () use ($factura, $user, $data): Factura {
            $factura->loadMissing(['estadoFactura', 'tipoFactura', 'lineas', 'impuestos']);
            $this->validarAcceso($factura, $user);

            if ($factura->estadoFactura?->codigo !== 'borrador') {
                throw new RuntimeException('Solo se pueden emitir facturas en borrador.');
            }

            if ($factura->lineas->isEmpty()) {
                throw new RuntimeException('No se puede emitir una factura sin lineas.');
            }

            $estadoAnteriorId = $factura->estado_factura_id;
            $esFiscal = $this->esTipoFiscal($factura);
            $estadoNuevo = $this->estadoFactura($esFiscal ? 'emitida' : 'enviada');

            if ($esFiscal && ! $factura->numero) {
                $numeracion = $this->numeracionFacturaService->siguiente(
                    (int) $factura->empresa_id,
                    (string) ($data['serie'] ?? $factura->serie ?: self::SERIE_DEFAULT),
                    (int) ($data['ejercicio'] ?? now()->year)
                );

                $factura->serie = $numeracion['serie'];
                $factura->numero = $numeracion['numero'];
                $factura->numero_completo = $numeracion['numero_completo'];
            }

            $factura->estado_factura_id = $estadoNuevo->id;
            $factura->fecha_emision = $data['fecha_emision'] ?? $factura->fecha_emision ?? now()->toDateString();
            $factura->fecha_operacion = $data['fecha_operacion'] ?? $factura->fecha_operacion ?? $factura->fecha_emision;
            $factura->updated_by = $user->id;
            $factura->save();

            $this->historialService->registrar($factura, 'factura_emitida', $user, $estadoAnteriorId, $estadoNuevo->id, $esFiscal ? 'Factura fiscal emitida.' : 'Documento no fiscal emitido.');

            if ($esFiscal) {
                $registroAlta = $this->registroFacturacionService->crearRegistroFacturacionAlta($factura->fresh(['impuestos']));

                $this->registroEventoFacturacionService->registrar([
                    'empresa_id' => $factura->empresa_id,
                    'user_id' => $user->id,
                    'factura_id' => $factura->id,
                    'registro_facturacion_id' => $registroAlta->id,
                    'codigo_evento' => 'REGISTRO_FACTURACION_ALTA_CREADO',
                    'descripcion' => 'Registro de facturacion de alta generado.',
                ]);
            }

            return $factura->fresh($this->relacionesCompletas());
        });
    }

    public function relacionesCompletas(): array
    {
        return ['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion', 'cobros', 'historial'];
    }

    private function datosCabecera(
        array $data,
        User $user,
        int $empresaId,
        Cliente $cliente,
        TipoFactura $tipoFactura,
        EstadoFactura $estadoFactura,
        ?Factura $factura = null
    ): array {
        $empresa = $user->empresa_id === $empresaId ? $user->empresa : null;
        $empresa ??= \App\Models\Empresa::query()->findOrFail($empresaId);
        $cliente->loadMissing(['localizacionPrincipal']);

        return [
            'empresa_id' => $empresaId,
            'cliente_id' => $cliente->id,
            'tipo_factura_id' => $tipoFactura->id,
            'estado_factura_id' => $estadoFactura->id,
            'serie' => (string) ($data['serie'] ?? $factura?->serie ?? self::SERIE_DEFAULT),
            'fecha_emision' => $data['fecha_emision'] ?? $factura?->fecha_emision ?? now()->toDateString(),
            'fecha_operacion' => $data['fecha_operacion'] ?? $factura?->fecha_operacion ?? now()->toDateString(),
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? $factura?->fecha_vencimiento,
            'moneda' => strtoupper((string) ($data['moneda'] ?? $factura?->moneda ?? 'EUR')),
            'emisor_nif' => $empresa->nif,
            'emisor_nombre_razon_social' => $empresa->nombre_fiscal,
            'emisor_domicilio_fiscal' => $empresa->direccion_fiscal ?: 'N/D',
            'receptor_nif' => $cliente->dni_cif,
            'receptor_nombre_razon_social' => $cliente->razon_social ?: $cliente->nombre_completo,
            'receptor_domicilio_fiscal' => $cliente->localizacionPrincipal?->direccion ?? 'N/D',
            'receptor_cp' => $cliente->localizacionPrincipal?->codigo_postal,
            'receptor_municipio' => $cliente->localizacionPrincipal?->municipio,
            'receptor_provincia' => $cliente->localizacionPrincipal?->provincia,
            'receptor_pais' => $cliente->localizacionPrincipal?->pais,
            'observaciones' => $data['observaciones'] ?? $factura?->observaciones,
            'metadatos' => $data['metadatos'] ?? $factura?->metadatos,
            'created_by' => $factura?->created_by ?? $user->id,
            'updated_by' => $user->id,
        ];
    }

    private function sincronizarLineas(Factura $factura, Collection $lineas, int $empresaId): void
    {
        if ($lineas->isEmpty()) {
            return;
        }

        $totales = ['base' => 0.0, 'iva' => 0.0, 'retencion' => 0.0, 'descuento' => 0.0, 'total' => 0.0];

        foreach ($lineas->values() as $idx => $lineaData) {
            $servicio = isset($lineaData['servicio_id'])
                ? Servicio::query()->where('empresa_id', $empresaId)->whereKey((int) $lineaData['servicio_id'])->first()
                : null;

            if (isset($lineaData['servicio_id']) && ! $servicio) {
                throw new RuntimeException('No se puede facturar un servicio de otra empresa.');
            }

            $linea = $this->calcularLinea($lineaData);
            $descripcion = (string) ($lineaData['concepto'] ?? $lineaData['descripcion'] ?? $servicio?->nombre ?? 'Servicio');

            $factura->lineas()->create([
                'orden_trabajo_linea_id' => $lineaData['orden_trabajo_linea_id'] ?? null,
                'servicio_id' => $servicio?->id,
                'servicio_nombre_snapshot' => $servicio?->nombre,
                'unidad_servicio_codigo' => $servicio?->unidad_servicio,
                'descripcion' => $descripcion,
                'cantidad' => $linea['cantidad'],
                'precio_unitario' => $linea['precio_unitario'],
                'base_imponible' => $linea['base'],
                'iva_porcentaje' => $linea['iva_porcentaje'],
                'retencion_porcentaje' => $linea['retencion_porcentaje'],
                'descuento_porcentaje' => $linea['descuento_porcentaje'],
                'subtotal' => $linea['base'],
                'total_iva' => $linea['iva'],
                'cuota_retencion' => $linea['retencion'],
                'total_linea' => $linea['total'],
                'total' => $linea['total'],
                'orden' => (int) ($lineaData['orden'] ?? $idx + 1),
            ]);

            $totales['base'] += $linea['base'];
            $totales['iva'] += $linea['iva'];
            $totales['retencion'] += $linea['retencion'];
            $totales['descuento'] += $linea['descuento'];
            $totales['total'] += $linea['total'];
        }

        $this->crearImpuestos($factura);

        $factura->subtotal = round($totales['base'], 2);
        $factura->cuota_iva = round($totales['iva'], 2);
        $factura->base_imponible = round($totales['base'], 2);
        $factura->total_iva = round($totales['iva'], 2);
        $factura->total_retencion = round($totales['retencion'], 2);
        $factura->total_descuento = round($totales['descuento'], 2);
        $factura->total = round($totales['total'], 2);
        $factura->save();
    }

    private function crearImpuestos(Factura $factura): void
    {
        $factura->impuestos()->delete();

        $factura->lineas()
            ->get()
            ->groupBy(fn ($linea) => (string) $linea->iva_porcentaje)
            ->each(function (Collection $items, string $iva) use ($factura): void {
                $base = round((float) $items->sum('base_imponible'), 2);
                $cuota = round((float) $items->sum('total_iva'), 2);

                $factura->impuestos()->create([
                    'tipo_impuesto' => 'IVA',
                    'impuesto_codigo' => 'IVA',
                    'impuesto_nombre' => 'Impuesto sobre el Valor Anadido',
                    'base' => $base,
                    'base_imponible' => $base,
                    'porcentaje' => (float) $iva,
                    'tipo_porcentaje' => (float) $iva,
                    'cuota' => $cuota,
                ]);
            });
    }

    private function calcularLinea(array $linea): array
    {
        $cantidad = round((float) ($linea['cantidad'] ?? 1), 4);
        $precio = round((float) ($linea['precio_unitario'] ?? 0), 4);
        $iva = round((float) ($linea['iva_porcentaje'] ?? 21), 2);
        $descuento = round((float) ($linea['descuento_porcentaje'] ?? 0), 2);
        $retencion = round((float) ($linea['retencion_porcentaje'] ?? 0), 2);

        if ($cantidad <= 0 || $precio < 0 || $iva < 0 || $iva > 100 || $descuento < 0 || $descuento > 100 || $retencion < 0 || $retencion > 100) {
            throw new RuntimeException('La linea de factura contiene importes o porcentajes no validos.');
        }

        $bruto = $cantidad * $precio;
        $descuentoImporte = round($bruto * $descuento / 100, 2);
        $base = round($bruto - $descuentoImporte, 2);
        $ivaImporte = round($base * $iva / 100, 2);
        $retencionImporte = round($base * $retencion / 100, 2);

        return [
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'iva_porcentaje' => $iva,
            'descuento_porcentaje' => $descuento,
            'retencion_porcentaje' => $retencion,
            'descuento' => $descuentoImporte,
            'base' => $base,
            'iva' => $ivaImporte,
            'retencion' => $retencionImporte,
            'total' => round($base + $ivaImporte - $retencionImporte, 2),
        ];
    }

    private function clienteEmpresa(int $clienteId, int $empresaId): Cliente
    {
        $cliente = Cliente::query()->where('empresa_id', $empresaId)->whereKey($clienteId)->first();

        if (! $cliente) {
            throw new RuntimeException('El cliente no pertenece a la empresa.');
        }

        return $cliente;
    }

    private function empresaIdDesdeUsuario(User $user, array $data): int
    {
        if (strtolower((string) $user->role?->nombre) === self::ROL_ADMIN) {
            return (int) ($data['empresa_id'] ?? 0);
        }

        return (int) $user->empresa_id;
    }

    private function tipoFactura(string $codigo): TipoFactura
    {
        $tipo = TipoFactura::query()->where('codigo', $codigo)->first();

        if (! $tipo) {
            throw new RuntimeException('No existe el tipo de factura "' . $codigo . '".');
        }

        return $tipo;
    }

    private function estadoFactura(string $codigo): EstadoFactura
    {
        $estado = EstadoFactura::query()->where('codigo', $codigo)->first();

        if (! $estado) {
            throw new RuntimeException('No existe el estado de factura "' . $codigo . '".');
        }

        return $estado;
    }

    private function esTipoFiscal(Factura $factura): bool
    {
        return in_array((string) $factura->tipoFactura?->codigo, self::TIPOS_FISCALES, true);
    }

    private function validarAcceso(Factura $factura, User $user): void
    {
        if (strtolower((string) $user->role?->nombre) === self::ROL_ADMIN) {
            return;
        }

        if ((int) $factura->empresa_id !== (int) $user->empresa_id) {
            throw new RuntimeException('No puedes operar con facturas de otra empresa.');
        }
    }
}
