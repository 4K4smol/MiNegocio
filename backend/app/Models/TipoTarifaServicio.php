<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoTarifaServicio extends Model
{
    protected $table = 'tipos_tarifa_servicio';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function precios(): HasMany
    {
        return $this->hasMany(ServicioPrecio::class, 'tipo_tarifa_servicio_id');
    }
}
