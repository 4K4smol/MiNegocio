<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEventoFacturacion extends Model
{
    protected $table = 'tipos_evento_facturacion';
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo', 'orden'];
    protected $casts = ['activo' => 'boolean'];

    public function registrosEvento(): HasMany
    {
        return $this->hasMany(RegistroEvento::class, 'tipo_evento_facturacion_id');
    }
}
