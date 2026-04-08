<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoVerificacion extends Model
{
    protected $table = 'estados_verificacion';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'estado_verificacion_id');
    }

    public function verificacionesEmpresa(): HasMany
    {
        return $this->hasMany(VerificacionEmpresa::class, 'estado_verificacion_id');
    }

    public function verificacionesUsuario(): HasMany
    {
        return $this->hasMany(VerificacionUsuario::class, 'estado_verificacion_id');
    }
}
