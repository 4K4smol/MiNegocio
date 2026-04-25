<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenTrabajoParte extends Model
{
    protected $table = 'ordenes_trabajo_partes';

    protected $fillable = [
        'orden_trabajo_id',
        'orden_trabajo_linea_id',
        'user_id',
        'inicio',
        'fin',
        'minutos',
        'descripcion',
        'facturable',
        'meta',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'minutos' => 'integer',
        'facturable' => 'boolean',
        'meta' => 'array',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function linea(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajoLinea::class, 'orden_trabajo_linea_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
