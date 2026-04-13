<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre_fiscal',
        'nombre_comercial',
        'nif',
        'correo',
        'telefono',
        'direccion_fiscal',
        'codigo_postal',
        'municipio',
        'provincia',
        'pais',
        'activa',
        'tipo_empresa_id',
    ];

    public function tipoEmpresa(): BelongsTo
    {
        return $this->belongsTo(TipoEmpresa::class, 'tipo_empresa_id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function verificaciones(): HasMany
    {
        return $this->hasMany(VerificacionEmpresa::class, 'empresa_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'empresa_id');
    }

    public function modulos(): BelongsToMany
    {
        return $this->belongsToMany(Modulo::class, 'empresa_modulos', 'empresa_id', 'modulo_id')
            ->using(EmpresaModulo::class)
            ->withPivot(['id', 'activo', 'fecha_activacion', 'fecha_desactivacion'])
            ->withTimestamps();
    }

    public function empresaModulos(): HasMany
    {
        return $this->hasMany(EmpresaModulo::class, 'empresa_id');
    }
}
