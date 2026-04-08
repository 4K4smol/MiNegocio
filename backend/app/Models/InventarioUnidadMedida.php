<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarioUnidadMedida extends Model
{
    protected $table = 'inventario_unidades_medida';

    protected $fillable = [
        'codigo',
        'nombre',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventarioItem::class, 'unidad_medida_id');
    }
}
