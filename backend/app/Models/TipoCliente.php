<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCliente extends Model
{
    protected $table = 'tipos_cliente';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo', 'orden'];

    protected $casts = ['activo' => 'boolean'];

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'tipo_cliente_id');
    }
}
