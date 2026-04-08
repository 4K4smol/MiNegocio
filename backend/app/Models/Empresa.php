<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre',
        'razon_social',
        'nif',
        'email',
        'telefono',
        'direccion',
        'tipo_empresa_id',
        'estado_verificacion_id',
    ];

    public function tipoEmpresa(): BelongsTo
    {
        return $this->belongsTo(TipoEmpresa::class, 'tipo_empresa_id');
    }

    public function estadoVerificacion(): BelongsTo
    {
        return $this->belongsTo(EstadoVerificacion::class, 'estado_verificacion_id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function verificaciones(): HasMany
    {
        return $this->hasMany(VerificacionEmpresa::class, 'empresa_id');
    }
}
