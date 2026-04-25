<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    use SoftDeletes;

    protected $table = 'servicios';

    protected $fillable = [
        'empresa_id',
        'tipo_negocio',
        'codigo',
        'nombre',
        'descripcion',
        'unidad_servicio',
        'duracion_estimada_min',
        'activo',
        'meta',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'duracion_estimada_min' => 'integer',
        'meta' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function precios(): HasMany
    {
        return $this->hasMany(ServicioPrecio::class, 'servicio_id');
    }

    public function lineasOrdenTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajoLinea::class, 'servicio_id');
    }
}
