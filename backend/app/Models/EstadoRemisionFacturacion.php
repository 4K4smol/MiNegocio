<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoRemisionFacturacion extends Model
{
    protected $table = 'estados_remision_facturacion';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo', 'orden'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function registrosFacturacion(): HasMany
    {
        return $this->hasMany(RegistroFacturacion::class, 'estado_remision_facturacion_id');
    }
}
