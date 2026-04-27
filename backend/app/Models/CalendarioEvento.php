<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarioEvento extends Model
{
    protected $table = 'calendario_eventos';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'orden_trabajo_id',
        'creado_por',
        'titulo',
        'descripcion',
        'tipo',
        'inicio',
        'fin',
        'todo_el_dia',
        'estado_codigo',
        'ubicacion',
        'meta',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'todo_el_dia' => 'boolean',
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

    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
