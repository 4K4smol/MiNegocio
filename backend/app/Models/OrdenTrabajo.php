<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenTrabajo extends Model
{
    protected $table = 'ordenes_trabajo';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'numero',
        'estado_id',
        'canal_origen',
        'prioridad_id',
        'prioridad_codigo',
        'fecha_apertura',
        'fecha_programada_inicio',
        'fecha_programada_fin',
        'fecha_inicio_real',
        'fecha_fin_real',
        'fecha_cierre',
        'tecnico_responsable_id',
        'descuento_global_porcentaje',
        'notas_internas',
        'notas_cliente',
        'meta',
    ];

    protected $casts = [
        'fecha_apertura' => 'date',
        'fecha_programada_inicio' => 'datetime',
        'fecha_programada_fin' => 'datetime',
        'fecha_inicio_real' => 'datetime',
        'fecha_fin_real' => 'datetime',
        'fecha_cierre' => 'datetime',
        'descuento_global_porcentaje' => 'decimal:2',
        'meta' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajoEstado::class, 'estado_id');
    }

    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajoPrioridad::class, 'prioridad_id');
    }

    public function tecnicoResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_responsable_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(OrdenTrabajoLinea::class, 'orden_trabajo_id');
    }

    public function partes(): HasMany
    {
        return $this->hasMany(OrdenTrabajoParte::class, 'orden_trabajo_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'orden_trabajo_id');
    }

    public function eventosCalendario(): HasMany
    {
        return $this->hasMany(CalendarioEvento::class, 'orden_trabajo_id');
    }
}
