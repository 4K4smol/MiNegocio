<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEmpresa extends Model
{
    protected $table = 'tipos_empresa';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'tipo_empresa_id');
    }
}
