<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaLinea extends Model
{
    // Snapshot fiscal final: no se altera por cambios posteriores de la orden.
    protected $table = 'factura_lineas';

    protected $fillable = [
       'factura_id'
    ];
    //'orden_trabajo_linea_id','unidad_servicio_id','unidad_servicio_codigo','unidad_servicio_nombre_snapshot',
    //     'servicio_nombre_snapshot','descripcion','cantidad','precio_unitario','base_imponible',
    //     'iva_porcentaje','descuento_porcentaje','subtotal','total_iva','total','orden',

    // protected $casts = [
    //     'cantidad' => 'decimal:2',
    //     'precio_unitario' => 'decimal:2',
    //     'base_imponible' => 'decimal:2',
    //     'iva_porcentaje' => 'decimal:2',
    //     'descuento_porcentaje' => 'decimal:2',
    //     'subtotal' => 'decimal:2',
    //     'total_iva' => 'decimal:2',
    //     'total' => 'decimal:2',
    // ];

    public function factura(): BelongsTo { return $this->belongsTo(Factura::class, 'factura_id'); }
    // public function ordenTrabajoLinea(): BelongsTo { return $this->belongsTo(OrdenTrabajoLinea::class, 'orden_trabajo_linea_id'); }
    // public function unidadServicio(): BelongsTo { return $this->belongsTo(UnidadServicio::class, 'unidad_servicio_id'); }
}
