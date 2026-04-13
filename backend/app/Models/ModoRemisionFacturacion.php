<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModoRemisionFacturacion extends Model
{
    protected $table = 'modos_remision_facturacion';
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'requiere_firma', 'activo', 'orden'];
    protected $casts = ['requiere_firma' => 'boolean', 'activo' => 'boolean'];

    public function registrosFacturacion(): HasMany
    {
        return $this->hasMany(RegistroFacturacion::class, 'modo_remision_facturacion_id');
    }

    public function registrosEvento(): HasMany
    {
        return $this->hasMany(RegistroEvento::class, 'modo_remision_facturacion_id');
    }
}
