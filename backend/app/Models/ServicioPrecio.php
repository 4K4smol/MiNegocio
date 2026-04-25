<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioPrecio extends Model
{
    protected $table = 'servicio_precios';

    protected $fillable = [
        'servicio_id',
        'servicio_tarifa_id',
        'precio_base',
        'iva_porcentaje',
        'retencion_porcentaje',
        'moneda',
        'vigente_desde',
        'vigente_hasta',
        'meta',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'iva_porcentaje' => 'decimal:2',
        'retencion_porcentaje' => 'decimal:2',
        'vigente_desde' => 'datetime',
        'vigente_hasta' => 'datetime',
        'meta' => 'array',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(ServicioTarifa::class, 'servicio_tarifa_id');
    }
}
