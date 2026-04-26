<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumentoIdentidad extends Model
{
    protected $table = 'tipos_documento_identidad';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function verificacionesUsuario(): HasMany
    {
        return $this->hasMany(VerificacionUsuario::class, 'tipo_documento_identidad_id');
    }
}
