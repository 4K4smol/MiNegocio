<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    protected $table = 'tareas';

    protected $fillable = [
        'orden_trabajo_id',
        'orden_trabajo_linea_id',
        'responsable_id',
        'titulo',
        'descripcion',
        'estado_codigo',
        'fecha_vencimiento',
        'fecha_completada',
        'orden',
        'activa',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'datetime',
        'fecha_completada' => 'datetime',
        'orden' => 'integer',
        'activa' => 'boolean',
    ];

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function ordenTrabajoLinea(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajoLinea::class, 'orden_trabajo_linea_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
