<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaLinea extends Model
{
    // Snapshot fiscal final: no se altera por cambios posteriores del origen operativo.
    protected $table = 'factura_lineas';

    protected $fillable = [
        'factura_id',
        'orden_trabajo_linea_id',
        'servicio_id',
        'servicio_nombre_snapshot',
        'descripcion',
        'cantidad',
        'unidad_snapshot',
        'precio_unitario',
        'descuento_porcentaje',
        'base_imponible',
        'iva_porcentaje',
        'cuota_iva',
        'total',
        'orden',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'descuento_porcentaje' => 'decimal:2',
        'base_imponible' => 'decimal:2',
        'iva_porcentaje' => 'decimal:2',
        'cuota_iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}
