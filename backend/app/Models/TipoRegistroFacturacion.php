<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoRegistroFacturacion extends Model
{
    protected $table = 'tipos_registro_facturacion';
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo', 'orden'];
    protected $casts = ['activo' => 'boolean'];

    public function registrosFacturacion(): HasMany
    {
        return $this->hasMany(RegistroFacturacion::class, 'tipo_registro_facturacion_id');
    }
}
