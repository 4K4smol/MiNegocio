<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioPrecio extends Model
{
    protected $table = 'servicio_precios';

    protected $fillable = [
        'servicio_id',
        'tipo_tarifa_servicio_id',
        'precio_base',
        'iva_porcentaje',
        'retencion_porcentaje',
        'moneda',
        'meta',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'iva_porcentaje' => 'decimal:2',
        'retencion_porcentaje' => 'decimal:2',
        'meta' => 'array',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(TipoTarifaServicio::class, 'tipo_tarifa_servicio_id');
    }

    public function tipoTarifaServicio(): BelongsTo
    {
        return $this->belongsTo(TipoTarifaServicio::class, 'tipo_tarifa_servicio_id');
    }
}
