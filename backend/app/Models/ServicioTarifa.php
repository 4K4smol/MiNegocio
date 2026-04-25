<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicioTarifa extends Model
{
    protected $table = 'servicio_tarifas';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nombre',
        'descripcion',
        'es_default',
        'activo',
    ];

    protected $casts = [
        'es_default' => 'boolean',
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function precios(): HasMany
    {
        return $this->hasMany(ServicioPrecio::class, 'servicio_tarifa_id');
    }
}
