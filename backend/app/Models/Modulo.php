<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
        'orden_visual',
        'icono',
    ];

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_modulos', 'modulo_id', 'empresa_id')
            ->using(EmpresaModulo::class)
            ->withPivot(['id', 'activo', 'fecha_activacion', 'fecha_desactivacion'])
            ->withTimestamps();
    }

    public function empresaModulos(): HasMany
    {
        return $this->hasMany(EmpresaModulo::class, 'modulo_id');
    }
}
