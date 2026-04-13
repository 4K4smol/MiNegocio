<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaImpuesto extends Model
{
    protected $table = 'factura_impuestos';

    protected $fillable = [
        'factura_id', 'impuesto_codigo', 'impuesto_nombre', 'base_imponible', 'tipo_porcentaje', 'cuota',
        'es_exento', 'motivo_exencion', 'es_no_sujeto', 'motivo_no_sujecion',
        'recargo_equivalencia_porcentaje', 'recargo_equivalencia_cuota', 'calificacion', 'descripcion',
    ];

    protected $casts = [
        'base_imponible' => 'decimal:2',
        'tipo_porcentaje' => 'decimal:2',
        'cuota' => 'decimal:2',
        'es_exento' => 'boolean',
        'es_no_sujeto' => 'boolean',
        'recargo_equivalencia_porcentaje' => 'decimal:2',
        'recargo_equivalencia_cuota' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }
}
