<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstadoFactura;
use App\Models\Factura;
use App\Models\TipoFactura;
use App\Models\tipo_rectificacion;
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
        private readonly RegistroEventoFacturacionService $registroEventoFacturacionService
    ) {}

    public function generarDesdeFactura(Factura $factura_original, User $user, ?string $motivo = null): Factura
    {
        return DB::transaction(function () use ($factura_original, $user, $motivo): Factura {
            $factura_original->loadMissing(['lineas', 'impuestos', 'estadoFactura', 'tipoFactura']);
            $this->validarFacturaRectificable($factura_original, $user);

            $tipoFactura = TipoFactura::query()->where('codigo', self::TIPO_FACTURA_RECTIFICATIVA)->first();
            $tipo_rectificacion = TipoRectificacion::query()->where('codigo', self::TIPO_RECTIFICACION_DIFERENCIAS)->first();
            $estadoEmitida = EstadoFactura::query()->where('codigo', self::ESTADO_FACTURA_EMITIDA)->first();

            if (!$tipoFactura || !$tipo_rectificacion || !$estadoEmitida) {
                throw new RuntimeException('Faltan catálogos para generar factura rectificativa.');
            }

            $fecha_emision = now()->toDateString();
            $rectificativa = Factura::query()->create([
                'empresa_id' => $factura_original->empresa_id,
                'cliente_id' => $factura_original->cliente_id,
                'tipo_factura_id' => $tipoFactura->id,
                'estado_factura_id' => $estadoEmitida->id,
                'factura_rectificada_id' => $factura_original->id,
                'tipo_rectificacion_id' => $tipo_rectificacion->id,
                'motivo_rectificacion' => $motivo ?: 'Rectificación por diferencias.',
                'serie' => self::SERIE_RECTIFICATIVA,
                'numero' => $this->generarNumeroRectificativa((int) $factura_original->empresa_id),
                'fecha_emision' => $fecha_emision,
                'fecha_operacion' => $factura_original->fecha_operacion?->toDateString() ?: $factura_original->fecha_emision?->toDateString(),
                'moneda' => $factura_original->moneda,
                'emisor_nif' => $factura_original->emisor_nif,
                'emisor_nombre_razon_social' => $factura_original->emisor_nombre_razon_social,
                'emisor_domicilio_fiscal' => $factura_original->emisor_domicilio_fiscal,
                'receptor_nif' => $factura_original->receptor_nif,
                'receptor_nombre_razon_social' => $factura_original->receptor_nombre_razon_social,
                'receptor_domicilio_fiscal' => $factura_original->receptor_domicilio_fiscal,
                'receptor_cp' => $factura_original->receptor_cp,
                'receptor_municipio' => $factura_original->receptor_municipio,
                'receptor_provincia' => $factura_original->receptor_provincia,
                'receptor_pais' => $factura_original->receptor_pais,
                'subtotal' => -1 * (float) $factura_original->subtotal,
                'cuota_iva' => -1 * (float) $factura_original->cuota_iva,
                'total' => -1 * (float) $factura_original->total,
                'observaciones' => 'Factura rectificativa de '.$factura_original->serie.'-'.$factura_original->numero,
            ]);

            foreach ($factura_original->lineas as $linea) {
                $rectificativa->lineas()->create([
                    'orden_trabajo_linea_id' => null,
                    'servicio_id' => $linea->servicio_id,
                    'unidad_servicio_codigo' => $linea->unidad_servicio_codigo,
                    'unidad_servicio_nombre_snapshot' => $linea->unidad_servicio_nombre_snapshot,
                    'servicio_nombre_snapshot' => $linea->servicio_nombre_snapshot,
                    'descripcion' => 'Rectificación: '.$linea->descripcion,
                    'cantidad' => -1 * (float) $linea->cantidad,
                    'precio_unitario' => $linea->precio_unitario,
                    'base_imponible' => -1 * (float) $linea->base_imponible,
                    'iva_porcentaje' => $linea->iva_porcentaje,
                    'descuento_porcentaje' => $linea->descuento_porcentaje,
                    'subtotal' => -1 * (float) $linea->subtotal,
                    'total_iva' => -1 * (float) $linea->total_iva,
                    'total' => -1 * (float) $linea->total,
                    'orden' => $linea->orden,
                ]);
            }

            foreach ($factura_original->impuestos as $impuesto) {
                $rectificativa->impuestos()->create([
                    'impuesto_codigo' => $impuesto->impuesto_codigo,
                    'impuesto_nombre' => $impuesto->impuesto_nombre,
                    'base_imponible' => -1 * (float) $impuesto->base_imponible,
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
                'descripcion' => 'Registro de facturación de alta para rectificativa generado.',
            ]);

            return $rectificativa->fresh(['lineas', 'impuestos', 'cliente', 'empresa', 'estadoFactura', 'tipoFactura', 'registrosFacturacion']);
        });
    }

    private function validarFacturaRectificable(Factura $factura, User $user): void
    {
        $esAdmin = strtolower((string) $user->role?->nombre) === self::ROL_ADMIN;

        if (! $esAdmin && (int) $factura->empresa_id !== (int) $user->empresa_id) {
            throw new RuntimeException('No puedes rectificar facturas de otra empresa.');
        }

        if ($factura->tipoFactura?->codigo === self::TIPO_FACTURA_RECTIFICATIVA) {
            throw new RuntimeException('No se puede rectificar una factura rectificativa.');
        }

        if ($factura->estadoFactura?->codigo !== self::ESTADO_FACTURA_EMITIDA) {
            throw new RuntimeException('Solo se pueden rectificar facturas emitidas.');
        }

        if (! $factura->registrosFacturacion()->exists()) {
            throw new RuntimeException('Solo se pueden rectificar facturas con registro de facturación.');
        }
    }

    private function generarNumeroRectificativa(int $empresaId): string
    {
        $anio = now()->year;
        $ultimaFactura = Factura::query()
            ->where('empresa_id', $empresaId)
            ->where('serie', self::SERIE_RECTIFICATIVA)
            ->where('numero', 'like', 'R-'.$anio.'-%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $siguienteNumero = $ultimaFactura
            ? ((int) substr((string) $ultimaFactura->numero, -6)) + 1
            : 1;

        return 'R-'.$anio.'-'.str_pad((string) $siguienteNumero, 6, '0', STR_PAD_LEFT);
    }
}
