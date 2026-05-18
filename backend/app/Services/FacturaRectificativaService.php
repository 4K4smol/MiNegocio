<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Models\TipoFactura;
use App\Models\TipoRectificacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FacturaRectificativaService
{
    private const ROL_ADMIN = 'admin';
    private const ESTADO_FACTURA_EMITIDA = 'emitida';
    private const TIPO_FACTURA_RECTIFICATIVA = 'rectificativa';
    private const TIPO_RECTIFICACION_DIFERENCIAS = 'por_diferencias';
    private const SERIE_RECTIFICATIVA = 'R';

    public function __construct(
        private readonly RegistroFacturacionService $registroFacturacionService,
        private readonly RegistroEventoFacturacionService $registroEventoFacturacionService,
        private readonly NumeracionFacturaService $numeracionFacturaService,
        private readonly FacturaHistorialService $historialService
    ) {}

    public function generarDesdeFactura(Factura $facturaOriginal, User $user, ?string $motivo = null): Factura
    {
        return DB::transaction(function () use ($facturaOriginal, $user, $motivo): Factura {
            $facturaOriginal->loadMissing(['lineas', 'impuestos', 'estadoFactura', 'tipoFactura']);
            $this->validarFacturaRectificable($facturaOriginal, $user);

            $tipoFactura = TipoFactura::query()->where('codigo', self::TIPO_FACTURA_RECTIFICATIVA)->first();
            $tipoRectificacion = TipoRectificacion::query()->where('codigo', self::TIPO_RECTIFICACION_DIFERENCIAS)->first();
            $estadoEmitida = EstadoFactura::query()->where('codigo', self::ESTADO_FACTURA_EMITIDA)->first();

            if (!$tipoFactura || !$tipoRectificacion || !$estadoEmitida) {
                throw new RuntimeException('Faltan catalogos para generar factura rectificativa.');
            }

            $numeracion = $this->numeracionFacturaService->siguiente((int) $facturaOriginal->empresa_id, self::SERIE_RECTIFICATIVA);
            $fechaEmision = now()->toDateString();

            $rectificativa = Factura::query()->create([
                'empresa_id' => $facturaOriginal->empresa_id,
                'cliente_id' => $facturaOriginal->cliente_id,
                'orden_trabajo_id' => $facturaOriginal->orden_trabajo_id,
                'tipo_factura_id' => $tipoFactura->id,
                'estado_factura_id' => $estadoEmitida->id,
                'factura_rectificada_id' => $facturaOriginal->id,
                'tipo_rectificacion_id' => $tipoRectificacion->id,
                'motivo_rectificacion' => $motivo ?: 'Rectificacion por diferencias.',
                'serie' => $numeracion['serie'],
                'numero' => $numeracion['numero'],
                'numero_completo' => $numeracion['numero_completo'],
                'fecha_emision' => $fechaEmision,
                'fecha_operacion' => $facturaOriginal->fecha_operacion?->toDateString() ?: $facturaOriginal->fecha_emision?->toDateString(),
                'moneda' => $facturaOriginal->moneda,
                'emisor_nif' => $facturaOriginal->emisor_nif,
                'emisor_nombre_razon_social' => $facturaOriginal->emisor_nombre_razon_social,
                'emisor_domicilio_fiscal' => $facturaOriginal->emisor_domicilio_fiscal,
                'receptor_nif' => $facturaOriginal->receptor_nif,
                'receptor_nombre_razon_social' => $facturaOriginal->receptor_nombre_razon_social,
                'receptor_domicilio_fiscal' => $facturaOriginal->receptor_domicilio_fiscal,
                'receptor_cp' => $facturaOriginal->receptor_cp,
                'receptor_municipio' => $facturaOriginal->receptor_municipio,
                'receptor_provincia' => $facturaOriginal->receptor_provincia,
                'receptor_pais' => $facturaOriginal->receptor_pais,
                'subtotal' => -1 * (float) $facturaOriginal->subtotal,
                'cuota_iva' => -1 * (float) $facturaOriginal->cuota_iva,
                'base_imponible' => -1 * (float) ($facturaOriginal->base_imponible ?: $facturaOriginal->subtotal),
                'total_iva' => -1 * (float) ($facturaOriginal->total_iva ?: $facturaOriginal->cuota_iva),
                'total_retencion' => -1 * (float) $facturaOriginal->total_retencion,
                'total_descuento' => -1 * (float) $facturaOriginal->total_descuento,
                'total' => -1 * (float) $facturaOriginal->total,
                'observaciones' => 'Factura rectificativa de ' . ($facturaOriginal->numero_completo ?: $facturaOriginal->serie . '-' . $facturaOriginal->numero),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            foreach ($facturaOriginal->lineas as $linea) {
                $rectificativa->lineas()->create([
                    'orden_trabajo_linea_id' => null,
                    'servicio_id' => $linea->servicio_id,
                    'unidad_servicio_codigo' => $linea->unidad_servicio_codigo,
                    'unidad_servicio_nombre_snapshot' => $linea->unidad_servicio_nombre_snapshot,
                    'servicio_nombre_snapshot' => $linea->servicio_nombre_snapshot,
                    'descripcion' => 'Rectificacion: ' . $linea->descripcion,
                    'cantidad' => -1 * (float) $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'base_imponible' => -1 * (float) $linea->base_imponible,
                    'iva_porcentaje' => $linea->iva_porcentaje,
                    'retencion_porcentaje' => $linea->retencion_porcentaje,
                    'descuento_porcentaje' => $linea->descuento_porcentaje,
                    'subtotal' => -1 * (float) $linea->subtotal,
                    'total_iva' => -1 * (float) $linea->total_iva,
                    'cuota_retencion' => -1 * (float) $linea->cuota_retencion,
                    'total_linea' => -1 * (float) ($linea->total_linea ?: $linea->total),
                    'total' => -1 * (float) $linea->total,
                    'orden' => $linea->orden,
                ]);
            }

            foreach ($facturaOriginal->impuestos as $impuesto) {
                $rectificativa->impuestos()->create([
                    'tipo_impuesto' => $impuesto->tipo_impuesto ?? 'IVA',
                    'impuesto_codigo' => $impuesto->impuesto_codigo,
                    'impuesto_nombre' => $impuesto->impuesto_nombre,
                    'base' => -1 * (float) ($impuesto->base ?: $impuesto->base_imponible),
                    'base_imponible' => -1 * (float) $impuesto->base_imponible,
                    'porcentaje' => $impuesto->porcentaje ?: $impuesto->tipo_porcentaje,
                    'tipo_porcentaje' => $impuesto->tipo_porcentaje,
                    'cuota' => -1 * (float) $impuesto->cuota,
                    'es_exento' => $impuesto->es_exento,
                    'motivo_exencion' => $impuesto->motivo_exencion,
                    'es_no_sujeto' => $impuesto->es_no_sujeto,
                    'motivo_no_sujecion' => $impuesto->motivo_no_sujecion,
                    'recargo_equivalencia_porcentaje' => $impuesto->recargo_equivalencia_porcentaje,
                    'recargo_equivalencia_cuota' => -1 * (float) $impuesto->recargo_equivalencia_cuota,
                    'calificacion' => $impuesto->calificacion,
                    'descripcion' => $impuesto->descripcion,
                ]);
            }

            $this->historialService->registrar($rectificativa, 'factura_rectificada', $user, null, $rectificativa->estado_factura_id, 'Factura rectificativa emitida.', [
                'factura_rectificada_id' => $facturaOriginal->id,
            ]);

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $rectificativa->empresa_id,
                'user_id' => $user->id,
                'factura_id' => $rectificativa->id,
                'codigo_evento' => 'FACTURA_RECTIFICATIVA_GENERADA',
                'descripcion' => 'Factura rectificativa generada desde factura emitida.',
            ]);

            $registroAlta = $this->registroFacturacionService->crearRegistroFacturacionAlta($rectificativa);

            $this->registroEventoFacturacionService->registrar([
                'empresa_id' => $rectificativa->empresa_id,
                'user_id' => $user->id,
                'factura_id' => $rectificativa->id,
                'registro_facturacion_id' => $registroAlta->id,
                'codigo_evento' => 'REGISTRO_FACTURACION_ALTA_RECTIFICATIVA_CREADO',
                'descripcion' => 'Registro de facturacion de alta para rectificativa generado.',
            ]);

            $estadoRectificada = EstadoFactura::query()->where('codigo', 'rectificada')->first();
            if ($estadoRectificada) {
                $estadoAnteriorId = $facturaOriginal->estado_factura_id;
                $facturaOriginal->estado_factura_id = $estadoRectificada->id;
                $facturaOriginal->save();

                $this->historialService->registrar($facturaOriginal, 'factura_rectificada', $user, $estadoAnteriorId, $estadoRectificada->id, 'Factura original marcada como rectificada.', [
                    'factura_rectificativa_id' => $rectificativa->id,
                ]);
            }

            return $rectificativa->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion', 'historial']);
        });
    }

    private function validarFacturaRectificable(Factura $factura, User $user): void
    {
        $esAdmin = strtolower((string) $user->role?->nombre) === self::ROL_ADMIN;

        if (!$esAdmin && (int) $factura->empresa_id !== (int) $user->empresa_id) {
            throw new RuntimeException('No puedes rectificar facturas de otra empresa.');
        }

        $codigo_tipo = $factura->tipoFactura?->codigo;
        if ($codigo_tipo === self::TIPO_FACTURA_RECTIFICATIVA) {
            throw new RuntimeException('No se puede rectificar una factura rectificativa.');
        }

        $codigo_estado = $factura->estadoFactura?->codigo;

        if ($codigo_estado === 'borrador') {
            throw new RuntimeException('No se pueden rectificar facturas en borrador. Edítalas directamente.');
        }

        if ($codigo_estado === 'anulada') {
            throw new RuntimeException('No se pueden rectificar facturas anuladas.');
        }

        if ($codigo_estado === 'rectificada') {
            throw new RuntimeException('No se puede rectificar una factura que ya ha sido rectificada.');
        }

        // Permitimos rectificar facturas emitidas, pagadas o con pago parcial
        $estados_permitidos = [self::ESTADO_FACTURA_EMITIDA, 'pagada', 'pagada_parcial'];
        if (!in_array($codigo_estado, $estados_permitidos, true)) {
            throw new RuntimeException('Solo se pueden rectificar facturas emitidas o pagadas.');
        }

        $tiene_alta = $factura->registrosFacturacion()
            ->whereHas('tipoRegistroFacturacion', fn ($q) => $q->where('codigo', 'alta'))
            ->exists();

        if (!$tiene_alta) {
            throw new RuntimeException('Solo se pueden rectificar facturas con registro de facturacion de alta.');
        }
    }
}
