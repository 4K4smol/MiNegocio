<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoFactura extends Model
{
    protected $table = 'tipos_factura';
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo', 'orden'];
    protected $casts = ['activo' => 'boolean'];

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'tipo_factura_id');
    }
}
