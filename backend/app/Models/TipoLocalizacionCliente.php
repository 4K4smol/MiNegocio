<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoLocalizacionCliente extends Model
{
    protected $table = 'tipos_localizacion_cliente';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'activo', 'orden'];

    protected $casts = ['activo' => 'boolean'];

    public function localizaciones(): HasMany
    {
        return $this->hasMany(LocalizacionCliente::class, 'tipo_localizacion_id');
    }
}
