<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoInventarioMovimiento extends Model
{
    protected $table = 'tipos_inventario_movimiento';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo', 'orden'];

    protected $casts = ['activo' => 'boolean'];

    public function movimientos(): HasMany
    {
        return $this->hasMany(InventarioMovimiento::class, 'tipo_movimiento_id');
    }
}
