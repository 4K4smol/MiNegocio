<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Factura extends Model
{
    use SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'orden_trabajo_id',
        'tipo_factura_id',
        'estado_factura_id',
        'factura_rectificada_id',
        'tipo_rectificacion_id',
        'motivo_rectificacion',
        'serie',
        'numero',
        'numero_completo',
        'fecha_emision',
        'fecha_operacion',
        'fecha_vencimiento',
        'moneda',
        'emisor_nif',
        'emisor_nombre_razon_social',
        'emisor_domicilio_fiscal',
        'receptor_nif',
        'receptor_nombre_razon_social',
        'receptor_domicilio_fiscal',
        'receptor_cp',
        'receptor_municipio',
        'receptor_provincia',
        'receptor_pais',
        'subtotal',
        'cuota_iva',
        'base_imponible',
        'total_iva',
        'total_retencion',
        'total_descuento',
        'total',
        'observaciones',
        'metadatos',
        'pagada',
        'fecha_pago',
        'observaciones_pago',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_operacion' => 'date',
        'fecha_vencimiento' => 'date',
        'pagada' => 'boolean',
        'fecha_pago' => 'date',
        'subtotal' => 'decimal:2',
        'cuota_iva' => 'decimal:2',
        'base_imponible' => 'decimal:2',
        'total_iva' => 'decimal:2',
        'total_retencion' => 'decimal:2',
        'total_descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'metadatos' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (Factura $factura): void {
            if (!$factura->registrosFacturacion()->exists()) {
                return;
            }

            $camposInmutables = [
                'empresa_id',
                'cliente_id',
                'tipo_factura_id',
                'factura_rectificada_id',
                'tipo_rectificacion_id',
                'motivo_rectificacion',
                'serie',
                'numero',
                'numero_completo',
                'fecha_emision',
                'fecha_operacion',
                'fecha_vencimiento',
                'moneda',
                'emisor_nif',
                'emisor_nombre_razon_social',
                'emisor_domicilio_fiscal',
                'receptor_nif',
                'receptor_nombre_razon_social',
                'receptor_domicilio_fiscal',
                'receptor_cp',
                'receptor_municipio',
                'receptor_provincia',
                'receptor_pais',
                'subtotal',
                'cuota_iva',
                'base_imponible',
                'total_iva',
                'total_retencion',
                'total_descuento',
                'total',
            ];

            if ($factura->isDirty($camposInmutables)) {
                throw new RuntimeException('No se pueden modificar datos fiscales de una factura con registros de facturación.');
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }
    public function tipoFactura(): BelongsTo
    {
        return $this->belongsTo(TipoFactura::class, 'tipo_factura_id');
    }
    public function estadoFactura(): BelongsTo
    {
        return $this->belongsTo(EstadoFactura::class, 'estado_factura_id');
    }
    public function facturaRectificada(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_rectificada_id');
    }
    public function tipoRectificacion(): BelongsTo
    {
        return $this->belongsTo(TipoRectificacion::class, 'tipo_rectificacion_id');
    }
    public function lineas(): HasMany
    {
        return $this->hasMany(FacturaLinea::class, 'factura_id');
    }
    public function impuestos(): HasMany
    {
        return $this->hasMany(FacturaImpuesto::class, 'factura_id');
    }
    public function registrosFacturacion(): HasMany
    {
        return $this->hasMany(RegistroFacturacion::class, 'factura_id');
    }
    public function cobros(): HasMany
    {
        return $this->hasMany(FacturaCobro::class, 'factura_id');
    }
    public function historial(): HasMany
    {
        return $this->hasMany(FacturaHistorial::class, 'factura_id');
    }
    public function documentos(): HasMany
    {
        return $this->hasMany(FacturaDocumento::class, 'factura_id');
    }
}
