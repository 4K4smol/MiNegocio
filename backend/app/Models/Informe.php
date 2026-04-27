<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Informe extends Model
{
    protected $table = 'informes';

    protected $fillable = [
        'empresa_id',
        'generado_por',
        'codigo',
        'nombre',
        'tipo',
        'formato',
        'estado_codigo',
        'filtros',
        'resumen',
        'contenido',
        'generado_en',
    ];

    protected $casts = [
        'filtros' => 'array',
        'resumen' => 'array',
        'generado_en' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }
}
