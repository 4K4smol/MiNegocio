<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenTrabajoLinea extends Model
{
    protected $table = 'ordenes_trabajo_lineas';

    protected $fillable = [
        'orden_trabajo_id',
        'servicio_id',
        'estado_codigo',
        'descripcion',
        'cantidad',
        'unidad_snapshot',
        'precio_unitario',
        'descuento_porcentaje',
        'base_imponible',
        'iva_porcentaje',
        'cuota_iva',
        'total',
        'facturable',
        'facturado_cantidad',
        'orden',
        'meta',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'base_imponible' => 'decimal:2',
        'iva_porcentaje' => 'decimal:2',
        'cuota_iva' => 'decimal:2',
        'total' => 'decimal:2',
        'facturable' => 'boolean',
        'facturado_cantidad' => 'decimal:2',
        'orden' => 'integer',
        'meta' => 'array',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function partes(): HasMany
    {
        return $this->hasMany(OrdenTrabajoParte::class, 'orden_trabajo_linea_id');
    }
}
